<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('requires_nickname')->default(false)->after('rank_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('custom_nickname')->nullable()->after('minecraft_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('requires_nickname');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('custom_nickname');
        });
    }
};
