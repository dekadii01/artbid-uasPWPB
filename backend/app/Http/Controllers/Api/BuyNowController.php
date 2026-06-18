<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionWinner;
use App\Models\Bid;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyNowController extends Controller
{
    /**
     * Lakukan Buy Now untuk memenangkan lelang secara instan.
     */
    public function store(Request $request, Auction $auction): JsonResponse
    {
        $user = $request->user();

        if (is_null($auction->buy_now_price)) {
            return response()->json(['message' => 'Fitur Buy Now tidak tersedia untuk lelang ini.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($auction, $user) {
                // Lock data lelang agar tidak bertabrakan dengan bid lain
                $lockedAuction = Auction::lockForUpdate()->findOrFail($auction->id);

                if ($lockedAuction->status !== 'active') {
                    return [
                        'success' => false,
                        'message' => 'Lelang tidak sedang berlangsung.',
                        'code'    => 422,
                    ];
                }

                if ($lockedAuction->seller_id === $user->id) {
                    return [
                        'success' => false,
                        'message' => 'Anda tidak dapat membeli lelang Anda sendiri.',
                        'code'    => 422,
                    ];
                }

                // Tandai semua bid active sebelumnya sebagai outbid
                Bid::where('auction_id', $lockedAuction->id)
                    ->where('status', 'active')
                    ->update(['status' => 'outbid']);

                // Buat bid baru sebagai bid pemenang
                $winningBid = Bid::create([
                    'auction_id' => $lockedAuction->id,
                    'bidder_id'  => $user->id,
                    'amount'     => $lockedAuction->buy_now_price,
                    'status'     => 'won',
                    'placed_at'  => now(),
                ]);

                // Buat pemenang lelang
                $winner = AuctionWinner::create([
                    'auction_id'     => $lockedAuction->id,
                    'winner_id'      => $user->id,
                    'winning_bid_id' => $winningBid->id,
                    'final_price'    => $lockedAuction->buy_now_price,
                ]);

                // Update data lelang
                $lockedAuction->current_price = $lockedAuction->buy_now_price;
                $lockedAuction->status = 'ended';
                $lockedAuction->save();

                return [
                    'success' => true,
                    'bid'     => $winningBid,
                    'winner'  => $winner,
                    'code'    => 201,
                ];
            });

            if (!$result['success']) {
                return response()->json(['message' => $result['message']], $result['code']);
            }

            // Buat Notifikasi untuk pembeli (pemenang)
            $buyerNotif = Notification::create([
                'user_id' => $user->id,
                'type'    => 'auction_won',
                'channel' => 'private',
                'title'   => 'Lelang Berhasil Dimenangkan!',
                'body'    => "Selamat! Anda memenangkan lelang \"" . $auction->title . "\" secara instan via Buy Now senilai Rp " . number_format($auction->buy_now_price) . ".",
                'data'    => [
                    'auction_id'    => $auction->id,
                    'auction_title' => $auction->title,
                    'price'         => (float) $auction->buy_now_price,
                ],
            ]);
            event(new \App\Events\NotificationSent($buyerNotif));

            // Buat Notifikasi untuk penjual (seller)
            $sellerNotif = Notification::create([
                'user_id' => $auction->seller_id,
                'type'    => 'auction_ended',
                'channel' => 'private',
                'title'   => 'Barang Anda Terjual!',
                'body'    => "Lelang \"" . $auction->title . "\" Anda telah selesai karena dibeli langsung (Buy Now) oleh " . $user->full_name . " senilai Rp " . number_format($auction->buy_now_price) . ".",
                'data'    => [
                    'auction_id'    => $auction->id,
                    'auction_title' => $auction->title,
                    'price'         => (float) $auction->buy_now_price,
                ],
            ]);
            event(new \App\Events\NotificationSent($sellerNotif));

            // Dispatch AuctionEnded event
            event(new \App\Events\AuctionEnded(
                $auction->id,
                'ended',
                $user->id,
                $user->full_name,
                (float) $auction->buy_now_price
            ));

            return response()->json([
                'message' => 'Lelang berhasil dibeli langsung.',
                'winner'  => $result['winner'],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses Buy Now.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
