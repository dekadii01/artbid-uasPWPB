<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

Broadcast::channel('auction.{auctionId}', function ($user, $auctionId) {
    return $user !== null;
}, ['guards' => ['sanctum']]);

Broadcast::channel('presence-auction.{auctionId}', function ($user, $auctionId) {
    if ($user !== null) {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
        ];
    }
    return null;
}, ['guards' => ['sanctum']]);

