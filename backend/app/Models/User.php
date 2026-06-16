<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -----------------------------------------------------------------------
    // Role helpers
    // -----------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Seller bukan dari kolom role, tapi dari aktivitas — punya lelang
    public function isSeller(): bool
    {
        return $this->auctions()->exists();
    }

    // Buyer bukan dari kolom role, tapi dari aktivitas — punya bid
    public function isBuyer(): bool
    {
        return $this->bids()->exists();
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Lelang yang dibuat oleh user ini (sebagai seller).
     */
    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class, 'seller_id');
    }

    /**
     * Semua penawaran yang ditempatkan oleh user ini (sebagai buyer).
     */
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'bidder_id');
    }

    /**
     * Lelang yang dimenangkan oleh user ini.
     */
    public function wins(): HasMany
    {
        return $this->hasMany(AuctionWinner::class, 'winner_id');
    }

    /**
     * Notifikasi milik user ini.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
