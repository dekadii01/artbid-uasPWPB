<?php

use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Models\Notification;
use Laravel\Sanctum\Sanctum;

test('buyer can place a valid bid on active auction', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'starting_price' => 1000000,
        'current_price' => 1000000,
        'bid_increment' => 200000,
        'status' => 'active',
    ]);

    Sanctum::actingAs($buyer);

    $response = $this->postJson("/api/auctions/{$auction->id}/bids", [
        'amount' => 1300000,
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Penawaran berhasil diajukan.']);

    $this->assertDatabaseHas('bids', [
        'auction_id' => $auction->id,
        'bidder_id' => $buyer->id,
        'amount' => 1300000.0,
        'status' => 'active',
    ]);

    $this->assertEquals(1300000.0, $auction->fresh()->current_price);
});

test('buyer cannot bid below minimum required amount', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'starting_price' => 1000000,
        'current_price' => 1000000,
        'bid_increment' => 200000,
        'status' => 'active',
    ]);

    Sanctum::actingAs($buyer);

    // Minimum required is current_price + bid_increment = 1,200,000. Let's try 1,100,000.
    $response = $this->postJson("/api/auctions/{$auction->id}/bids", [
        'amount' => 1100000,
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'Tawaran minimal harus senilai 1,200,000.']);
});

test('seller cannot bid on their own auction', function () {
    $seller = User::factory()->create();
    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'status' => 'active',
    ]);

    Sanctum::actingAs($seller);

    $response = $this->postJson("/api/auctions/{$auction->id}/bids", [
        'amount' => $auction->current_price + $auction->bid_increment + 100000,
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'Anda tidak dapat menawar lelang Anda sendiri.']);
});

test('placing a higher bid outbids previous bidder and sends notification', function () {
    $seller = User::factory()->create();
    $buyer1 = User::factory()->create();
    $buyer2 = User::factory()->create();

    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'starting_price' => 1000000,
        'current_price' => 1000000,
        'bid_increment' => 200000,
        'status' => 'active',
    ]);

    // Buyer 1 bids 1,200,000
    Sanctum::actingAs($buyer1);
    $this->postJson("/api/auctions/{$auction->id}/bids", ['amount' => 1200000]);

    $this->assertDatabaseHas('bids', [
        'auction_id' => $auction->id,
        'bidder_id' => $buyer1->id,
        'amount' => 1200000.0,
        'status' => 'active',
    ]);

    // Buyer 2 bids 1,500,000
    Sanctum::actingAs($buyer2);
    $response = $this->postJson("/api/auctions/{$auction->id}/bids", ['amount' => 1500000]);

    $response->assertStatus(201);

    // Verify Buyer 1's bid status is updated to outbid
    $this->assertDatabaseHas('bids', [
        'auction_id' => $auction->id,
        'bidder_id' => $buyer1->id,
        'amount' => 1200000.0,
        'status' => 'outbid',
    ]);

    // Verify Buyer 1 received outbid notification
    $this->assertDatabaseHas('notifications', [
        'user_id' => $buyer1->id,
        'type' => 'outbid',
    ]);
});
