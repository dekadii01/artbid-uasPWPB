<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Tipe notifikasi:
            // 'outbid'          → penawar tergeser oleh tawaran lebih tinggi
            // 'auction_won'     → pemenang lelang
            // 'auction_ended'   → lelang selesai (untuk seller)
            // 'auction_started' → lelang mulai (untuk yang watchlist)
            $table->string('type');

            // broadcast = dikirim ke semua viewer kanal lelang (harga baru, pemenang)
            // private   = dikirim ke kanal privat user (outbid, menang)
            $table->enum('channel', ['broadcast', 'private'])->default('private');

            $table->string('title');
            $table->text('body')->nullable();

            // Data kontekstual untuk aksi di frontend
            // Contoh: { "auction_id": 1, "bid_id": 42, "auction_title": "..." }
            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index untuk notifikasi belum dibaca per user
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
