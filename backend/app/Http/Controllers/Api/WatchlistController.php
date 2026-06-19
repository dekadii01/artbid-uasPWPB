<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    /**
     * Get paginated watchlist for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Load watched auctions with main image and bids count
        $auctions = $user->watchedAuctions()
            ->with(['mainImage', 'category:id,name'])
            ->withCount('bids')
            ->latest()
            ->paginate(12);

        $statusMap = [
            'scheduled' => 'upcoming',
            'active'    => 'live',
            'ended'     => 'ended',
        ];

        $auctions->getCollection()->transform(function (Auction $auction) use ($statusMap) {
            $mainImageUrl = $auction->mainImage?->url ?? null;
            $watchingCount = Watchlist::where('auction_id', $auction->id)->count();
            $hasBid = $auction->bids()->where('bidder_id', auth()->id())->exists();

            // Calculate time left object
            $timeLeft = null;
            if ($auction->status === 'active' && $auction->ends_at) {
                $diff = now()->diff($auction->ends_at);
                $timeLeft = [
                    'd' => str_pad($diff->d + ($diff->m * 30), 2, '0', STR_PAD_LEFT),
                    'h' => str_pad($diff->h, 2, '0', STR_PAD_LEFT),
                    'm' => str_pad($diff->i, 2, '0', STR_PAD_LEFT),
                ];
            } elseif ($auction->status === 'scheduled' && $auction->starts_at) {
                $diff = now()->diff($auction->starts_at);
                $timeLeft = [
                    'd' => '00',
                    'h' => str_pad($diff->h + ($diff->d * 24), 2, '0', STR_PAD_LEFT),
                    'm' => str_pad($diff->i, 2, '0', STR_PAD_LEFT),
                ];
            }

            return [
                'id'           => $auction->id,
                'name'         => $auction->title,
                'artist'       => $auction->artist ?? '—',
                'category'     => $auction->category?->name,
                'image'        => $mainImageUrl,
                'currentPrice' => (float) $auction->current_price,
                'totalBids'    => $auction->bids_count ?? 0,
                'watching'     => $watchingCount,
                'hasBid'       => $hasBid,
                'status'       => $statusMap[$auction->status] ?? $auction->status,
                'timeLeft'     => $timeLeft,
                'endDate'      => $auction->ends_at ? $auction->ends_at->setTimezone('Asia/Makassar')->format('d M Y') : null,
                'addedAt'      => $auction->pivot->created_at ? $auction->pivot->created_at->timestamp : $auction->created_at->timestamp,
            ];
        });

        // Fetch latest 5 bids on any of the user's watched auctions
        $watchedAuctionIds = $user->watchedAuctions()->pluck('auctions.id');
        $activities = \App\Models\Bid::whereIn('auction_id', $watchedAuctionIds)
            ->with(['auction:id,title', 'bidder:id,first_name,last_name'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($bid) {
                $timeStr = $bid->created_at->diffForHumans();
                // Translate diffForHumans to Indonesian
                $timeStr = str_replace(
                    ['seconds', 'second', 'minutes', 'minute', 'hours', 'hour', 'days', 'day', 'weeks', 'week', 'months', 'month', 'years', 'year', 'ago', 'from now'],
                    ['detik', 'detik', 'menit', 'menit', 'jam', 'jam', 'hari', 'hari', 'minggu', 'minggu', 'bulan', 'bulan', 'tahun', 'tahun', 'yang lalu', 'dari sekarang'],
                    $timeStr
                );
                return [
                    'type' => 'price',
                    'text' => 'Penawaran baru pada <strong class="text-black">"' . e($bid->auction->title) . '"</strong> naik menjadi <strong class="text-black">Rp ' . number_format($bid->amount, 0, ',', '.') . '</strong>',
                    'time' => $timeStr,
                ];
            });

        $responseData = $auctions->toArray();
        $responseData['activities'] = $activities;

        return response()->json($responseData);
    }

    /**
     * Toggle watchlist status for an auction.
     */
    public function store(Request $request, Auction $auction): JsonResponse
    {
        $user = $request->user();

        $watchlist = Watchlist::where('user_id', $user->id)
            ->where('auction_id', $auction->id)
            ->first();

        if ($watchlist) {
            $watchlist->delete();
            return response()->json([
                'watchlisted' => false,
                'message'     => 'Dihapus dari watchlist.',
            ]);
        }

        Watchlist::create([
            'user_id'    => $user->id,
            'auction_id' => $auction->id,
        ]);

        return response()->json([
            'watchlisted' => true,
            'message'     => 'Ditambahkan ke watchlist.',
        ]);
    }
}
