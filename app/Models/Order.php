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
        'duration_commands',
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
        'duration_commands' => 'array',
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
     * Command yang dijalankan saat delivery. Kalau durasi yang dibeli punya
     * command khusus (diisi admin per-durasi, misal 30 Hari dapet kit tambahan
     * yang 7 Hari nggak dapet), itu yang dipakai. Kalau nggak diisi, fallback ke
     * auto-generate command LuckPerms dari rank_name + durasi.
     *
     * duration_commands & duration_days/label disnapshot ke order saat checkout
     * (bukan dibaca live dari row product_durations), supaya command yang
     * dieksekusi tetap konsisten dengan yang dibeli walau admin edit/hapus
     * durasi tsb setelah order dibuat.
     *
     * Pakai UUID (bukan username) sebagai target LuckPerms: LP memvalidasi
     * argumen <user> sebagai format username Mojang standar, jadi username
     * Bedrock yang diawali "." (mis. .BintangNS) ditolak/gagal di-resolve.
     * UUID sudah tersimpan di order sejak verifikasi dan diterima LP tanpa
     * perlu validasi format tsb, jadi berlaku sama untuk Java maupun Bedrock.
     */
    public function resolveCommands(): array
    {
        $target = $this->minecraft_uuid ?: $this->minecraft_username;

        if ($this->product_duration_id) {
            if (!empty($this->duration_commands)) {
                return array_map(
                    fn ($cmd) => str_replace('{player}', $target, $cmd),
                    $this->duration_commands
                );
            }

            if ($this->product->rank_name) {
                $rankName = $this->product->rank_name;
                $command  = $this->duration_days
                    ? "lp user {$target} parent addtemp {$rankName} {$this->duration_days}d"
                    : "lp user {$target} parent set {$rankName}";

                return [$command];
            }
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
