<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verify_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('minecraft_username');
            $table->string('token');
            $table->boolean('verified')->default(false);
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('minecraft_username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verify_tokens');
    }
};
