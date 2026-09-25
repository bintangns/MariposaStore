<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rank_rewards', function (Blueprint $table) {
            $table->id();
            // Produk rank (mis. VIP/Master/Legend) yang pas dibeli & sukses
            // delivered, ngasih jatah nickname gratis ke pembelinya.
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('gradient_count')->default(0);
            $table->unsignedInteger('custom_count')->default(0);
            $table->timestamps();
        });

        // Catatan "player X udah pernah kena kredit reward dari beli produk Y"
        // — sekali per (username, product) selamanya, jadi perpanjang/beli
        // ulang produk rank yang sama gak nambah jatah lagi. Murni transaksi
        // database, gak ada RCON/query ke server Minecraft sama sekali.
        Schema::create('player_rank_credits', function (Blueprint $table) {
            $table->id();
            $table->string('minecraft_username');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['minecraft_username', 'product_id']);
        });

        Schema::table('player_nicknames', function (Blueprint $table) {
            $table->boolean('is_free_claim')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('player_nicknames', function (Blueprint $table) {
            $table->dropColumn('is_free_claim');
        });
        Schema::dropIfExists('player_rank_credits');
        Schema::dropIfExists('rank_rewards');
    }
};
