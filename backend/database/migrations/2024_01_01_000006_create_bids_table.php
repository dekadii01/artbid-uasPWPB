<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->cascadeOnDelete();
            $table->foreignId('bidder_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            // active   = tawaran tertinggi saat ini
            // outbid   = tergeser tawaran lebih tinggi → trigger notifikasi realtime
            // won      = pemenang setelah lelang berakhir
            // rejected = ditolak validasi server (lelang sudah ended, amount tidak valid, dll)
            $table->enum('status', ['active', 'outbid', 'won', 'rejected'])->default('active');

            // Waktu tawaran ditempatkan — terpisah dari created_at untuk presisi realtime
            $table->timestamp('placed_at')->useCurrent();

            $table->timestamps();

            // Index untuk query realtime: ambil harga tertinggi & daftar penawaran
            $table->index(['auction_id', 'status']);
            $table->index(['auction_id', 'amount']);
            $table->index(['bidder_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
