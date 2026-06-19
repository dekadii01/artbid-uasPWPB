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
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
