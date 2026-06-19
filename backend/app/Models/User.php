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
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'avatar',
        'role',
        'status',
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

    /**
     * Gabungan first_name + last_name untuk ditampilkan di frontend.
     * Otomatis muncul di response JSON sebagai "full_name".
     */
    protected function fullName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    protected $appends = ['full_name'];

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

    /**
     * Watchlist items created by this user.
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Auctions watchlisted by this user.
     */
    public function watchedAuctions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Auction::class, 'watchlists', 'user_id', 'auction_id')->withTimestamps();
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
