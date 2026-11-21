<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus order yang ditinggal tanpa dibayar (lihat Order::purgeAbandoned).
// Jalan otomatis tiap menit kalau cron `php artisan schedule:run` aktif di
// hosting; selain itu tetap dibersihkan tiap admin buka dashboard / daftar order.
Artisan::command('orders:purge-abandoned', function () {
    $this->info('Order dihapus: ' . Order::purgeAbandoned());
})->purpose('Hapus order pending/batal yang ditinggal tanpa dibayar');

Schedule::command('orders:purge-abandoned')->everyMinute()->withoutOverlapping();
