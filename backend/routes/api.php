<?php

use App\Http\Controllers\Api\Admin\AuctionController as AdminAuctionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\AuctionImageController;
use App\Http\Controllers\Api\AuctionWinnerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\BuyNowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WatchlistController;
use Illuminate\Support\Facades\Route;

// Route::get('/hello', function () {
//     return response()->json([
//         'message' => 'Hello World'
//     ]);
// });

// ---------------------------------------------------------------------------
// Public routes — tidak perlu login
// ---------------------------------------------------------------------------

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

// Listing & detail lelang bisa dilihat tanpa login
Route::get('auctions',              [AuctionController::class, 'index']);
Route::get('auctions/{auction}',    [AuctionController::class, 'show']);
Route::get('auctions/{auction}/bids',   [BidController::class, 'index']);
Route::get('auctions/{auction}/winner', [AuctionWinnerController::class, 'show']);

// ---------------------------------------------------------------------------
// Protected routes — wajib login (Sanctum token)
// ---------------------------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Profil user yang sedang login
    Route::get('profile',   [ProfileController::class, 'show']);
    Route::put('profile',   [ProfileController::class, 'update']);

    // ------------------------------------------------------------------
    // Lelang
    // ------------------------------------------------------------------

    // Seller: buat lelang baru
    Route::post('auctions', [AuctionController::class, 'store']);

    // Seller: ubah & hapus — hanya saat status = scheduled (dicek di controller/policy)
    Route::put('auctions/{auction}',    [AuctionController::class, 'update']);
    Route::delete('auctions/{auction}', [AuctionController::class, 'destroy']);

    // Seller: daftar lelang milik sendiri beserta hasilnya
    Route::get('my-auctions', [AuctionController::class, 'myAuctions']);

    // ------------------------------------------------------------------
    // Foto lelang
    // ------------------------------------------------------------------

    Route::post('auctions/{auction}/images',         [AuctionImageController::class, 'store']);
    Route::delete('auctions/{auction}/images/{image}', [AuctionImageController::class, 'destroy']);

    // ------------------------------------------------------------------
    // Watchlist
    // ------------------------------------------------------------------
    Route::get('watchlist', [WatchlistController::class, 'index']);
    Route::post('auctions/{auction}/watchlist', [WatchlistController::class, 'store']);

    // ------------------------------------------------------------------
    // Penawaran (bid)
    // ------------------------------------------------------------------

    // Buyer: tempatkan tawaran
    Route::post('auctions/{auction}/bids', [BidController::class, 'store']);

    // Riwayat bid milik user yang sedang login
    Route::get('my-bids', [BidController::class, 'myBids']);

    // ------------------------------------------------------------------
    // Buy Now (fitur bonus)
    // ------------------------------------------------------------------

    Route::post('auctions/{auction}/buy-now', [BuyNowController::class, 'store']);

    // ------------------------------------------------------------------
    // Notifikasi
    // ------------------------------------------------------------------

    Route::get('notifications',                     [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/read-all',          [NotificationController::class, 'markAllAsRead']);

    // ------------------------------------------------------------------
    // Admin routes — tambahan middleware role:admin
    // ------------------------------------------------------------------

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('auctions',                      [AdminAuctionController::class, 'index']);
        Route::patch('auctions/{auction}/end',      [AdminAuctionController::class, 'forceEnd']);
        Route::patch('auctions/{auction}/activate', [AdminAuctionController::class, 'activate']);
        Route::put('users/{user}',                  [AdminUserController::class, 'update']);
    });
});
