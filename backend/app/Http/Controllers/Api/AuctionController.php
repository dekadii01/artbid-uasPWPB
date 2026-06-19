<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuctionRequest;
use App\Http\Requests\UpdateAuctionRequest;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Bid;

class AuctionController extends Controller
{
    /**
     * Daftar lelang (publik, tidak perlu login).
     *
     * Query params yang didukung:
     *   ?status=active|scheduled|ended   → filter per status
     *   ?category=Lukisan                → filter per kategori
     *   ?search=keyword                  → cari judul/deskripsi
     *   ?sort=latest|price_high|price_low|ending_soon|most_bids
     *   ?per_page=12
     *
     * Frontend pakai status:
     *   "live"     → backend: "active"
     *   "upcoming" → backend: "scheduled"
     *   "ended"    → backend: "ended"
     */
    public function index(Request $request): JsonResponse
    {
        // Auto-start any scheduled auctions whose starts_at time has passed
        Auction::dueToStart()->update(['status' => 'active']);

        $query = Auction::with(['seller:id,first_name,last_name', 'mainImage', 'category:id,name'])
            ->withCount(['bids', 'watchers']);

        // ── Status filter ────────────────────────────────────────────
        // Map status dari frontend ke backend
        $statusMap = [
            'live'     => 'active',
            'upcoming' => 'scheduled',
            'ended'    => 'ended',
            // Kalau sudah kirim nilai backend langsung, tetap jalan
            'active'    => 'active',
            'scheduled' => 'scheduled',
        ];

        if ($request->filled('status') && $request->status !== 'all') {
            $backendStatus = $statusMap[$request->status] ?? $request->status;
            $query->where('status', $backendStatus);
        }
        // Kalau tidak ada filter status / status=all → tampilkan semua

        // ── Category filter ──────────────────────────────────────────
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // ── Search ───────────────────────────────────────────────────
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        // ── Sort ─────────────────────────────────────────────────────
        switch ($request->sort) {
            case 'price_high':
                $query->orderByDesc('current_price');
                break;
            case 'price_low':
                $query->orderBy('current_price');
                break;
            case 'ending_soon':
                // Prioritaskan yang aktif dan hampir habis
                $query->orderByRaw("FIELD(status, 'active', 'scheduled', 'ended')")
                    ->orderBy('ends_at');
                break;
            case 'most_bids':
                $query->orderByDesc('bids_count');
                break;
            default: // latest
                $query->latest();
                break;
        }

        $perPage = min((int) $request->get('per_page', 12), 50);
        $auctions = $query->paginate($perPage);

        // ── Transform: map field backend → frontend ──────────────────
        $auctions->getCollection()->transform(function (Auction $auction) {
            return $this->transformAuction($auction);
        });

        return response()->json($auctions);
    }

    /**
     * Detail satu lelang (publik).
     */
    /**
     * Detail satu lelang (publik).
     * GET /api/auctions/{id}
     */
    public function show(Auction $auction): JsonResponse
    {
        if ($auction->status === 'scheduled' && $auction->starts_at && $auction->starts_at->isPast()) {
            $auction->update(['status' => 'active']);
            $auction->status = 'active'; // keep in-memory model updated
        }

        $auction->load([
            'seller:id,first_name,last_name,email,avatar,created_at',
            'category:id,name',
            'images',
            'bids.bidder:id,first_name,last_name,avatar',
            'winner.winner:id,first_name,last_name',
        ])->loadCount('bids');

        $statusMap = [
            'scheduled' => 'upcoming',
            'active'    => 'live',
            'ended'     => 'ended',
        ];

        // Sort images by sort_order
        $images = $auction->images->sortBy('sort_order')->values();

        // Format bids — masking nama untuk privasi
        $currentUser = auth('sanctum')->user();
        $bids = $auction->bids->map(function ($bid) use ($currentUser) {
            $name = $bid->bidder?->full_name ?? 'Anonim';
            // Masking: ambil 3 huruf pertama + *** + 3 huruf terakhir
            $masked = strlen($name) > 6
                ? substr($name, 0, 3) . '***' . substr($name, -3)
                : substr($name, 0, 3) . '***';

            $displayName = ($currentUser && $currentUser->id === $bid->bidder_id) ? 'Anda' : $masked;

            return [
                'id'     => $bid->id,
                'user'   => $displayName,
                'avatar' => $bid->bidder?->avatar
                    ? \Storage::disk(config('filesystems.default'))->url($bid->bidder->avatar)
                    : 'https://i.pravatar.cc/32?u=' . $bid->bidder_id,
                'amount' => (float) $bid->amount,
                'time'   => $bid->placed_at
                    ? \Carbon\Carbon::parse($bid->placed_at)->setTimezone('Asia/Makassar')->format('H:i:s')
                    : $bid->created_at->setTimezone('Asia/Makassar')->format('H:i:s'),
                'status' => $bid->status,
            ];
        })->sortByDesc('amount')->values();

        // Winner info
        $winner = null;
        if ($auction->winner) {
            $winner = [
                'name'          => $auction->winner->winner?->full_name ?? '—',
                'finalPrice'    => (float) $auction->winner->final_price,
                'paymentStatus' => $auction->winner->payment_status ?? 'pending',
            ];
        }

        // Check if watchlisted by current user
        $isWatched = false;
        $user = auth('sanctum')->user();
        if ($user) {
            $isWatched = $user->watchedAuctions()->where('auction_id', $auction->id)->exists();
        }

        return response()->json([
            'auction' => [
                'id'            => $auction->id,
                'name'          => $auction->title,
                'category'      => $auction->category?->name,
                'description'   => $auction->description,
                'condition'     => $auction->condition ?? null,
                'artist'        => $auction->artist ?? null,
                'year'          => $auction->year ?? null,
                'status'        => $statusMap[$auction->status] ?? $auction->status,
                'startPrice'    => (float) $auction->starting_price,
                'currentPrice'  => (float) $auction->current_price,
                'minIncrement'  => (float) $auction->bid_increment,
                'buyNowPrice'   => $auction->buy_now_price ? (float) $auction->buy_now_price : null,
                'startsAt'      => $auction->starts_at?->toIso8601String(),
                'endsAt'        => $auction->ends_at?->toIso8601String(),
                'createdAt'     => $auction->created_at?->toIso8601String(),
                'isWatchlisted' => $isWatched,
                'images'        => $images->map(fn($img) => [
                    'id'        => $img->id,
                    'url'       => $img->url,
                    'sortOrder' => $img->sort_order,
                ])->values(),
                'seller' => [
                    'id'        => $auction->seller?->id,
                    'name'      => $auction->seller?->full_name,
                    'email'     => $auction->seller?->email,
                    'avatar'    => $auction->seller?->avatar ?? null,
                    'joinedAt'  => $auction->seller?->created_at?->toIso8601String(),
                ],
                'bids'         => $bids,
                'bidCount'     => $auction->bids_count,
                'winner'       => $winner,
            ],
        ]);
    }

    /**
     * Buat lelang baru beserta upload foto.
     */
    public function store(StoreAuctionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $disk = config('filesystems.default');

        $auction = DB::transaction(function () use ($validated, $request, $disk) {
            $categoryModel = Category::firstOrCreate(
                    ['name' => $validated['category']],
                    ['slug' => \Illuminate\Support\Str::slug($validated['category'])]
                );

            $auction = Auction::create([
                'seller_id'      => $request->user()->id,
                'title'          => $validated['title'],
                'description'    => $validated['description'],
                'category_id'    => $categoryModel->id,
                'condition'      => $validated['condition'] ?? null,
                'artist'         => $validated['artist'] ?? null,
                'year'           => $validated['year'] ?? null,
                'starting_price' => $validated['starting_price'],
                'current_price'  => $validated['starting_price'],
                'bid_increment'  => $validated['bid_increment'],
                'buy_now_price'  => $validated['buy_now_price'] ?? null,
                'status'         => 'scheduled',
                'starts_at'      => $validated['starts_at'],
                'ends_at'        => $validated['ends_at'],
            ]);

            $this->storeImage($request->file('main_photo'), $auction, $disk, 0);

            if ($request->hasFile('extra_photos')) {
                foreach ($request->file('extra_photos') as $index => $file) {
                    $this->storeImage($file, $auction, $disk, $index + 1);
                }
            }

            return $auction;
        });

        $auction->load('images');

        return response()->json([
            'message' => 'Lelang berhasil dibuat.',
            'auction' => $auction,
        ], 201);
    }

    /**
     * Update lelang (hanya jika status = scheduled dan milik sendiri).
     */
    public function update(UpdateAuctionRequest $request, Auction $auction): JsonResponse
    {
        // Pastikan lelang milik user yang sedang login atau admin
        if ($auction->seller_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Kamu tidak berhak mengubah lelang ini.'], 403);
        }

        // Hanya boleh diubah jika status scheduled
        if ($auction->status !== 'scheduled') {
            return response()->json(['message' => 'Lelang hanya dapat diubah sebelum dimulai.'], 422);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $auction) {
            $categoryModel = Category::firstOrCreate(
                ['name' => $validated['category']],
                ['slug' => \Illuminate\Support\Str::slug($validated['category'])]
            );

            $auction->update([
                'title'          => $validated['title'],
                'description'    => $validated['description'],
                'category_id'    => $categoryModel->id,
                'condition'      => $validated['condition'] ?? null,
                'artist'         => $validated['artist'] ?? null,
                'year'           => $validated['year'] ?? null,
                'starting_price' => $validated['starting_price'],
                'current_price'  => $validated['starting_price'],
                'bid_increment'  => $validated['bid_increment'],
                'buy_now_price'  => $validated['buy_now_price'] ?? null,
                'starts_at'      => $validated['starts_at'],
                'ends_at'        => $validated['ends_at'],
            ]);

            // Reorder existing photos if photo_order is provided
            if ($request->has('photo_order')) {
                $order = $request->input('photo_order');
                foreach ($order as $index => $imageId) {
                    AuctionImage::where('id', $imageId)
                        ->where('auction_id', $auction->id)
                        ->update(['sort_order' => $index]);
                }
            }
        });

        $auction->load(['images', 'category']);

        return response()->json([
            'message' => 'Lelang berhasil diperbarui.',
            'auction' => $this->transformAuction($auction),
        ]);
    }

    /**
     * Lelang milik seller yang sedang login.
     */
    public function myAuctions(): JsonResponse
    {
        $auctions = auth()->user()
            ->auctions()
            ->with(['mainImage', 'category:id,name', 'winner.winner'])
            ->withCount(['bids', 'watchers'])
            ->latest()
            ->paginate(12);

        $auctions->getCollection()->transform(fn($a) => $this->transformAuction($a));

        return response()->json($auctions);
    }

    /**
     * Transform satu Auction model → array siap pakai di frontend.
     *
     * Status mapping: scheduled → upcoming, active → live, ended → ended
     */
    private function transformAuction(Auction $auction): array
    {
        $statusMap = [
            'scheduled' => 'upcoming',
            'active'    => 'live',
            'ended'     => 'ended',
        ];

        // URL foto utama — pakai accessor 'url' dari AuctionImage model
        $mainImageUrl = $auction->mainImage?->url ?? null;

        return [
            'id'           => $auction->id,
            'name'         => $auction->title,
            'artist'       => $auction->artist,
            'category'     => $auction->category?->name,
            'description'  => $auction->description,
            'seller'       => $auction->seller?->full_name,
            'seller_id'    => $auction->seller_id,
            'currentPrice' => (float) $auction->current_price,
            'startPrice'   => (float) $auction->starting_price,
            'bidCount'     => $auction->bids_count ?? 0,
            'totalBids'    => $auction->bids_count ?? 0,
            'watching'     => $auction->watchers_count ?? 0,
            'photoCount'   => $auction->images_count ?? 1,
            'image'        => $mainImageUrl,
            'status'       => $statusMap[$auction->status] ?? $auction->status,
            'startsAt'     => $auction->starts_at?->toIso8601String(),
            'endsAt'       => $auction->ends_at?->toIso8601String(),
            'createdAt'    => $auction->created_at?->toIso8601String(),
        ];
    }

    /**
     * Helper: upload satu file foto ke disk aktif.
     */
    private function storeImage($file, Auction $auction, string $disk, int $sortOrder): AuctionImage
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "auctions/{$auction->id}/{$filename}";

        Storage::disk($disk)->put($path, file_get_contents($file));

        return AuctionImage::create([
            'auction_id'   => $auction->id,
            'image_path'   => $path,
            'storage_disk' => $disk,
            'sort_order'   => $sortOrder,
        ]);
    }

    /**
     * Hapus lelang (hanya jika status = scheduled dan milik sendiri).
     */
    public function destroy(Auction $auction): JsonResponse
    {
        // Pastikan lelang milik user yang sedang login
        if ($auction->seller_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Hanya boleh dihapus jika status scheduled
        if ($auction->status !== 'scheduled') {
            return response()->json(['message' => 'Lelang hanya dapat dihapus sebelum dimulai.'], 422);
        }

        // Soft delete the auction
        $auction->delete();

        return response()->json([
            'message' => 'Lelang berhasil dihapus.',
        ]);
    }

    /**
     * Dashboard statistik, popular works, ended results, performance data, & recent activities.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $sellerAuctionIds = $user->auctions()->pluck('id');

        // 1. Stats
        $stats = [
            'total'     => $user->auctions()->count(),
            'active'    => $user->auctions()->where('status', 'active')->count(),
            'upcoming'  => $user->auctions()->where('status', 'scheduled')->count(),
            'ended'     => $user->auctions()->where('status', 'ended')->count(),
        ];

        // 2. Popular Works (Top 3)
        $allAuctions = $user->auctions()
            ->with(['mainImage', 'category:id,name'])
            ->withCount(['bids', 'watchers'])
            ->get();

        $popularWorks = [];

        // - Paling Banyak Ditawar
        $p1 = $allAuctions->sortByDesc('bids_count')->first();
        if ($p1) {
            $popularWorks[] = [
                'id'           => $p1->id,
                'name'         => $p1->title,
                'image'        => $p1->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=600&q=80',
                'currentPrice' => (float) $p1->current_price,
                'totalBids'    => $p1->bids_count,
                'watching'     => $p1->watchers_count,
                'badge'        => 'Paling Banyak Ditawar',
            ];
        }

        // - Paling Banyak Dilihat (Watched)
        $p2 = $allAuctions->where('id', '!=', $p1?->id)->sortByDesc('watchers_count')->first();
        if ($p2) {
            $popularWorks[] = [
                'id'           => $p2->id,
                'name'         => $p2->title,
                'image'        => $p2->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=600&q=80',
                'currentPrice' => (float) $p2->current_price,
                'totalBids'    => $p2->bids_count,
                'watching'     => $p2->watchers_count,
                'badge'        => 'Paling Banyak Dilihat',
            ];
        }

        // - Harga Tertinggi
        $p3 = $allAuctions->whereNotIn('id', array_filter([$p1?->id, $p2?->id]))->sortByDesc('current_price')->first();
        if ($p3) {
            $popularWorks[] = [
                'id'           => $p3->id,
                'name'         => $p3->title,
                'image'        => $p3->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=600&q=80',
                'currentPrice' => (float) $p3->current_price,
                'totalBids'    => $p3->bids_count,
                'watching'     => $p3->watchers_count,
                'badge'        => 'Harga Tertinggi',
            ];
        }

        // 3. Recent Activities (from Bids & Ends)
        $recentBids = Bid::whereIn('auction_id', $sellerAuctionIds)
            ->with(['bidder', 'auction'])
            ->latest()
            ->limit(10)
            ->get();

        $activities = [];
        foreach ($recentBids as $bid) {
            $isFirstBid = !Bid::where('auction_id', $bid->auction_id)
                ->where('id', '<', $bid->id)
                ->exists();

            $timeDiff = $bid->created_at->diffForHumans();

            if ($isFirstBid) {
                $activities[] = [
                    'type' => 'newbidder',
                    'text' => 'Lelang <strong class="text-black">"' . e($bid->auction->title) . '"</strong> mendapatkan penawar baru',
                    'time' => $timeDiff,
                    'timestamp' => $bid->created_at->timestamp,
                ];
            }

            $activities[] = [
                'type' => 'bid',
                'text' => 'Penawaran baru sebesar <strong class="text-black">Rp ' . number_format($bid->amount, 0, ',', '.') . '</strong> pada <strong class="text-black">"' . e($bid->auction->title) . '"</strong>',
                'time' => $timeDiff,
                'timestamp' => $bid->created_at->timestamp,
            ];
        }

        // Ambil lelang yang baru selesai
        $endedAuctions = $user->auctions()
            ->where('status', 'ended')
            ->latest('ends_at')
            ->limit(5)
            ->get();

        foreach ($endedAuctions as $auction) {
            $timeDiff = ($auction->ends_at ?? $auction->updated_at)->diffForHumans();
            $activities[] = [
                'type' => 'ended',
                'text' => 'Lelang <strong class="text-black">"' . e($auction->title) . '"</strong> telah berakhir dengan harga akhir <strong class="text-black">Rp ' . number_format($auction->current_price, 0, ',', '.') . '</strong>',
                'time' => $timeDiff,
                'timestamp' => ($auction->ends_at ?? $auction->updated_at)->timestamp,
            ];
        }

        // Sort combined activities by timestamp DESC
        usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        $activities = array_slice($activities, 0, 8); // top 8

        // 4. Ended Auction Results
        $auctionResults = $user->auctions()
            ->where('status', 'ended')
            ->with(['winner.winner'])
            ->withCount('bids')
            ->latest('ends_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'id'         => $a->id,
                'name'       => $a->title,
                'finalPrice' => (float) ($a->winner?->final_price ?? $a->current_price),
                'winner'     => $a->winner?->winner?->full_name ?? '—',
                'totalBids'  => $a->bids_count,
            ]);

        // 5. Performance
        $totalEarnings = (float) DB::table('auction_winners')
            ->whereIn('auction_id', $sellerAuctionIds)
            ->sum('final_price');

        $endedCount = $user->auctions()->where('status', 'ended')->count();
        $endedWithBids = $user->auctions()->where('status', 'ended')->withCount('bids')->get();
        $avgBids = $endedCount > 0 ? (int) round($endedWithBids->avg('bids_count')) : 0;
        $avgPrice = $endedCount > 0 ? (float) $endedWithBids->avg('current_price') : 0;

        $performance = [
            'totalEarnings' => $totalEarnings,
            'endedCount'    => $endedCount,
            'avgPrice'      => $avgPrice,
            'avgBids'       => $avgBids,
        ];

        return response()->json([
            'stats'            => $stats,
            'popularWorks'     => $popularWorks,
            'recentActivities' => $activities,
            'auctionResults'   => $auctionResults,
            'performance'      => $performance,
        ]);
    }
}
