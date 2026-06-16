<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'bidder_id',
        'amount',
        'status',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'    => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Status helpers
    // -----------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOutbid(): bool
    {
        return $this->status === 'outbid';
    }

    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Tandai bid ini sebagai tergeser (outbid).
     * Dipanggil saat ada bid baru yang lebih tinggi masuk.
     */
    public function markAsOutbid(): void
    {
        $this->update(['status' => 'outbid']);
    }

    /**
     * Tandai bid ini sebagai pemenang.
     * Dipanggil oleh EndAuctionJob saat lelang ditutup.
     */
    public function markAsWon(): void
    {
        $this->update(['status' => 'won']);
    }

    /**
     * Tandai bid ini sebagai ditolak.
     * Dipanggil jika validasi server gagal setelah bid tersimpan.
     */
    public function markAsRejected(): void
    {
        $this->update(['status' => 'rejected']);
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * Lelang tempat tawaran ini ditempatkan.
     */
    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * User yang menempatkan tawaran ini.
     */
    public function bidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_id');
    }

    /**
     * Data pemenang, jika bid ini menjadi pemenang (nullable).
     */
    public function auctionWinner(): HasOne
    {
        return $this->hasOne(AuctionWinner::class, 'winning_bid_id');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOutbid($query)
    {
        return $query->where('status', 'outbid');
    }

    public function scopeHighest($query)
    {
        return $query->orderByDesc('amount');
    }
}
