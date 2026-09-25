<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('midtrans_transaction_id', 'payment_reference');
            $table->renameColumn('midtrans_status', 'payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('payment_reference', 'midtrans_transaction_id');
            $table->renameColumn('payment_status', 'midtrans_status');
        });
    }
};
