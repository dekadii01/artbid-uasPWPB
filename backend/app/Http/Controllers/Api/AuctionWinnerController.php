<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionWinnerController extends Controller
{
    /**
     * @group Auctions
     *
     * Tampilkan pemenang lelang (Publik).
     *
     * @urlParam auction integer required ID lelang. Example: 1
     *
     * @response 200 {
     *   "winner": {
     *     "name": "Budi Setiawan",
     *     "finalPrice": 12500000.0,
     *     "joinedAt": "2026-06-19T08:00:00Z"
     *   }
     * }
     * @response 404 {
     *   "message": "Lelang belum selesai atau tidak memiliki pemenang."
     * }
     */
    public function show(Auction $auction): JsonResponse
    {
        $auction->load('winner.winner');

        if (!$auction->winner) {
            return response()->json([
                'message' => 'Lelang belum selesai atau tidak memiliki pemenang.',
            ], 404);
        }

        return response()->json([
            'winner' => [
                'name'       => $auction->winner->winner?->full_name ?? '—',
                'finalPrice' => (float) $auction->winner->final_price,
                'joinedAt'   => $auction->winner->winner?->created_at?->toIso8601String(),
            ]
        ]);
    }
}
