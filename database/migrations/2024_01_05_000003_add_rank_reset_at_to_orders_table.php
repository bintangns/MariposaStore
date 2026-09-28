<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Diisi admin kalau perlu "reset" kepemilikan rank order ini
            // (misal ada masalah/kesalahan) — order tetap tercatat delivered
            // apa adanya, cuma gak lagi dihitung sebagai rank aktif, jadi
            // pemain bisa beli produk itu lagi dari Store. Murni ubah data di
            // website, TIDAK ngirim command RCON apapun ke server.
            $table->timestamp('rank_reset_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('rank_reset_at');
        });
    }
};
