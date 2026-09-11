<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('minecraft_username');
            $table->string('minecraft_uuid')->nullable();
            $table->foreignId('product_id')->constrained();
            $table->integer('amount');
            $table->string('status')->default('pending');
            $table->string('payment_type')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_status')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
