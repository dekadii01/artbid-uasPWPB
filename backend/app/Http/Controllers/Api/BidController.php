<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBidRequest;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    /**
     * Tampilkan daftar penawaran lelang ini.
     */
    public function index(Auction $auction): JsonResponse
    {
        $bids = $auction->bids()
            ->with('bidder:id,first_name,last_name,avatar')
            ->orderByDesc('amount')
            ->get()
            ->map(function ($bid) {
                $name = $bid->bidder?->full_name ?? 'Anonim';
                $masked = strlen($name) > 6
                    ? substr($name, 0, 3) . '***' . substr($name, -3)
                    : substr($name, 0, 3) . '***';

                return [
                    'id'     => $bid->id,
                    'user'   => $masked,
                    'avatar' => $bid->bidder?->avatar
                        ? \Storage::disk(config('filesystems.default'))->url($bid->bidder->avatar)
                        : 'https://i.pravatar.cc/32?u=' . $bid->bidder_id,
                    'amount' => (float) $bid->amount,
                    'time'   => $bid->placed_at
                        ? \Carbon\Carbon::parse($bid->placed_at)->setTimezone('Asia/Makassar')->format('H:i:s')
                        : $bid->created_at->setTimezone('Asia/Makassar')->format('H:i:s'),
                    'status' => $bid->status,
                ];
            });

        return response()->json($bids);
    }

    /**
     * Kirim penawaran baru.
     */
    public function store(StoreBidRequest $request, Auction $auction): JsonResponse
    {
        $user = $request->user();
        $amount = (float) $request->input('amount');

        try {
            $result = DB::transaction(function () use ($auction, $user, $amount) {
                // Lock data auction agar tidak terjadi race condition saat bid bersamaan
                $lockedAuction = Auction::lockForUpdate()->findOrFail($auction->id);

                // Auto-start if starts_at is reached/passed
                if ($lockedAuction->status === 'scheduled' && $lockedAuction->starts_at && $lockedAuction->starts_at->isPast()) {
                    $lockedAuction->status = 'active';
                    $lockedAuction->save();
                }

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
                        'message' => 'Anda tidak dapat menawar lelang Anda sendiri.',
                        'code'    => 422,
                    ];
                }

                $minBid = (float) $lockedAuction->current_price + (float) $lockedAuction->bid_increment;
                if ($amount < $minBid) {
                    return [
                        'success' => false,
                        'message' => 'Tawaran minimal harus senilai ' . number_format($minBid) . '.',
                        'code'    => 422,
                    ];
                }

                // Ambil active bid sebelumnya untuk dikirimi notifikasi outbid
                $previousActiveBid = Bid::where('auction_id', $lockedAuction->id)
                    ->where('status', 'active')
                    ->first();

                // Set bid sebelumnya menjadi 'outbid'
                if ($previousActiveBid) {
                    $previousActiveBid->update(['status' => 'outbid']);
                }

                // Buat bid baru
                $newBid = Bid::create([
                    'auction_id' => $lockedAuction->id,
                    'bidder_id'  => $user->id,
                    'amount'     => $amount,
                    'status'     => 'active',
                    'placed_at'  => now(),
                ]);

                // Update harga saat ini pada lelang
                $lockedAuction->current_price = $amount;

                // Anti-sniping protection
                $extended = $lockedAuction->extendIfSniping();

                $lockedAuction->save();

                return [
                    'success'           => true,
                    'bid'               => $newBid,
                    'extended'          => $extended,
                    'previousActiveBid' => $previousActiveBid,
                    'code'              => 201,
                ];
            });

            if (!$result['success']) {
                return response()->json(['message' => $result['message']], $result['code']);
            }

            $newBid = $result['bid'];
            // Reload auction to get the latest updated fields (e.g. ends_at, current_price)
            $updatedAuction = Auction::find($auction->id);

            // Format bid data for broadcasting (matching transforms in AuctionController)
            $name = $user->full_name ?? 'Anonim';
            $masked = strlen($name) > 6
                ? substr($name, 0, 3) . '***' . substr($name, -3)
                : substr($name, 0, 3) . '***';

            $bidDetails = [
                'id'     => $newBid->id,
                'user'   => $masked,
                'avatar' => $user->avatar
                    ? \Storage::disk(config('filesystems.default'))->url($user->avatar)
                    : 'https://i.pravatar.cc/32?u=' . $newBid->bidder_id,
                'amount' => (float) $newBid->amount,
                'time'   => $newBid->placed_at
                    ? \Carbon\Carbon::parse($newBid->placed_at)->setTimezone('Asia/Makassar')->format('H:i:s')
                    : $newBid->created_at->setTimezone('Asia/Makassar')->format('H:i:s'),
                'status' => $newBid->status,
            ];

            // Dispatch BidPlaced event
            event(new \App\Events\BidPlaced(
                $auction->id,
                $amount,
                $user->id,
                $masked,
                (float) $updatedAuction->current_price,
                $updatedAuction->bids()->count(),
                $updatedAuction->ends_at?->toIso8601String(),
                $bidDetails
            ));

            // Kirim notifikasi 'outbid' di luar transaksi agar tidak memblokir DB
            $previousActiveBid = $result['previousActiveBid'];
            if ($previousActiveBid && $previousActiveBid->bidder_id !== $user->id) {
                $notif = Notification::create([
                    'user_id' => $previousActiveBid->bidder_id,
                    'type'    => 'outbid',
                    'channel' => 'private',
                    'title'   => 'Penawaran Anda tergeser!',
                    'body'    => "Penawaran Anda sebesar Rp " . number_format($previousActiveBid->amount) . " pada lelang \"" . $auction->title . "\" telah dilewati oleh penawar lain.",
                    'data'    => [
                        'auction_id'    => $auction->id,
                        'auction_title' => $auction->title,
                        'amount'        => (float) $amount,
                    ],
                ]);

                // Dispatch NotificationSent event for real-time notification
                event(new \App\Events\NotificationSent($notif));
            }

            return response()->json([
                'message'  => 'Penawaran berhasil diajukan.',
                'bid'      => $result['bid'],
                'extended' => $result['extended'],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses penawaran.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Riwayat penawaran user yang sedang login.
     */
    public function myBids(Request $request): JsonResponse
    {
        $user = $request->user();

        // Ambil penawaran tertinggi user di setiap lelang
        $bids = Bid::where('bidder_id', $user->id)
            ->with(['auction.mainImage', 'auction.category:id,name', 'auction.bids'])
            ->latest()
            ->paginate(12);

        // Map data agar siap pakai di frontend
        $bids->getCollection()->transform(function ($bid) {
            $auction = $bid->auction;
            if (!$auction) return null;

            $statusMap = [
                'scheduled' => 'upcoming',
                'active'    => 'live',
                'ended'     => 'ended',
            ];

            // Cek apakah bid tertinggi pada lelang ini adalah milik user ini
            $topBid = $auction->bids()->orderByDesc('amount')->first();
            $isLeading = $topBid && $topBid->bidder_id === $bid->bidder_id;

            $status = 'outbid';
            if ($auction->status === 'ended') {
                $status = $isLeading ? 'won' : 'lost';
            } else {
                $status = $isLeading ? 'leading' : 'outbid';
            }

            // Calculate time left object
            $timeLeft = null;
            if ($auction->status === 'active' && $auction->ends_at) {
                $diff = now()->diff($auction->ends_at);
                $timeLeft = [
                    'd' => str_pad($diff->d + ($diff->m * 30), 2, '0', STR_PAD_LEFT),
                    'h' => str_pad($diff->h, 2, '0', STR_PAD_LEFT),
                    'm' => str_pad($diff->i, 2, '0', STR_PAD_LEFT),
                ];
            }

            return [
                'id'          => $auction->id,
                'name'        => $auction->title,
                'artist'      => $auction->artist ?? '—',
                'category'    => $auction->category?->name,
                'image'       => $auction->mainImage?->url ?? null,
                'myBid'       => (float) $bid->amount,
                'topBid'      => $topBid ? (float) $topBid->amount : (float) $auction->current_price,
                'totalBids'   => $auction->bids()->count(),
                'lastBidTime' => $bid->created_at->setTimezone('Asia/Makassar')->format('d M Y • H:i') . ' WITA',
                'status'      => $status,
                'isActive'    => $auction->status === 'active',
                'timeLeft'    => $timeLeft,
                'endDate'     => $auction->ends_at ? $auction->ends_at->setTimezone('Asia/Makassar')->format('d M Y') : null,
            ];
        });

        // Hapus null values jika ada lelang terhapus
        $filteredCollection = $bids->getCollection()->filter()->values();
        $bids->setCollection($filteredCollection);

        return response()->json($bids);
    }
}
