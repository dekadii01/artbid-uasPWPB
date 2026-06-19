<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Models\Category;
use App\Models\AuctionWinner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    /**
     * Dashboard statistik, popular works, ended results, performance data, & recent activities.
     */
    public function dashboard(Request $request): JsonResponse
    {
        // 1. Main statistics
        $totalUsers = User::count();
        $totalAuctions = Auction::count();
        $activeAuctions = Auction::where('status', 'active')->count();
        $totalBids = Bid::count();

        // 2. Auction Status Breakdown
        $scheduledAuctions = Auction::where('status', 'scheduled')->count();
        $endedAuctions = Auction::where('status', 'ended')->count();

        // Percentages for status (cap at 100 or relative)
        $maxStatus = max(1, $activeAuctions, $scheduledAuctions, $endedAuctions);
        $activePct = (int) round(($activeAuctions / $maxStatus) * 100);
        $scheduledPct = (int) round(($scheduledAuctions / $maxStatus) * 100);
        $endedPct = (int) round(($endedAuctions / $maxStatus) * 100);

        $statusData = [
            ['label' => 'Sedang Berlangsung', 'count' => $activeAuctions, 'dot' => 'bg-black', 'pct' => $activePct],
            ['label' => 'Akan Datang', 'count' => $scheduledAuctions, 'dot' => 'bg-gray-400', 'pct' => $scheduledPct],
            ['label' => 'Selesai', 'count' => $endedAuctions, 'dot' => 'bg-gray-200', 'pct' => $endedPct],
        ];

        // 3. Top Categories
        $topCategories = Category::withCount('auctions')
            ->orderByDesc('auctions_count')
            ->limit(5)
            ->get()
            ->map(function ($cat, $index) {
                $emojis = ['🖼', '🗿', '🎭', '🪵', '🏺'];
                return [
                    'name'  => $cat->name,
                    'emoji' => $emojis[$index] ?? '📦',
                    'count' => $cat->auctions_count,
                ];
            });

        // 4. Monthly Auction Chart (last 6 months)
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->translatedFormat('M'); // e.g. Jan, Feb
            $val = Auction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyChart[] = [
                'month'  => $monthName,
                'val'    => $val,
                'pct'    => 0, // calculated later relative to max
                'active' => $i === 0,
            ];
        }
        $maxVal = max(array_column($monthlyChart, 'val')) ?: 1;
        foreach ($monthlyChart as &$m) {
            $m['pct'] = (int) round(($m['val'] / $maxVal) * 100);
        }

        // 5. Daily Bids Chart (last 7 days)
        $dailyBidsChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayName = $date->translatedFormat('D'); // Sen, Sel, Rab, etc.
            $val = Bid::whereDate('created_at', $date->toDateString())->count();
            $dailyBidsChart[] = [
                'day'    => $dayName,
                'val'    => $val,
                'pct'    => 0, // calculated later
                'active' => $i === 0,
            ];
        }
        $maxBidVal = max(array_column($dailyBidsChart, 'val')) ?: 1;
        foreach ($dailyBidsChart as &$d) {
            $d['pct'] = (int) round(($d['val'] / $maxBidVal) * 100);
        }

        // 6. System Activities (Bids, Users, Ended Auctions)
        $recentBids = Bid::with(['bidder', 'auction'])->latest()->limit(5)->get()->map(function ($bid) {
            return [
                'type'      => 'bid',
                'text'      => 'Lelang <strong class="text-black">"' . e($bid->auction?->title ?? 'Lelang') . '"</strong> menerima penawaran baru sebesar <strong class="text-black">Rp ' . number_format($bid->amount, 0, ',', '.') . '</strong> oleh "' . e($bid->bidder?->full_name) . '"',
                'time'      => $bid->created_at->diffForHumans(),
                'timestamp' => $bid->created_at->timestamp,
            ];
        });

        $recentUsers = User::latest()->limit(5)->get()->map(function ($u) {
            return [
                'type'      => 'user',
                'text'      => 'Pengguna baru <strong class="text-black">"' . e($u->full_name) . '"</strong> berhasil melakukan registrasi',
                'time'      => $u->created_at->diffForHumans(),
                'timestamp' => $u->created_at->timestamp,
            ];
        });

        $recentEnded = Auction::where('status', 'ended')->latest('ends_at')->limit(5)->get()->map(function ($a) {
            return [
                'type'      => 'ended',
                'text'      => 'Lelang <strong class="text-black">"' . e($a->title) . '"</strong> telah berakhir dengan harga akhir <strong class="text-black">Rp ' . number_format($a->current_price, 0, ',', '.') . '</strong>',
                'time'      => ($a->ends_at ?? $a->updated_at)->diffForHumans(),
                'timestamp' => ($a->ends_at ?? $a->updated_at)->timestamp,
            ];
        });

        $systemActivities = collect()
            ->concat($recentBids)
            ->concat($recentUsers)
            ->concat($recentEnded)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values();

        // 7. Ending Soon Auctions (Active, sorted by ends_at asc, limit 3)
        $endingSoon = Auction::where('status', 'active')
            ->with('mainImage')
            ->orderBy('ends_at')
            ->limit(3)
            ->get()
            ->map(function ($a) {
                // Calculate countdown string
                $countdown = '00:00:00';
                if ($a->ends_at) {
                    $diff = now()->diff($a->ends_at);
                    if (now()->lessThan($a->ends_at)) {
                        $hours = ($diff->d * 24) + $diff->h;
                        $countdown = sprintf('%02d:%02d:%02d', $hours, $diff->i, $diff->s);
                    }
                }
                return [
                    'id'           => $a->id,
                    'name'         => $a->title,
                    'image'        => $a->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=200&q=80',
                    'currentPrice' => (float) $a->current_price,
                    'countdown'    => $countdown,
                    'endsAt'       => $a->ends_at?->toIso8601String(),
                ];
            });

        // 8. Popular Auctions (Active, ordered by bids count desc, limit 4)
        $popularAuctions = Auction::where('status', 'active')
            ->withCount(['bids', 'watchers'])
            ->with(['seller', 'mainImage'])
            ->orderByDesc('bids_count')
            ->limit(4)
            ->get()
            ->map(function ($a) {
                return [
                    'id'           => $a->id,
                    'name'         => $a->title,
                    'seller'       => $a->seller?->full_name ?? '—',
                    'bids'         => $a->bids_count,
                    'viewers'      => $a->watchers_count,
                    'image'        => $a->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=200&q=80',
                ];
            });

        // 9. Active Users (Users with most bids)
        $activeUsers = User::withCount('bids')
            ->orderByDesc('bids_count')
            ->limit(4)
            ->get()
            ->map(function ($u) {
                $initials = collect(explode(' ', $u->full_name))
                    ->map(fn($w) => mb_substr($w, 0, 1))
                    ->take(2)
                    ->join('');

                // count auctions created
                $auctionsCreated = Auction::where('seller_id', $u->id)->count();

                return [
                    'id'       => $u->id,
                    'name'     => $u->full_name,
                    'initials' => strtoupper($initials),
                    'bids'     => $u->bids_count,
                    'auctions' => $auctionsCreated,
                ];
            });

        // 10. Financial Transactions
        // total transaction value = final prices of all won auctions
        $totalTransactions = (float) AuctionWinner::sum('final_price');
        $averageTransaction = (float) AuctionWinner::avg('final_price');
        $commissionPlatform = (float) ($totalTransactions * 0.08); // 8% commission

        return response()->json([
            'mainStats' => [
                [
                    'label' => 'Total Pengguna',
                    'value' => number_format($totalUsers, 0, ',', '.'),
                    'sub'   => '+' . User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() . ' pengguna baru bulan ini',
                    'badge' => 'All Users',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'dark'  => false,
                ],
                [
                    'label' => 'Total Lelang',
                    'value' => number_format($totalAuctions, 0, ',', '.'),
                    'sub'   => 'Total seluruh lelang dibuat',
                    'badge' => 'All time',
                    'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'dark'  => true,
                ],
                [
                    'label' => 'Lelang Aktif',
                    'value' => number_format($activeAuctions, 0, ',', '.'),
                    'sub'   => 'Sedang berlangsung saat ini',
                    'badge' => 'Live',
                    'icon'  => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'dark'  => false,
                ],
                [
                    'label' => 'Total Penawaran',
                    'value' => number_format($totalBids, 0, ',', '.'),
                    'sub'   => '+' . Bid::whereDate('created_at', now()->toDateString())->count() . ' hari ini',
                    'badge' => 'Bids placed',
                    'icon'  => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'dark'  => false,
                ],
            ],
            'auctionStatus'      => $statusData,
            'topCategories'      => $topCategories,
            'monthlyChart'       => $monthlyChart,
            'dailyBidsChart'     => $dailyBidsChart,
            'systemActivities'   => $systemActivities,
            'endingAuctions'     => $endingSoon,
            'popularAuctions'    => $popularAuctions,
            'activeUsers'        => $activeUsers,
            'financial'          => [
                'totalTransactions'   => $totalTransactions,
                'averageTransaction' => $averageTransaction,
                'commissionPlatform'  => $commissionPlatform,
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $auctions = Auction::withTrashed()
            ->with(['mainImage', 'category', 'seller'])
            ->withCount(['bids', 'watchers'])
            ->latest()
            ->get();

        $sellerIds = $auctions->pluck('seller_id')->unique();
        $sellerAuctionsCounts = Auction::whereIn('seller_id', $sellerIds)
            ->groupBy('seller_id')
            ->selectRaw('seller_id, count(*) as count')
            ->pluck('count', 'seller_id');

        $statusMap = [
            'scheduled' => 'upcoming',
            'active'    => 'active',
            'ended'     => 'ended',
            'cancelled' => 'cancelled',
        ];

        $transformed = $auctions->map(function ($a) use ($sellerAuctionsCounts, $statusMap) {
            $status = $statusMap[$a->status] ?? $a->status;
            if ($a->trashed()) {
                $status = 'deleted';
            }

            return [
                'id'             => $a->id,
                'name'           => $a->title,
                'seller'         => $a->seller?->full_name ?? '—',
                'sellerEmail'    => $a->seller?->email ?? '—',
                'sellerAuctions' => $sellerAuctionsCounts[$a->seller_id] ?? 0,
                'category'       => $a->category?->name ?? '—',
                'image'          => $a->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=200&q=80',
                'startPrice'     => (float) $a->starting_price,
                'currentPrice'   => (float) $a->current_price,
                'totalBids'      => $a->bids_count ?? 0,
                'watching'       => $a->watchers_count ?? 0,
                'status'         => $status,
                'startsAt'       => $a->starts_at?->toIso8601String(),
                'endsAt'         => $a->ends_at?->toIso8601String(),
                'startDate'      => $a->starts_at ? $a->starts_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') : '—',
                'endDate'        => $a->ends_at ? $a->ends_at->setTimezone('Asia/Makassar')->format('d M Y, H.i') . ' WITA' : '—',
                'description'    => $a->description,
            ];
        });

        // Compute summary counts
        $total = Auction::withTrashed()->count();
        $active = Auction::where('status', 'active')->count();
        $upcoming = Auction::where('status', 'scheduled')->count();
        $ended = Auction::where('status', 'ended')->count();
        $deleted = Auction::onlyTrashed()->count();

        // System alerts
        $systemAlerts = [];
        if ($upcoming > 0) {
            $systemAlerts[] = [
                'text'   => "Terdapat {$upcoming} lelang yang menunggu verifikasi admin.",
                'action' => "Verifikasi Sekarang",
                'dark'   => true,
                'icon'   => "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
            ];
        }

        // ending in 1 hour
        $endingSoonCount = Auction::where('status', 'active')
            ->where('ends_at', '>', now())
            ->where('ends_at', '<=', now()->addHour())
            ->count();
        if ($endingSoonCount > 0) {
            $systemAlerts[] = [
                'text'   => "Terdapat {$endingSoonCount} lelang yang akan berakhir dalam 1 jam ke depan.",
                'action' => "Pantau",
                'dark'   => false,
                'icon'   => "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
            ];
        }

        // Segera berakhir list
        $endingSoonList = Auction::where('status', 'active')
            ->with(['mainImage'])
            ->withCount('bids')
            ->orderBy('ends_at')
            ->limit(3)
            ->get()
            ->map(function ($a) {
                $countdown = '00:00:00';
                if ($a->ends_at) {
                    $diff = now()->diff($a->ends_at);
                    if (now()->lessThan($a->ends_at)) {
                        $hours = ($diff->d * 24) + $diff->h;
                        $countdown = sprintf('%02d:%02d:%02d', $hours, $diff->i, $diff->s);
                    }
                }
                return [
                    'id'           => $a->id,
                    'name'         => $a->title,
                    'image'        => $a->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=100&q=80',
                    'currentPrice' => (float) $a->current_price,
                    'totalBids'    => $a->bids_count,
                    'countdown'    => $countdown,
                    'endsAt'       => $a->ends_at?->toIso8601String(),
                ];
            });

        // Most active list (top 4 active by bids_count)
        $mostActiveList = Auction::where('status', 'active')
            ->with(['mainImage'])
            ->withCount(['bids', 'watchers'])
            ->orderByDesc('bids_count')
            ->limit(4)
            ->get()
            ->map(function ($a) {
                return [
                    'id'           => $a->id,
                    'name'         => $a->title,
                    'image'        => $a->mainImage?->url ?? 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?w=100&q=80',
                    'totalBids'    => $a->bids_count,
                    'watching'     => $a->watchers_count,
                    'currentPrice' => (float) $a->current_price,
                ];
            });

        return response()->json([
            'auctions'     => $transformed,
            'categories'   => Category::pluck('name')->toArray(),
            'stats'        => [
                ['label' => 'Total Lelang', 'value' => (string) $total, 'sub' => 'Seluruh lelang dibuat', 'filter' => 'all', 'dark' => false, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Lelang Aktif', 'value' => (string) $active, 'sub' => 'Sedang berlangsung', 'filter' => 'active', 'dark' => true, 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                ['label' => 'Akan Datang', 'value' => (string) $upcoming, 'sub' => 'Belum dimulai', 'filter' => 'upcoming', 'dark' => false, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['label' => 'Dihapus', 'value' => (string) $deleted, 'sub' => 'Lelang yang dihapus', 'filter' => 'deleted', 'dark' => false, 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
            ],
            'statusFilters' => [
                ['label' => 'Semua', 'value' => 'all', 'count' => $total],
                ['label' => 'Akan Datang', 'value' => 'upcoming', 'count' => $upcoming],
                ['label' => 'Sedang Berlangsung', 'value' => 'active', 'count' => $active],
                ['label' => 'Selesai', 'value' => 'ended', 'count' => $ended],
                ['label' => 'Dihapus', 'value' => 'deleted', 'count' => $deleted],
            ],
            'systemAlerts' => $systemAlerts,
            'endingSoon'   => $endingSoonList,
            'mostActive'   => $mostActiveList,
        ]);
    }

    /**
     * Activate a scheduled auction.
     */
    public function activate($id): JsonResponse
    {
        try {
            $auction = Auction::withTrashed()->findOrFail($id);

            if ($auction->status !== 'scheduled') {
                return response()->json(['message' => 'Lelang hanya dapat diaktifkan jika berstatus Akan Datang.'], 422);
            }

            $now = now();
            $durationSeconds = 86400; // default 24h
            if ($auction->starts_at && $auction->ends_at) {
                $durationSeconds = max(60, $auction->ends_at->diffInSeconds($auction->starts_at));
            }

            $auction->status = 'active';
            $auction->starts_at = $now;
            $auction->ends_at = $now->copy()->addSeconds($durationSeconds);
            $auction->save();

            return response()->json([
                'message' => 'Lelang berhasil diaktifkan.',
                'auction' => $auction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengaktifkan lelang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force end an active auction.
     */
    public function forceEnd($id): JsonResponse
    {
        try {
            $auction = Auction::withTrashed()->findOrFail($id);

            if ($auction->status !== 'active') {
                return response()->json(['message' => 'Hanya lelang aktif yang dapat dihentikan.'], 422);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($auction) {
                // Lock the auction
                $lockedAuction = Auction::lockForUpdate()->find($auction->id);

                if ($lockedAuction->status !== 'active') {
                    return;
                }

                // Get the highest active bid
                $highestBid = Bid::where('auction_id', $lockedAuction->id)
                    ->where('status', 'active')
                    ->orderByDesc('amount')
                    ->first();

                if ($highestBid) {
                    // Mark this bid as won
                    $highestBid->update(['status' => 'won']);

                    // Declare winner
                    $winner = AuctionWinner::create([
                        'auction_id'     => $lockedAuction->id,
                        'winner_id'      => $highestBid->bidder_id,
                        'winning_bid_id' => $highestBid->id,
                        'final_price'    => $highestBid->amount,
                    ]);

                    // Set auction status to ended
                    $lockedAuction->status = 'ended';
                    $lockedAuction->ends_at = now();
                    $lockedAuction->save();

                    // Notify winner
                    $winnerUser = $highestBid->bidder;
                    $winnerNotif = \App\Models\Notification::create([
                        'user_id' => $highestBid->bidder_id,
                        'type'    => 'auction_won',
                        'channel' => 'private',
                        'title'   => 'Lelang Dimenangkan!',
                        'body'    => "Selamat! Anda telah memenangkan lelang \"" . $lockedAuction->title . "\" dengan penawaran tertinggi sebesar Rp " . number_format($highestBid->amount) . ".",
                        'data'    => [
                            'auction_id'    => $lockedAuction->id,
                            'auction_title' => $lockedAuction->title,
                            'price'         => (float) $highestBid->amount,
                        ],
                    ]);
                    event(new \App\Events\NotificationSent($winnerNotif));

                    // Notify seller
                    $sellerNotif = \App\Models\Notification::create([
                        'user_id' => $lockedAuction->seller_id,
                        'type'    => 'auction_ended',
                        'channel' => 'private',
                        'title'   => 'Barang Anda Terjual!',
                        'body'    => "Lelang \"" . $lockedAuction->title . "\" Anda telah selesai dan dimenangkan oleh " . ($winnerUser->full_name ?? 'Penawar') . " senilai Rp " . number_format($highestBid->amount) . ".",
                        'data'    => [
                            'auction_id'    => $lockedAuction->id,
                            'auction_title' => $lockedAuction->title,
                            'price'         => (float) $highestBid->amount,
                        ],
                    ]);
                    event(new \App\Events\NotificationSent($sellerNotif));

                    // Broadcast AuctionEnded event to viewers
                    event(new \App\Events\AuctionEnded(
                        $lockedAuction->id,
                        'ended',
                        $highestBid->bidder_id,
                        $winnerUser->full_name ?? 'Pemenang',
                        (float) $highestBid->amount
                    ));

                } else {
                    // Ended without bids
                    $lockedAuction->status = 'ended';
                    $lockedAuction->ends_at = now();
                    $lockedAuction->save();

                    // Notify seller
                    $sellerNotif = \App\Models\Notification::create([
                        'user_id' => $lockedAuction->seller_id,
                        'type'    => 'auction_ended',
                        'channel' => 'private',
                        'title'   => 'Lelang Berakhir Tanpa Penawaran',
                        'body'    => "Lelang \"" . $lockedAuction->title . "\" Anda telah berakhir, namun tidak ada penawaran yang masuk.",
                        'data'    => [
                            'auction_id'    => $lockedAuction->id,
                            'auction_title' => $lockedAuction->title,
                            'price'         => 0.0,
                        ],
                    ]);
                    event(new \App\Events\NotificationSent($sellerNotif));

                    // Broadcast AuctionEnded event to viewers
                    event(new \App\Events\AuctionEnded(
                        $lockedAuction->id,
                        'ended',
                        null,
                        null,
                        0.0
                    ));
                }
            });

            return response()->json([
                'message' => 'Lelang berhasil dihentikan.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghentikan lelang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an auction (permanent force delete - admin only).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $auction = Auction::withTrashed()->findOrFail($id);
            \Illuminate\Support\Facades\DB::transaction(function () use ($auction) {
                // Delete photos from disk and database
                foreach ($auction->images as $img) {
                    \Storage::disk($img->storage_disk)->delete($img->image_path);
                    $img->delete();
                }

                // Delete bid history
                $auction->bids()->delete();

                // Delete winner info
                $auction->winner()->delete();

                // Delete watchlists
                \DB::table('watchlists')->where('auction_id', $auction->id)->delete();

                // Force delete the auction
                $auction->forceDelete();
            });

            return response()->json([
                'message' => 'Lelang berhasil dihapus secara permanen oleh admin.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus lelang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get auction details (including soft-deleted - admin only).
     */
    public function show($id): JsonResponse
    {
        try {
            $auction = Auction::withTrashed()->with([
                'seller',
                'category',
                'images',
                'bids.bidder',
                'winner.winner',
            ])->findOrFail($id);

            $statusMap = [
                'scheduled' => 'upcoming',
                'active'    => 'active',
                'ended'     => 'ended',
            ];

            $images = $auction->images->sortBy('sort_order')->values();

            // Format bids
            $bids = $auction->bids->map(function ($bid) {
                return [
                    'id'     => $bid->id,
                    'name'   => $bid->bidder?->full_name ?? 'Anonim',
                    'email'  => $bid->bidder?->email ?? '',
                    'avatar' => $bid->bidder?->avatar
                        ? \Storage::disk(config('filesystems.default'))->url($bid->bidder->avatar)
                        : 'https://i.pravatar.cc/32?u=' . $bid->bidder_id,
                    'amount' => (float) $bid->amount,
                    'time'   => $bid->placed_at
                        ? \Carbon\Carbon::parse($bid->placed_at)->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A')
                        : $bid->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                    'status' => ($bid->status === 'active' || $bid->status === 'won') ? 'highest' : 'outbid',
                ];
            })->sortByDesc('amount')->values();

            // System Activities (timeline)
            $systemActivity = [];
            // 1. Creation activity
            $systemActivity[] = [
                'text' => 'Lelang dibuat oleh ' . ($auction->seller?->full_name ?? 'Penjual'),
                'time' => $auction->created_at->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                'icon' => 'M12 4v16m8-8H4',
            ];

            // 2. Bid activities
            foreach ($auction->bids->sortBy('created_at') as $bid) {
                $systemActivity[] = [
                    'text' => 'Penawaran masuk sebesar Rp ' . number_format($bid->amount, 0, ',', '.') . ' oleh ' . ($bid->bidder?->full_name ?? 'Anonim'),
                    'time' => ($bid->placed_at ? \Carbon\Carbon::parse($bid->placed_at) : $bid->created_at)->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                ];
            }

            // 3. Victory/Cancellation activities
            if ($auction->status === 'cancelled') {
                $systemActivity[] = [
                    'text' => 'Lelang dibatalkan oleh Administrator',
                    'time' => $auction->updated_at->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                    'icon' => 'M6 18L18 6M6 6l12 12',
                ];
            } elseif ($auction->status === 'ended') {
                if ($auction->winner) {
                    $systemActivity[] = [
                        'text' => 'Pemenang berhasil ditentukan: ' . ($auction->winner->winner?->full_name ?? '—'),
                        'time' => \Carbon\Carbon::parse($auction->winner->created_at)->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    ];
                } else {
                    $systemActivity[] = [
                        'text' => 'Lelang berakhir tanpa pemenang',
                        'time' => $auction->ends_at->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    ];
                }
            }

            // Winner info
            $winner = null;
            if ($auction->winner) {
                $winner = [
                    'name'          => $auction->winner->winner?->full_name ?? '—',
                    'finalPrice'    => (float) $auction->winner->final_price,
                    'closedAt'      => \Carbon\Carbon::parse($auction->winner->created_at)->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                    'paymentStatus' => $auction->winner->payment_status === 'paid' ? 'Lunas' : 'Belum Bayar',
                ];
            }

            // Seller stats
            $seller = $auction->seller;
            $sellerStats = [
                'name'              => $seller?->full_name ?? '—',
                'email'             => $seller?->email ?? '—',
                'phone'             => $seller?->phone ?? '—',
                'joinDate'          => $seller?->created_at ? $seller->created_at->setTimezone('Asia/Makassar')->format('d M Y') : '—',
                'status'            => 'active',
                'totalAuctions'     => $seller ? Auction::withTrashed()->where('seller_id', $seller->id)->count() : 0,
                'completedAuctions' => $seller ? Auction::withTrashed()->where('seller_id', $seller->id)->where('status', 'ended')->count() : 0,
                'activeAuctions'    => $seller ? Auction::withTrashed()->where('seller_id', $seller->id)->where('status', 'active')->count() : 0,
                'totalSold'         => $seller ? Auction::withTrashed()->where('seller_id', $seller->id)->where('status', 'ended')->has('winner')->count() : 0,
                'avatar'            => $seller?->avatar ? \Storage::disk(config('filesystems.default'))->url($seller->avatar) : null,
            ];

            $uniqueBiddersCount = $auction->bids->pluck('bidder_id')->unique()->count();
            
            // Format status: if soft-deleted, status is 'deleted'
            $status = $auction->deleted_at ? 'deleted' : ($statusMap[$auction->status] ?? $auction->status);

            // Notifications
            $notifications = [];
            if ($auction->deleted_at) {
                $notifications[] = [
                    'type' => 'warning',
                    'text' => 'Lelang ini telah dihapus (soft delete) oleh penjual pada ' . $auction->deleted_at->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A') . '.',
                ];
            } else if ($auction->status === 'active' && $auction->ends_at->diffInHours(now()) < 24) {
                $notifications[] = [
                    'type' => 'warning',
                    'text' => 'Lelang akan berakhir dalam waktu kurang dari 24 jam.',
                ];
            }

            return response()->json([
                'auction' => [
                    'id'            => $auction->id,
                    'name'          => $auction->title,
                    'category'      => $auction->category?->name ?? 'Lainnya',
                    'description'   => $auction->description,
                    'images'        => $images->map(fn($img) => $img->url)->values(),
                    'startPrice'    => (float) $auction->starting_price,
                    'currentPrice'  => (float) $auction->current_price,
                    'minIncrement'  => (float) $auction->bid_increment,
                    'totalBids'     => $auction->bids->count(),
                    'status'        => $status,
                    'startDate'     => $auction->starts_at?->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                    'endDate'       => $auction->ends_at?->setTimezone('Asia/Makassar')->format('d M Y, H.i \W\I\T\A'),
                    'endTimestamp'  => $auction->ends_at?->timestamp * 1000,
                    'seller'        => $sellerStats,
                    'stats' => [
                        'totalViewers'   => 12 + $auction->bids->count() * 3,
                        'currentViewers' => $auction->status === 'active' ? rand(2, 6) : 0,
                        'bidsLastHour'   => $auction->bids->where('created_at', '>=', now()->subHour())->count(),
                        'bidsLast24h'    => $auction->bids->where('created_at', '>=', now()->subDay())->count(),
                        'lowestBid'      => $auction->bids->min('amount') ?: $auction->starting_price,
                        'uniqueBidders'  => $uniqueBiddersCount,
                        'lastActivity'   => $auction->bids->count() > 0 
                            ? 'Penawaran baru sebesar Rp ' . number_format($auction->bids->max('amount'), 0, ',', '.') . ' oleh ' . ($auction->bids->sortByDesc('amount')->first()->bidder?->full_name ?? 'Anonim') . ' pada ' . ($auction->bids->sortByDesc('amount')->first()->placed_at ? \Carbon\Carbon::parse($auction->bids->sortByDesc('amount')->first()->placed_at) : $auction->bids->sortByDesc('amount')->first()->created_at)->setTimezone('Asia/Makassar')->format('d M Y \p\u\k\u\l H.i \W\I\T\A') . '.'
                            : 'Lelang dibuat pada ' . $auction->created_at->setTimezone('Asia/Makassar')->format('d M Y \p\u\k\u\l H.i \W\I\T\A') . '.',
                    ],
                    'antiSniping' => [
                        'active'        => true,
                        'lastExtension' => 'Tidak ada',
                        'note'          => 'Lelang diperpanjang secara otomatis jika terdapat penawaran pada 30 detik terakhir sebelum penutupan.',
                    ],
                    'buyNow' => [
                        'used'  => $auction->bids->where('amount', '>=', $auction->buy_now_price)->count() > 0 && $auction->buy_now_price > 0,
                        'price' => $auction->buy_now_price ? (float) $auction->buy_now_price : 0,
                    ],
                    'notifications' => $notifications,
                    'winner'        => $winner,
                ],
                'bidHistory' => $bids,
                'systemActivity' => $systemActivity,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memuat detail lelang: ' . $e->getMessage()
            ], 500);
        }
    }
}

