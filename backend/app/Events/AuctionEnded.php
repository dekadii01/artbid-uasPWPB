<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auction_id;
    public $status;
    public $winner_id;
    public $winner_name;
    public $final_price;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $auctionId,
        string $status,
        ?int $winnerId,
        ?string $winnerName,
        float $finalPrice
    ) {
        $this->auction_id = $auctionId;
        $this->status = $status;
        $this->winner_id = $winnerId;
        $this->winner_name = $winnerName;
        $this->final_price = $finalPrice;
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
