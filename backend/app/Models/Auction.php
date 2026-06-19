<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'category_id',
        'condition',
        'artist',
        'year',
        'starting_price',
        'current_price',
        'bid_increment',
        'buy_now_price',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starting_price' => 'decimal:2',
            'current_price'  => 'decimal:2',
            'bid_increment'  => 'decimal:2',
            'buy_now_price'  => 'decimal:2',
            'starts_at'      => 'datetime',
            'ends_at'        => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Status helpers
    // -----------------------------------------------------------------------

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    /**
     * Apakah lelang masih bisa diubah/dihapus oleh seller?
     * Hanya boleh selama status masih scheduled.
     */
    public function isEditable(): bool
    {
        return $this->isScheduled();
    }

    /**
     * Apakah buy_now tersedia untuk lelang ini?
     */
    public function hasBuyNow(): bool
    {
        return ! is_null($this->buy_now_price);
    }

    // -----------------------------------------------------------------------
    // Anti-sniping helper
    // -----------------------------------------------------------------------

    /**
     * Cek apakah bid masuk di N detik terakhir sebelum lelang berakhir.
     * Nilai N diambil dari config/auction.php.
     * Jika ya, perpanjang ends_at sebesar N detik dari sekarang.
     */
    public function extendIfSniping(): bool
    {
        $snipeSeconds = config('auction.snipe_protection_seconds', 0);

        if ($snipeSeconds === 0) {
            return false;
        }

        $threshold = now()->addSeconds($snipeSeconds);

        if ($this->ends_at->lessThanOrEqualTo($threshold)) {
            $this->ends_at = now()->addSeconds($snipeSeconds);
            return true;
        }

        return false;
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Seller yang membuat lelang ini.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Kategori barang lelang.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Semua foto barang lelang, diurutkan berdasarkan sort_order.
     */
    public function images(): HasMany
    {
        return $this->hasMany(AuctionImage::class)->orderBy('sort_order');
    }

    /**
     * Foto utama (sort_order = 0).
     */
    public function mainImage(): HasOne
    {
        return $this->hasOne(AuctionImage::class)->oldestOfMany('sort_order');
    }

    /**
     * Semua penawaran pada lelang ini.
     */
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Tawaran tertinggi yang sedang aktif.
     */
    public function activeBid(): HasOne
    {
        return $this->hasOne(Bid::class)->where('status', 'active')->latestOfMany('amount');
    }

    /**
     * Data pemenang lelang ini (nullable — hanya ada setelah lelang ended).
     */
    public function winner(): HasOne
    {
        return $this->hasOne(AuctionWinner::class);
    }

    /**
     * Watchlist items for this auction.
     */
    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    /**
     * Users who have watchlisted this auction.
     */
    public function watchers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'watchlists', 'auction_id', 'user_id')->withTimestamps();
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    /**
     * Lelang yang sudah waktunya diaktifkan (scheduled & starts_at sudah lewat).
     */
    public function scopeDueToStart($query)
    {
        return $query->scheduled()->where('starts_at', '<=', now());
    }

    /**
     * Lelang yang sudah waktunya ditutup (active & ends_at sudah lewat).
     */
    public function scopeDueToEnd($query)
    {
        return $query->active()->where('ends_at', '<=', now());
    }
}
