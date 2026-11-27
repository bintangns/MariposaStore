<?php

namespace App\Http\Controllers;

use App\Models\Gradient;
use App\Models\Order;
use App\Models\PlayerNickname;
use App\Models\Product;
use App\Models\Setting;
use App\Services\DiscordService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private DiscordService $discord,
        private TelegramService $telegram,
    ) {}

    public function create(Request $request, Product $product)
    {
        if (Setting::isMaintenanceMode()) {
            return back()->with('error', Setting::maintenanceMessage());
        }

        $request->validate(['username' => ['required', 'string', 'regex:/^\.?[a-zA-Z0-9_]{3,16}$/']]);

        $username = $request->username;

        // Pastikan username sudah verified
        if (session('verified_username') !== strtolower($username)) {
            return back()->with('error', 'Username belum diverifikasi!');
        }

        // Wajib centang Syarat & Ketentuan (termasuk kebijakan no-refund) sebelum bayar
        if (! $request->boolean('terms_accepted')) {
            return back()->with('error', 'Kamu harus membaca dan menyetujui Syarat & Ketentuan terlebih dahulu.');
        }

        // Kalau produk ini punya opsi durasi, wajib pilih salah satu
        $duration = null;
        if ($product->durations->isNotEmpty()) {
            $duration = $product->durations()->find($request->input('duration_id'));
            if (! $duration) {
                return back()->with('error', 'Pilih durasi rank terlebih dahulu.');
            }
        }

        // Upgrade dari rank aktif yang udah dimiliki: kredit sebesar yang udah
        // pernah dibayar di order lama, dipotong dari harga target. Divalidasi
        // ulang di sini (bukan cuma trust harga yang dikirim dari halaman
        // upgrade) — target harus beneran salah satu opsi upgrade yang valid
        // dari order sumbernya, dan order sumber itu belum pernah dipakai buat
        // upgrade lain (anti double-spend kreditnya).
        $upgradeFromOrder = null;
        $upgradeCredit = 0;
        if ($request->filled('upgrade_from_order_id')) {
            $upgradeFromOrder = Order::whereRaw('LOWER(minecraft_username) = ?', [session('verified_username')])
                ->find($request->input('upgrade_from_order_id'));

            if (! $upgradeFromOrder) {
                return back()->with('error', 'Order upgrade sumber gak ditemukan.');
            }

            $alreadyUsed = Order::where('upgraded_from_order_id', $upgradeFromOrder->id)
                ->whereIn('status', ['pending', 'paid', 'delivered'])
                ->exists();
            if ($alreadyUsed) {
                return back()->with('error', 'Order ini udah pernah dipakai buat upgrade sebelumnya.');
            }

            $isEligible = collect($upgradeFromOrder->eligibleUpgradeOptions())
                ->contains(fn ($opt) => $opt['product']->id === $product->id && $opt['duration']->id === $duration?->id);
            if (! $isEligible) {
                return back()->with('error', 'Kombinasi upgrade ini gak valid.');
            }

            $upgradeCredit = $upgradeFromOrder->amount;

            // Kredit dari rank lama kebesaran sampai harga target-nya kepotong
            // pas Rp0 -> jangan diloloskan (bukan berarti gratis). Tombolnya
            // udah di-disable di halaman Store buat opsi kayak gini, ini
            // jaring pengaman kalau ada yang coba submit langsung.
            $targetPrice = $duration?->price ?? $product->price;
            if (max(0, $targetPrice - $upgradeCredit) === 0) {
                return back()->with('error', 'Opsi ini otomatis jadi gratis dari kredit upgrade kamu, jadi gak bisa dipilih. Pilih durasi/produk lain.');
            }
        }

        // Produk cosmetics/custom nickname: dua mode.
        // - "gradient": customer pilih 3 warna sendiri (color picker, boleh
        //   mulai dari quick-pick preset admin lalu diubah manual). Warnanya
        //   di-compute SERVER-SIDE (bukan trust input client) dari
        //   Gradient::apply(username asli) — gradient ini gak disimpan ke
        //   tabel gradients, cuma dipakai sekali buat hitung nickname-nya.
        // - "custom": whitelist-only huruf, angka, spasi, kode warna/format
        //   &0-9a-f / &k-o / &r — karena string ini langsung masuk ke command
        //   RCON, karakter lain ditolak (anti command injection).
        $nickname = null;
        $nicknameLabel = null;
        $gradient = null;
        if ($product->requires_nickname) {
            if ($product->nickname_type === 'gradient') {
                $request->validate([
                    'colors' => ['required', 'array', 'size:3'],
                    'colors.*' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
                ], [
                    'colors.required' => 'Pilih 3 warna buat gradient nickname kamu.',
                    'colors.size' => 'Harus tepat 3 warna.',
                    'colors.*.regex' => 'Format warna gak valid.',
                ]);

                $gradient = new Gradient(['colors' => array_values($request->input('colors'))]);
                $nickname = $gradient->apply($username);
                $nicknameLabel = 'Gradient Custom';
            } else {
                $request->validate([
                    'nickname' => [
                        'required',
                        'string',
                        'max:64',
                        'regex:/^(?:&[0-9a-fk-orA-FK-OR]|[a-zA-Z0-9 ])+$/',
                    ],
                ], [
                    'nickname.required' => 'Isi nickname kamu dulu ya.',
                    'nickname.regex' => 'Nickname cuma boleh huruf, angka, spasi, dan kode warna &0-&f / &k-&o / &r.',
                ]);

                $nickname = trim($request->input('nickname'));
                $visibleLength = strlen(preg_replace('/&[0-9a-fk-orA-FK-OR]/', '', $nickname));

                if ($visibleLength < 1) {
                    return back()->withErrors(['nickname' => 'Nickname gak boleh cuma kode warna doang, isi teksnya juga.'])->withInput();
                }
                if ($visibleLength > 32) {
                    return back()->withErrors(['nickname' => 'Nickname (tanpa kode warna) maksimal 32 karakter.'])->withInput();
                }

                $nicknameLabel = preg_replace('/&[0-9a-fk-orA-FK-OR]/', '', $nickname);
            }
        }

        // Buat order + (kalau cosmetics) catatan kepemilikan nickname di
        // "inventory" website-nya, dibungkus 1 transaction — kalau salah satu
        // gagal disimpan, dua-duanya rollback (gak ada order nyangkut setengah jalan).
        $order = DB::transaction(function () use ($product, $username, $duration, $nickname, $nicknameLabel, $gradient, $upgradeFromOrder, $upgradeCredit) {
            $order = Order::create([
                'order_id' => 'MRP-'.strtoupper(Str::random(8)),
                'minecraft_username' => $username,
                'minecraft_uuid' => session('verified_uuid'),
                'custom_nickname' => $nickname,
                'terms_accepted_at' => now(),
                'product_id' => $product->id,
                'product_duration_id' => $duration?->id,
                'upgraded_from_order_id' => $upgradeFromOrder?->id,
                'duration_label' => $duration?->label,
                'duration_days' => $duration?->days,
                'duration_commands' => $duration?->commands,
                'amount' => max(0, Setting::applyPromo($duration?->price ?? $product->price) - $upgradeCredit),
                'status' => 'pending',
                'expires_at' => now()->addMinutes(Order::PAYMENT_EXPIRY_MINUTES),
            ]);

            if ($product->requires_nickname) {
                $order->setRelation('product', $product);

                PlayerNickname::create([
                    'minecraft_username' => $username,
                    'minecraft_uuid' => session('verified_uuid'),
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'gradient_id' => $gradient?->id,
                    // Produk lama yang belum pernah disimpan ulang lewat form
                    // baru masih punya nickname_type null di database — treat
                    // sebagai "custom" (behavior yang dipakai di branch validasi atas).
                    'type' => $product->nickname_type === 'gradient' ? 'gradient' : 'custom',
                    'label' => $nicknameLabel,
                    'value' => $nickname,
                    'command_template' => $order->resolveCommands(),
                    'is_active' => false,
                ]);
            }

            return $order;
        });

        // Satu-satunya metode pembayaran: QRIS GoPay Merchant + upload bukti
        // transaksi, lalu admin verifikasi manual dari panel admin.
        return redirect()->route('checkout.manual', $order->order_id);
    }

    /**
     * Halaman pembayaran QRIS + form upload bukti transaksi.
     */
    public function manualPayment(Order $order)
    {
        abort_unless(strtolower($order->minecraft_username) === session('verified_username'), 403);

        // Sekaligus cek expired & pulihkan batas waktu normal kalau customer
        // cuma refresh / balik lagi dalam masa tenggang (lihat presence()).
        $order->markPaymentPageBack();
        $order->expireIfNeeded();

        if ($order->status !== 'pending') {
            return redirect()->route('orders.invoice', $order->order_id);
        }

        return view('pages.checkout-manual', ['order' => $order]);
    }

    /**
     * Simpan bukti pembayaran yang diupload. Order tetap "pending" sampai
     * admin verifikasi manual & trigger delivery dari panel admin.
     */
    public function uploadProof(Request $request, Order $order)
    {
        abort_unless(strtolower($order->minecraft_username) === session('verified_username'), 403);

        // Customer masih di halaman pembayaran (submit form) — batalkan efek
        // "ninggalin halaman" yang mungkin kepicu waktu dia buka app bank.
        $order->markPaymentPageBack();
        $order->expireIfNeeded();

        if ($order->status !== 'pending') {
            return redirect()->route('orders.invoice', $order->order_id)
                ->with('error', 'Waktu pembayaran buat order ini udah habis dan otomatis dibatalkan.');
        }

        $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'proof.required' => 'Upload bukti pembayaran dulu ya.',
            'proof.image' => 'File harus berupa gambar (JPG/PNG/WEBP).',
            'proof.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
            'proof.max' => 'Ukuran gambar maksimal 5MB.',
            'proof.uploaded' => 'Gagal upload, kemungkinan ukuran filenya kegedean. Coba kompres/screenshot ulang lalu upload lagi.',
        ]);

        // Nama file sendiri (bukan nama asli upload) supaya gak bisa ditebak/di-enumerate.
        $filename = $order->order_id.'-'.Str::random(20).'.'.$request->file('proof')->extension();
        $path = $request->file('proof')->storeAs('payment-proofs', $filename, 'local');

        $order->update([
            'payment_proof' => $path,
            'payment_type' => 'qris',
        ]);

        $this->discord->notifyNewOrder($order);
        $this->telegram->notifyPaymentProof($order);

        return redirect()->route('checkout.manual.uploaded', $order->order_id);
    }

    public function manualUploaded(Order $order)
    {
        abort_unless(strtolower($order->minecraft_username) === session('verified_username'), 403);

        return view('pages.checkout-result', ['status' => 'manual_pending', 'order' => $order]);
    }

    /**
     * Dibatalkan sendiri oleh customer (tombol "Batalkan Pembayaran" atau
     * timer habis di halaman checkout/checkout-manual) — biar order yang
     * ditinggal gak nyangkut selamanya sebagai "pending" di daftar admin.
     * Cuma bisa dipakai selama belum ada bukti transfer yang diupload;
     * kalau udah upload, artinya udah nunggu verifikasi admin dan gak boleh
     * dibatalkan sepihak dari sini.
     */
    public function cancel(Order $order)
    {
        abort_unless(strtolower($order->minecraft_username) === session('verified_username'), 403);

        if ($order->status === 'pending' && ! $order->payment_proof) {
            $order->update(['status' => 'cancelled', 'payment_status' => 'cancelled_by_user']);
        }

        return view('pages.checkout-result', ['status' => 'cancelled', 'order' => $order->fresh()]);
    }

    /**
     * Dipanggil lewat navigator.sendBeacon dari halaman pembayaran:
     * state=left  -> customer nutup / ninggalin halaman, order dibatalkan
     *                 kalau dia gak balik dalam Order::LEAVE_GRACE_SECONDS;
     * state=back  -> halaman dibuka lagi dari cache browser (tombol back),
     *                 batas waktu normalnya dipulihkan.
     */
    public function presence(Request $request, Order $order)
    {
        if (strtolower($order->minecraft_username) !== session('verified_username')) {
            return response()->noContent(403);
        }

        $request->input('state') === 'back'
            ? $order->markPaymentPageBack()
            : $order->markPaymentPageLeft();

        return response()->noContent();
    }
}
