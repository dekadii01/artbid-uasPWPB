<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model Category
 * 
 * Digunakan untuk mengelola data kategori barang seni lelang.
 */
class Category extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image_path',
    ];

    /**
     * Boot function untuk mendengarkan event model Eloquent.
     * Digunakan untuk mengotomatisasi pengisian slug berdasarkan nama.
     */
    protected static function boot()
    {
        parent::boot();

        // Sebelum menyimpan model ke database, buat slug otomatis dari name jika slug kosong
        static::saving(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Relasi HasMany ke model Auction.
     * Menunjukkan bahwa satu kategori memiliki banyak lelang.
     * 
     * @return HasMany
     */
    public function auctions(): HasMany
    {
        // Hubungkan ke model Auction melalui foreign key category_id
        return $this->hasMany(Auction::class, 'category_id');
    }
}
