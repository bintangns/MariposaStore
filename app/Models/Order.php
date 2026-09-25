<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id',
        'minecraft_username',
        'minecraft_uuid',
        'custom_nickname',
        'terms_accepted_at',
        'product_id',
        'product_duration_id',
        'upgraded_from_order_id',
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

    /**
     * Order rank aktif (delivered & belum expired) milik username ini, satu
     * per produk (yang paling baru kalau ada beberapa) — dasar buat nampilin
     * opsi upgrade di halaman Riwayat.
     */
    public static function activeRankOrdersFor(string $username)
    {
        return static::whereRaw('LOWER(minecraft_username) = ?', [strtolower($username)])
            ->where('status', 'delivered')
            ->whereNotNull('product_duration_id')
            ->with(['product.durations', 'product.category'])
            ->latest()
            ->get()
            ->filter(fn (Order $order) => $order->isActiveRankOrder())
            ->unique('product_id')
            ->values();
    }

    public function duration()
    {
        return $this->belongsTo(ProductDuration::class, 'product_duration_id');
    }

    public function upgradedFromOrder()
    {
        return $this->belongsTo(Order::class, 'upgraded_from_order_id');
    }

    /**
     * True kalau order ini rank berdurasi yang masih "aktif" (permanent, atau
     * belum lewat masa berlakunya) — dasar buat nentuin apakah order ini
     * masih bisa di-upgrade. Murni dihitung dari riwayat order kita sendiri
     * (delivered_at + duration_days), bukan cek live ke LuckPerms.
     */
    public function isActiveRankOrder(): bool
    {
        if ($this->status !== 'delivered' || !$this->product_duration_id) {
            return false;
        }

        if ($this->duration_days === null) {
            return true; // permanent, gak pernah expired
        }

        return $this->delivered_at && $this->delivered_at->copy()->addDays($this->duration_days)->isFuture();
    }

    /**
     * Opsi upgrade yang tersedia dari order rank aktif ini: durasi lain yang
     * lebih mahal di produk yang sama, atau produk lain di kategori yang sama
     * dengan sort_order lebih tinggi ("rank berikutnya"). Harga upgrade =
     * harga baru dikurangi jumlah yang udah pernah dibayar di order ini
     * (kredit penuh, gak diprorata sisa hari), minimal Rp 0.
     *
     * @return array<int, array{product: Product, duration: ProductDuration, price: int, upgrade_price: int}>
     */
    public function eligibleUpgradeOptions(): array
    {
        if (!$this->isActiveRankOrder()) {
            return [];
        }

        $product = $this->product;
        $options = [];

        foreach ($product->durations as $duration) {
            if ($duration->id === $this->product_duration_id || $duration->price <= $this->amount) {
                continue;
            }
            $options[] = ['product' => $product, 'duration' => $duration, 'price' => $duration->price];
        }

        $higherProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('sort_order', '>', $product->sort_order)
            ->where('is_active', true)
            ->with('durations')
            ->orderBy('sort_order')
            ->get();

        foreach ($higherProducts as $p) {
            foreach ($p->durations as $duration) {
                $options[] = ['product' => $p, 'duration' => $duration, 'price' => $duration->price];
            }
        }

        foreach ($options as &$opt) {
            $opt['upgrade_price'] = max(0, $opt['price'] - $this->amount);
        }

        return $options;
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
     * Placeholder tersedia buat command custom (durations/product):
     * - {player}   -> username. Dipakai kebanyakan plugin (PlayerPoints,
     *   CrazyCrates, give, dll) yang resolve player lewat nama.
     * - {uuid}     -> UUID. Wajib dipakai buat command LuckPerms ("lp user
     *   {uuid} ..."), karena LP memvalidasi argumen <user> sebagai format
     *   username Mojang standar dan menolak username Bedrock yang diawali
     *   "." (mis. .BintangNS). UUID gak kena validasi itu, jadi aman buat
     *   Java maupun Bedrock.
     * - {nickname} -> nickname custom yang diisi pembeli (cuma ada kalau
     *   product->requires_nickname). Sudah divalidasi whitelist ketat di
     *   CheckoutController sebelum disimpan, jadi aman langsung masuk RCON.
     *
     * Command auto-generate dari rank_name (kalau duration_commands kosong)
     * selalu pakai UUID juga, dengan alasan yang sama.
     */
    public function resolveCommands(): array
    {
        $username = $this->minecraft_username;
        $uuid     = $this->minecraft_uuid ?: $this->minecraft_username;
        $nickname = $this->custom_nickname ?? '';
        $replace  = fn (string $cmd) => str_replace(
            ['{player}', '{uuid}', '{nickname}'],
            [$username, $uuid, $nickname],
            $cmd
        );

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

    /**
     * Dipanggil tiap kali delivery order ini sukses penuh (status baru pindah
     * ke "delivered") — dari webhook Midtrans, tombol Kirim Manual admin,
     * verifikasi pembayaran manual, maupun retry command yang gagal.
     */
    public function handleDeliverySuccess(): void
    {
        $this->activateLinkedNickname();
        $this->grantRankRewardIfApplicable();
    }

    /**
     * Tandai PlayerNickname yang lahir dari order ini (kalau ada) sebagai
     * nickname aktif si player, sekalian non-aktifin nickname lain miliknya.
     */
    private function activateLinkedNickname(): void
    {
        $nickname = PlayerNickname::where('order_id', $this->id)->first();
        if (!$nickname) {
            return;
        }

        PlayerNickname::whereRaw('LOWER(minecraft_username) = ?', [strtolower($this->minecraft_username)])
            ->update(['is_active' => false]);
        $nickname->update(['is_active' => true]);
    }

    /**
     * Kalau produk yang dibeli order ini punya reward jatah nickname gratis
     * (RankReward) dan player belum pernah kena kredit dari produk ini
     * sebelumnya, kredit sekarang. Murni transaksi database — gak ada
     * RCON/query ke server Minecraft sama sekali, dan gak bisa dobel walau
     * order-nya di-retry berkali-kali (unique constraint per username+produk).
     */
    private function grantRankRewardIfApplicable(): void
    {
        $reward = RankReward::where('product_id', $this->product_id)->first();
        if (!$reward) {
            return;
        }

        PlayerRankCredit::firstOrCreate([
            'minecraft_username' => strtolower($this->minecraft_username),
            'product_id'         => $this->product_id,
        ]);
    }
}
