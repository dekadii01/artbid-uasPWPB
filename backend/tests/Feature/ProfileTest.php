<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('authenticated user can view their profile', function () {
    $user = User::factory()->create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/profile');

    $response->assertStatus(200)
        ->assertJsonPath('user.first_name', 'Jane')
        ->assertJsonPath('user.last_name', 'Doe')
        ->assertJsonPath('user.full_name', 'Jane Doe');
});

test('unauthenticated user cannot view profile', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(401);
});

test('user can update their profile details', function () {
    $user = User::factory()->create([
        'first_name' => 'Old',
        'last_name' => 'Name',
        'email' => 'old@example.com',
        'phone' => '11111',
    ]);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/profile', [
        'firstName' => 'New',
        'lastName' => 'Name',
        'email' => 'new@example.com',
        'phone' => '22222',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('user.first_name', 'New')
        ->assertJsonPath('user.last_name', 'Name')
        ->assertJsonPath('user.full_name', 'New Name');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'New',
        'last_name' => 'Name',
        'email' => 'new@example.com',
        'phone' => '22222',
    ]);
});

test('normal user cannot upgrade their role to admin', function () {
    $user = User::factory()->create([
        'role' => 'user',
    ]);
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/profile', [
        'role' => 'admin',
    ]);

    $response->assertStatus(200);
    $this->assertEquals('user', $user->fresh()->role);
});

test('user can upload an avatar picture', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->putJson('/api/profile', [
        'avatar' => $file,
    ]);

    $response->assertStatus(200);
    $user->refresh();
    $this->assertNotNull($user->avatar);
    Storage::disk('public')->assertExists($user->avatar);
});
