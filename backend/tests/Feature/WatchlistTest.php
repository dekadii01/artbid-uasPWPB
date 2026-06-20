<?php

use App\Models\Auction;
use App\Models\User;
use App\Models\Watchlist;
use Laravel\Sanctum\Sanctum;

test('buyer can toggle watchlist status of an auction', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->create();

    Sanctum::actingAs($user);

    // Toggle ON (add)
    $response = $this->postJson("/api/auctions/{$auction->id}/watchlist");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'watchlisted' => true,
            'message' => 'Ditambahkan ke watchlist.',
        ]);

    $this->assertDatabaseHas('watchlists', [
        'user_id' => $user->id,
        'auction_id' => $auction->id,
    ]);

    // Toggle OFF (remove)
    $response = $this->postJson("/api/auctions/{$auction->id}/watchlist");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'watchlisted' => false,
            'message' => 'Dihapus dari watchlist.',
        ]);

    $this->assertDatabaseMissing('watchlists', [
        'user_id' => $user->id,
        'auction_id' => $auction->id,
    ]);
});

test('buyer can retrieve watchlist list', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->create();
    
    Watchlist::create([
        'user_id' => $user->id,
        'auction_id' => $auction->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/watchlist');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => ['id', 'name', 'currentPrice', 'status']
            ],
        ]);
});
