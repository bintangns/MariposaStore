<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_durations', function (Blueprint $table) {
            $table->json('commands')->nullable()->after('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('duration_commands')->nullable()->after('duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('product_durations', function (Blueprint $table) {
            $table->dropColumn('commands');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('duration_commands');
        });
    }
};
