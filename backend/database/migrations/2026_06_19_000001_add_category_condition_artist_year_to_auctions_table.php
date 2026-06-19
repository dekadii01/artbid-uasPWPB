<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('description')->constrained('categories')->nullOnDelete();
            $table->string('condition', 100)->nullable()->after('category_id');
            $table->string('artist', 255)->nullable()->after('condition');
            $table->integer('year')->nullable()->after('artist');
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'condition', 'artist', 'year']);
        });
    }
};
