<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gradients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('colors'); // array hex stop, mis. ["#FF0000","#FFFF00"]
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('nickname_type')->nullable()->after('requires_nickname'); // 'custom' | 'gradient'
        });

        Schema::create('player_nicknames', function (Blueprint $table) {
            $table->id();
            $table->string('minecraft_username');
            $table->string('minecraft_uuid')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('gradient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // 'custom' | 'gradient'
            $table->string('label'); // label buat ditampilin di inventory
            $table->text('value'); // string nickname final (sudah ada kode warna/hex)
            $table->json('command_template'); // snapshot command RCON (target+placeholder), buat re-apply kapan aja
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('minecraft_username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_nicknames');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('nickname_type');
        });
        Schema::dropIfExists('gradients');
    }
};
