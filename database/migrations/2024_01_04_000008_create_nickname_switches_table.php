<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nickname_switches', function (Blueprint $table) {
            $table->id();
            $table->string('minecraft_username');
            $table->foreignId('player_nickname_id')->nullable()->constrained('player_nicknames')->nullOnDelete();
            $table->timestamps();

            $table->index(['minecraft_username', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nickname_switches');
    }
};
