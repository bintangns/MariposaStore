<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'minecraft_username',
        'minecraft_uuid',
        'product_id',
        'product_duration_id',
        'duration_label',
        'duration_days',
        'amount',
        'status',           // pending, paid, delivered, failed
        'payment_type',
        'midtrans_transaction_id',
        'midtrans_status',
        'delivered_at',
        'delivery_log',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'amount' => 'integer',
        'duration_days' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function duration()
    {
        return $this->belongsTo(ProductDuration::class, 'product_duration_id');
    }

    /**
     * Command yang dijalankan saat delivery. Kalau order ini beli rank dengan
     * durasi, command LuckPerms di-generate otomatis dari rank_name + durasi
     * yang tersimpan di order (bukan bergantung pada row product_durations,
     * yang bisa saja sudah dihapus/diubah admin setelah order dibuat).
     *
     * Pakai UUID (bukan username) sebagai target LuckPerms: LP memvalidasi
     * argumen <user> sebagai format username Mojang standar, jadi username
     * Bedrock yang diawali "." (mis. .BintangNS) ditolak/gagal di-resolve.
     * UUID sudah tersimpan di order sejak verifikasi dan diterima LP tanpa
     * perlu validasi format tsb, jadi berlaku sama untuk Java maupun Bedrock.
     */
    public function resolveCommands(): array
    {
        if ($this->product_duration_id && $this->product->rank_name) {
            $target   = $this->minecraft_uuid ?: $this->minecraft_username;
            $rankName = $this->product->rank_name;
            $command  = $this->duration_days
                ? "lp user {$target} parent addtemp {$rankName} {$this->duration_days}d"
                : "lp user {$target} parent set {$rankName}";

            return [$command];
        }

        return $this->product->commands ?? [];
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending'   => 'Menunggu Pembayaran',
            'paid'      => 'Dibayar',
            'delivered' => 'Selesai',
            'failed'    => 'Gagal',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending'   => 'yellow',
            'paid'      => 'blue',
            'delivered' => 'green',
            'failed'    => 'red',
            default     => 'gray',
        };
    }
}
