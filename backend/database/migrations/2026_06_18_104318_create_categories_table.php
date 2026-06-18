<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migrasi untuk membuat tabel 'categories'.
     * tabel ini digunakan untuk menyimpan kategori barang lelang.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // Primary Key auto-increment
            $table->string('name')->unique(); // Nama kategori (harus unik)
            $table->string('slug')->unique(); // Slug kategori untuk URL-friendly (harus unik)
            $table->text('description')->nullable(); // Deskripsi kategori (boleh kosong)
            $table->string('icon')->nullable(); // Path/Nama ikon SVG (boleh kosong)
            $table->string('image_path')->nullable(); // Path gambar thumbnail kategori (boleh kosong)
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Membatalkan migrasi dengan menghapus tabel 'categories'.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
