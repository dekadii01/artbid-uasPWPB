<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction_id;
    public $amount;
    public $bidder_id;
    public $bidder_name_masked;
    public $highest_bid;
    public $bids_count;
    public $ends_at;
    public $bid;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $auctionId,
        float $amount,
        int $bidderId,
        string $bidderNameMasked,
        float $highestBid,
        int $bidsCount,
        ?string $endsAt,
        array $bidDetails
    ) {
        $this->auction_id = $auctionId;
        $this->amount = $amount;
        $this->bidder_id = $bidderId;
        $this->bidder_name_masked = $bidderNameMasked;
        $this->highest_bid = $highestBid;
        $this->bids_count = $bidsCount;
        $this->ends_at = $endsAt;
        $this->bid = $bidDetails;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('auction.' . $this->auction_id),
        ];
    }
}
