<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('days')->nullable(); // null = permanent
            $table->integer('price');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('rank_name')->nullable()->after('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('product_duration_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('duration_label')->nullable()->after('product_duration_id');
            $table->unsignedInteger('duration_days')->nullable()->after('duration_label');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_duration_id');
            $table->dropColumn(['duration_label', 'duration_days']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('rank_name');
        });

        Schema::dropIfExists('product_durations');
    }
};
