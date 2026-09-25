<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Order lama yang "ditukar" jadi kredit buat order upgrade ini.
            // Null berarti order biasa (bukan hasil upgrade).
            $table->foreignId('upgraded_from_order_id')->nullable()->after('product_duration_id')
                ->constrained('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('upgraded_from_order_id');
        });
    }
};
