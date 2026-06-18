<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);

/*
 * Channel: auction.{auctionId}
 *
 * This callback handles BOTH private AND presence subscriptions.
 * When echo.join('auction.5') is called, Echo subscribes to
 * "presence-auction.5". Laravel strips the prefix and looks up
 * "auction.{auctionId}" here.
 *
 * For presence channels the callback MUST return an array with
 * user metadata — returning a boolean silently breaks here/joining/leaving.
 */
Broadcast::channel('auction.{auctionId}', function ($user, $auctionId) {
    if ($user) {
        return [
            'id'   => $user->id,
            'name' => $user->full_name,
        ];
    }

    return false;
}, ['guards' => ['sanctum']]);
