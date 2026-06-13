<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->cascadeOnDelete();

            // Path file relatif di storage (misal: auctions/uuid/photo.jpg)
            $table->string('image_path');

            // Disk aktif: 'public' (local dev), 's3' atau 'r2' (production)
            // Fitur bonus: unggah ke object storage S3/R2
            $table->string('storage_disk')->default('public');

            // 0 = foto utama yang tampil di listing
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['auction_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_images');
    }
};
