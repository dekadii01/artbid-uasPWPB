<?php

use App\Models\User;
use App\Models\Notification;
use Laravel\Sanctum\Sanctum;

test('authenticated user can view their notifications', function () {
    $user = User::factory()->create();
    Notification::create([
        'user_id' => $user->id,
        'type' => 'outbid',
        'channel' => 'private',
        'title' => 'Test Notification',
        'body' => 'Body of test notification',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/notifications');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => ['id', 'user_id', 'type', 'title', 'body', 'read_at']
            ],
        ]);
});

test('user can mark single notification as read', function () {
    $user = User::factory()->create();
    $notif = Notification::create([
        'user_id' => $user->id,
        'type' => 'outbid',
        'channel' => 'private',
        'title' => 'Test Notification',
        'body' => 'Body of test notification',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson("/api/notifications/{$notif->id}/read");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Notifikasi ditandai sebagai dibaca.']);

    $this->assertNotNull($notif->fresh()->read_at);
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();
    Notification::create([
        'user_id' => $user->id,
        'type' => 'outbid',
        'channel' => 'private',
        'title' => 'Test 1',
        'body' => 'Body 1',
    ]);
    Notification::create([
        'user_id' => $user->id,
        'type' => 'outbid',
        'channel' => 'private',
        'title' => 'Test 2',
        'body' => 'Body 2',
    ]);

    Sanctum::actingAs($user);

    $response = $this->patchJson('/api/notifications/read-all');

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Semua notifikasi berhasil ditandai sebagai dibaca.']);

    $unreadCount = Notification::where('user_id', $user->id)->whereNull('read_at')->count();
    $this->assertEquals(0, $unreadCount);
});
