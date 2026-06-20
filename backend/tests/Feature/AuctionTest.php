<?php

use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use App\Models\AuctionImage;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('anyone can view paginated auctions list', function () {
    Auction::factory()->count(15)->create(['status' => 'active']);

    $response = $this->getJson('/api/auctions');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => ['id', 'name', 'currentPrice', 'status', 'startsAt', 'endsAt']
            ],
            'total',
        ])
        ->assertJsonPath('total', 15);
});

test('anyone can view single auction details', function () {
    $auction = Auction::factory()->create();

    $response = $this->getJson("/api/auctions/{$auction->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'auction' => ['id', 'name', 'category', 'currentPrice', 'status', 'bids', 'seller']
        ])
        ->assertJsonPath('auction.id', $auction->id);
});

test('authenticated user can create scheduled auction', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $mainPhoto = UploadedFile::fake()->image('art1.jpg');
    $extraPhoto = UploadedFile::fake()->image('art2.jpg');

    $response = $this->postJson('/api/auctions', [
        'title' => 'Amazing Painting',
        'category' => 'Lukisan',
        'description' => 'This is a premium Bali traditional painting of Barong.',
        'condition' => 'Sangat Baik',
        'artist' => 'Made Arya',
        'year' => 2022,
        'starting_price' => 5000000,
        'bid_increment' => 250000,
        'buy_now_price' => 10000000,
        'start_date' => now()->addDays(1)->format('Y-m-d'),
        'start_time' => '10:00',
        'end_date' => now()->addDays(8)->format('Y-m-d'),
        'end_time' => '10:00',
        'main_photo' => $mainPhoto,
        'extra_photos' => [$extraPhoto],
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Lelang berhasil dibuat.']);

    $this->assertDatabaseHas('auctions', [
        'title' => 'Amazing Painting',
        'status' => 'scheduled',
    ]);
});

test('seller can update their scheduled auction', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->scheduled()->create(['seller_id' => $user->id]);
    
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/auctions/{$auction->id}", [
        'title' => 'Updated Title',
        'category' => 'Lukisan',
        'description' => 'This is a updated Bali traditional painting of Barong.',
        'condition' => 'Sangat Baik',
        'artist' => 'Made Arya',
        'year' => 2022,
        'starting_price' => 6000000,
        'bid_increment' => 500000,
        'buy_now_price' => 12000000,
        'start_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '11:00',
        'end_date' => now()->addDays(9)->format('Y-m-d'),
        'end_time' => '11:00',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Lelang berhasil diperbarui.']);

    $this->assertEquals('Updated Title', $auction->fresh()->title);
});

test('seller cannot update active or ended auction', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->create(['seller_id' => $user->id, 'status' => 'active']);
    
    Sanctum::actingAs($user);

    $response = $this->putJson("/api/auctions/{$auction->id}", [
        'title' => 'Updated Title',
        'category' => 'Lukisan',
        'description' => 'This is a updated Bali traditional painting of Barong.',
        'condition' => 'Sangat Baik',
        'starting_price' => 6000000,
        'bid_increment' => 500000,
        'start_date' => now()->addDays(2)->format('Y-m-d'),
        'start_time' => '11:00',
        'end_date' => now()->addDays(9)->format('Y-m-d'),
        'end_time' => '11:00',
    ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'Lelang hanya dapat diubah sebelum dimulai.']);
});

test('seller can delete scheduled auction', function () {
    $user = User::factory()->create();
    $auction = Auction::factory()->scheduled()->create(['seller_id' => $user->id]);
    
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/auctions/{$auction->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Lelang berhasil dihapus.']);

    $this->assertSoftDeleted('auctions', ['id' => $auction->id]);
});

test('seller can upload more photos to scheduled auction', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $auction = Auction::factory()->scheduled()->create(['seller_id' => $user->id]);
    
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->image('extra.jpg');

    $response = $this->postJson("/api/auctions/{$auction->id}/images", [
        'photos' => [$file],
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Foto berhasil ditambahkan.']);
});
