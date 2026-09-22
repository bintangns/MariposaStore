<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'minecraft_username',
        'minecraft_uuid',
        'terms_accepted_at',
        'product_id',
        'product_duration_id',
        'duration_label',
        'duration_days',
        'duration_commands',
        'amount',
        'status',           // pending, paid, delivered, failed
        'payment_type',
        'payment_proof',
        'midtrans_transaction_id',
        'midtrans_status',
        'delivered_at',
        'delivery_log',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'amount' => 'integer',
        'duration_days' => 'integer',
        'duration_commands' => 'array',
        'delivery_log' => 'array',
    ];

    /**
     * Command yang gagal terkirim pada percobaan delivery terakhir (dari
     * delivery_log), masing-masing entry {target, command, success: false}.
     */
    public function getFailedCommandsAttribute(): array
    {
        return collect($this->delivery_log ?? [])
            ->filter(fn ($entry) => !($entry['success'] ?? false))
            ->values()
            ->all();
    }

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
     * Dua placeholder tersedia buat command custom (durations/product):
     * - {player} -> username. Dipakai kebanyakan plugin (PlayerPoints,
     *   CrazyCrates, give, dll) yang resolve player lewat nama.
     * - {uuid}   -> UUID. Wajib dipakai buat command LuckPerms ("lp user
     *   {uuid} ..."), karena LP memvalidasi argumen <user> sebagai format
     *   username Mojang standar dan menolak username Bedrock yang diawali
     *   "." (mis. .BintangNS). UUID gak kena validasi itu, jadi aman buat
     *   Java maupun Bedrock.
     *
     * Command auto-generate dari rank_name (kalau duration_commands kosong)
     * selalu pakai UUID juga, dengan alasan yang sama.
     */
    public function resolveCommands(): array
    {
        $username = $this->minecraft_username;
        $uuid     = $this->minecraft_uuid ?: $this->minecraft_username;
        $replace  = fn (string $cmd) => str_replace(['{player}', '{uuid}'], [$username, $uuid], $cmd);

        if ($this->product_duration_id) {
            if (!empty($this->duration_commands)) {
                return array_map($replace, $this->duration_commands);
            }

            if ($this->product->rank_name) {
                $rankName = $this->product->rank_name;
                $command  = $this->duration_days
                    ? "lp user {$uuid} parent addtemp {$rankName} {$this->duration_days}d"
                    : "lp user {$uuid} parent set {$rankName}";

                return [$command];
            }
        }

        return array_map($replace, $this->product->commands ?? []);
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusLabelAttribute()
    {
        // Sudah upload bukti transfer manual, tinggal nunggu admin verifikasi —
        // beda kondisi sama "pending" murni yang belum bayar sama sekali.
        if ($this->status === 'pending' && $this->payment_proof) {
            return 'Menunggu Konfirmasi';
        }

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
        if ($this->status === 'pending' && $this->payment_proof) {
            return 'purple';
        }

        return match($this->status) {
            'pending'   => 'yellow',
            'paid'      => 'blue',
            'delivered' => 'green',
            'failed'    => 'red',
            default     => 'gray',
        };
    }
}
