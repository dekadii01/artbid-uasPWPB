<?php

namespace App\Console\Commands;

use App\Events\AuctionEnded;
use App\Events\NotificationSent;
use App\Models\Auction;
use App\Models\AuctionWinner;
use App\Models\Bid;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseEndedAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auctions:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close auctions that have reached their ends_at time and declare winners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for ended auctions...');

        // Find all active auctions where ends_at is <= now
        $endedAuctions = Auction::where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        if ($endedAuctions->isEmpty()) {
            $this->info('No auctions to close.');
            return 0;
        }

        foreach ($endedAuctions as $auction) {
            $this->info("Closing auction ID {$auction->id}: '{$auction->title}'");

            try {
                DB::transaction(function () use ($auction) {
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
                        $lockedAuction->save();

                        // Notify winner
                        $winnerUser = $highestBid->bidder;
                        $winnerNotif = Notification::create([
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
                        event(new NotificationSent($winnerNotif));

                        // Notify seller
                        $sellerNotif = Notification::create([
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
                        event(new NotificationSent($sellerNotif));

                        // Broadcast AuctionEnded event to viewers
                        event(new AuctionEnded(
                            $lockedAuction->id,
                            'ended',
                            $highestBid->bidder_id,
                            $winnerUser->full_name ?? 'Pemenang',
                            (float) $highestBid->amount
                        ));

                    } else {
                        // Ended without bids
                        $lockedAuction->status = 'ended';
                        $lockedAuction->save();

                        // Notify seller
                        $sellerNotif = Notification::create([
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
                        event(new NotificationSent($sellerNotif));

                        // Broadcast AuctionEnded event to viewers
                        event(new AuctionEnded(
                            $lockedAuction->id,
                            'ended',
                            null,
                            null,
                            0.0
                        ));
                    }
                });

                $this->info("Successfully closed auction ID {$auction->id}");

            } catch (\Exception $e) {
                $this->error("Failed to close auction ID {$auction->id}: " . $e->getMessage());
            }
        }

        return 0;
    }
}
