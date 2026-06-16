<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AuctionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'image_path',
        'storage_disk',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // URL helper
    // -----------------------------------------------------------------------

    /**
     * Kembalikan URL publik foto, sesuai disk yang digunakan
     * (public / s3 / r2 — transparan untuk frontend).
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->storage_disk)->url($this->image_path);
    }

    protected $appends = ['url'];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }
}
