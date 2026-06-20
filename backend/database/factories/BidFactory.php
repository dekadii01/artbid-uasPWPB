<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bid>
 */
class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'bidder_id' => User::factory(),
            'amount' => fake()->numberBetween(1000000, 20000000),
            'status' => 'active',
            'placed_at' => now(),
        ];
    }
}
