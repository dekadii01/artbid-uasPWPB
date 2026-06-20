<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

test('user can register successfully', function () {
    $response = $this->postJson('/api/auth/register', [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '081234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'first_name', 'last_name', 'email', 'phone'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john.doe@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);
});

test('registration requires correct inputs', function () {
    $response = $this->postJson('/api/auth/register', [
        'firstName' => '',
        'lastName' => '',
        'email' => 'not-an-email',
        'phone' => '',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['firstName', 'lastName', 'email', 'phone', 'password']);
});

test('user can login successfully', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user',
            'token',
        ]);
});

test('login rejects invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'login@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonFragment(['message' => 'Email atau password salah.']);
});

test('blocked user cannot login', function () {
    $user = User::factory()->create([
        'email' => 'blocked@example.com',
        'password' => Hash::make('password123'),
        'status' => 'blocked',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'blocked@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(403)
        ->assertJsonFragment(['message' => 'Akun Anda telah diblokir oleh administrator.']);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Logout berhasil.']);
});
