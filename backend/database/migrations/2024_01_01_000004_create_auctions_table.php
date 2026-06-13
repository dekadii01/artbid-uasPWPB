<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('description');

            // Harga awal tidak boleh diubah setelah lelang mulai
            $table->decimal('starting_price', 15, 2);

            // Di-update setiap ada bid baru — tidak dihitung dari tabel bids
            // agar query realtime lebih ringan
            $table->decimal('current_price', 15, 2);

            // Kelipatan minimum penawaran (validasi wajib di server)
            $table->decimal('bid_increment', 15, 2);

            // Fitur bonus: Buy Now — nullable jika tidak diaktifkan seller
            $table->decimal('buy_now_price', 15, 2)->nullable();

            // Dikelola otomatis oleh Scheduler:
            // scheduled -> active (saat starts_at tercapai)
            // active    -> ended  (saat ends_at tercapai)
            $table->enum('status', ['scheduled', 'active', 'ended'])->default('scheduled');

            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamps();

            // Soft delete: seller hanya bisa hapus saat status = scheduled
            // Data tetap tersimpan untuk keperluan audit
            $table->softDeletes();

            // Index untuk query yang sering dijalankan Scheduler & listing
            $table->index('status');
            $table->index('ends_at');
            $table->index(['status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
