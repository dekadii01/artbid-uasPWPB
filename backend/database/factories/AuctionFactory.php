<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition(): array
    {
        $startingPrice = fake()->numberBetween(1000000, 10000000);
        return [
            'seller_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(2),
            'category_id' => Category::factory(),
            'condition' => 'Sangat Baik',
            'artist' => fake()->name(),
            'year' => fake()->numberBetween(2000, 2025),
            'starting_price' => $startingPrice,
            'current_price' => $startingPrice,
            'bid_increment' => 250000,
            'buy_now_price' => $startingPrice * 2,
            'status' => 'active',
            'starts_at' => now()->subHours(1),
            'ends_at' => now()->addDays(7),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(8),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ended',
            'starts_at' => now()->subDays(8),
            'ends_at' => now()->subDays(1),
        ]);
    }
}
