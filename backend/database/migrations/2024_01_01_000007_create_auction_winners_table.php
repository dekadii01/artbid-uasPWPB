<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_winners', function (Blueprint $table) {
            $table->id();

            // Satu lelang hanya boleh punya satu pemenang
            $table->foreignId('auction_id')->unique()->constrained('auctions')->cascadeOnDelete();
            $table->foreignId('winner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('winning_bid_id')->constrained('bids')->cascadeOnDelete();

            // Snapshot harga final saat lelang berakhir
            // Disimpan terpisah agar tidak berubah jika data bid dimodifikasi
            $table->decimal('final_price', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_winners');
    }
};
