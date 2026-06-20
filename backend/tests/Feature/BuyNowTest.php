<?php

use App\Models\Auction;
use App\Models\User;
use App\Models\AuctionWinner;
use Laravel\Sanctum\Sanctum;

test('buyer can buy an auction instantly using Buy Now', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'status' => 'active',
        'buy_now_price' => 15000000.0,
    ]);

    Sanctum::actingAs($buyer);

    $response = $this->postJson("/api/auctions/{$auction->id}/buy-now");

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Lelang berhasil dibeli langsung.']);

    // Check auction is ended and price updated
    $auction->refresh();
    $this->assertEquals('ended', $auction->status);
    $this->assertEquals(15000000.0, $auction->current_price);

    // Check winner is declared
    $this->assertDatabaseHas('auction_winners', [
        'auction_id' => $auction->id,
        'winner_id' => $buyer->id,
        'final_price' => 15000000.0,
    ]);

    // Check notifications sent
    $this->assertDatabaseHas('notifications', [
        'user_id' => $buyer->id,
        'type' => 'auction_won',
    ]);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $seller->id,
        'type' => 'auction_ended',
    ]);
});

test('buy now is rejected if no buy now price is set', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'status' => 'active',
        'buy_now_price' => null,
    ]);

    Sanctum::actingAs($buyer);

    $response = $this->postJson("/api/auctions/{$auction->id}/buy-now");

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'Fitur Buy Now tidak tersedia untuk lelang ini.']);
});
