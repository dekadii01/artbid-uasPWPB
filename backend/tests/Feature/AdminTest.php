<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Setting;
use Laravel\Sanctum\Sanctum;

test('normal user cannot access admin routes', function () {
    $user = User::factory()->create(['role' => 'user']);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/admin/dashboard');

    $response->assertStatus(403);
});

test('admin can access admin dashboard statistics', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'mainStats',
            'auctionStatus',
            'topCategories',
            'financial',
        ]);
});

test('admin can block user and delete their active tokens', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['status' => 'active']);
    
    // Create some tokens for user
    $user->createToken('test_token');
    $this->assertCount(1, $user->tokens);

    Sanctum::actingAs($admin);

    $response = $this->putJson("/api/admin/users/{$user->id}", [
        'status' => 'blocked',
    ]);

    $response->assertStatus(200);
    $user->refresh();
    $this->assertEquals('blocked', $user->status);
    $this->assertCount(0, $user->tokens); // tokens are cleared on block
});

test('admin can delete user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Akun pengguna berhasil dihapus secara permanen.']);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin can manage categories', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    // Create Category
    $response = $this->postJson('/api/admin/categories', [
        'name' => 'Seni Ukir',
        'description' => 'Ukiran tradisional',
    ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Kategori berhasil ditambahkan.']);

    $category = Category::where('name', 'Seni Ukir')->first();

    // Update Category
    $response = $this->putJson("/api/admin/categories/{$category->id}", [
        'name' => 'Seni Ukir Bali',
        'description' => 'Ukiran tradisional Bali',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Kategori berhasil diperbarui.']);

    // Delete Category
    $response = $this->deleteJson("/api/admin/categories/{$category->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Kategori berhasil dihapus.']);
});

test('admin can activate scheduled auction', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $auction = Auction::factory()->scheduled()->create();

    Sanctum::actingAs($admin);

    $response = $this->patchJson("/api/admin/auctions/{$auction->id}/activate");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Lelang berhasil diaktifkan.']);

    $this->assertEquals('active', $auction->fresh()->status);
});

test('admin can force end active auction and determine winner', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $seller = User::factory()->create();
    $buyer = User::factory()->create();

    $auction = Auction::factory()->create([
        'seller_id' => $seller->id,
        'status' => 'active',
        'starting_price' => 1000000,
        'current_price' => 1200000,
    ]);

    $bid = Bid::factory()->create([
        'auction_id' => $auction->id,
        'bidder_id' => $buyer->id,
        'amount' => 1200000,
        'status' => 'active',
    ]);

    Sanctum::actingAs($admin);

    $response = $this->patchJson("/api/admin/auctions/{$auction->id}/end");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Lelang berhasil dihentikan.']);

    $auction->refresh();
    $this->assertEquals('ended', $auction->status);

    $this->assertDatabaseHas('auction_winners', [
        'auction_id' => $auction->id,
        'winner_id' => $buyer->id,
        'final_price' => 1200000.0,
    ]);
});

test('admin can retrieve and update settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    // Initial load
    $response = $this->getJson('/api/admin/settings');
    $response->assertStatus(200);

    // Update settings
    $response = $this->putJson('/api/admin/settings', [
        'key' => 'komisi_lelang',
        'value' => '10',
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Pengaturan sistem berhasil disimpan.']);
});

test('admin can view reports', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/admin/reports?period=month');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'stats',
            'charts',
            'sales',
        ]);
});
