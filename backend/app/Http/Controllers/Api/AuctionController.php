<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuctionRequest;
use App\Models\Auction;
use App\Models\AuctionImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $query = Auction::with(['seller:id,first_name,last_name', 'mainImage'])
            ->withCount('bids');

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
            $query->where('category', $request->category);
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
        $auction->load([
            'seller:id,first_name,last_name,email,avatar,created_at',
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
                'category'      => $auction->category,
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
            $auction = Auction::create([
                'seller_id'      => $request->user()->id,
                'title'          => $validated['title'],
                'description'    => $validated['description'],
                'category'       => $validated['category'],
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
     * Lelang milik seller yang sedang login.
     */
    public function myAuctions(): JsonResponse
    {
        $auctions = auth()->user()
            ->auctions()
            ->with(['mainImage', 'winner.winner'])
            ->withCount('bids')
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
            'category'     => $auction->category,
            'description'  => $auction->description,
            'seller'       => $auction->seller?->full_name,
            'seller_id'    => $auction->seller_id,
            'currentPrice' => (float) $auction->current_price,
            'startPrice'   => (float) $auction->starting_price,
            'bidCount'     => $auction->bids_count ?? 0,
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
}
