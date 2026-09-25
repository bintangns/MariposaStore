<?php

namespace App\Http\Controllers;

use App\Models\Gradient;
use App\Models\Order;
use App\Models\PlayerNickname;
use App\Models\Product;
use App\Models\Setting;
use App\Services\DuitkuService;
use App\Services\MinecraftService;
use App\Services\DiscordService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    public function __construct(
        private DuitkuService     $duitku,
        private MinecraftService $minecraft,
        private DiscordService   $discord,
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
        if (!$request->boolean('terms_accepted')) {
            return back()->with('error', 'Kamu harus membaca dan menyetujui Syarat & Ketentuan terlebih dahulu.');
        }

        // Kalau produk ini punya opsi durasi, wajib pilih salah satu
        $duration = null;
        if ($product->durations->isNotEmpty()) {
            $duration = $product->durations()->find($request->input('duration_id'));
            if (!$duration) {
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

            if (!$upgradeFromOrder) {
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
            if (!$isEligible) {
                return back()->with('error', 'Kombinasi upgrade ini gak valid.');
            }

            $upgradeCredit = $upgradeFromOrder->amount;
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
                    'colors'   => ['required', 'array', 'size:3'],
                    'colors.*' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
                ], [
                    'colors.required'   => 'Pilih 3 warna buat gradient nickname kamu.',
                    'colors.size'       => 'Harus tepat 3 warna.',
                    'colors.*.regex'    => 'Format warna gak valid.',
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
                    'nickname.regex'    => 'Nickname cuma boleh huruf, angka, spasi, dan kode warna &0-&f / &k-&o / &r.',
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
                'order_id'            => 'MRP-' . strtoupper(Str::random(8)),
                'minecraft_username'  => $username,
                'minecraft_uuid'      => session('verified_uuid'),
                'custom_nickname'     => $nickname,
                'terms_accepted_at'   => now(),
                'product_id'          => $product->id,
                'product_duration_id' => $duration?->id,
                'upgraded_from_order_id' => $upgradeFromOrder?->id,
                'duration_label'      => $duration?->label,
                'duration_days'       => $duration?->days,
                'duration_commands'   => $duration?->commands,
                'amount'              => max(0, Setting::applyPromo($duration?->price ?? $product->price) - $upgradeCredit),
                'status'              => 'pending',
            ]);

            if ($product->requires_nickname) {
                $order->setRelation('product', $product);

                PlayerNickname::create([
                    'minecraft_username' => $username,
                    'minecraft_uuid'     => session('verified_uuid'),
                    'product_id'         => $product->id,
                    'order_id'           => $order->id,
                    'gradient_id'        => $gradient?->id,
                    // Produk lama yang belum pernah disimpan ulang lewat form
                    // baru masih punya nickname_type null di database — treat
                    // sebagai "custom" (behavior yang dipakai di branch validasi atas).
                    'type'               => $product->nickname_type === 'gradient' ? 'gradient' : 'custom',
                    'label'              => $nicknameLabel,
                    'value'              => $nickname,
                    'command_template'   => $order->resolveCommands(),
                    'is_active'          => false,
                ]);
            }

            return $order;
        });

        // Mode pembayaran manual (Duitku dimatikan sementara): skip gateway,
        // arahkan ke halaman upload bukti transfer.
        if (Setting::isManualPaymentMode()) {
            return redirect()->route('checkout.manual', $order->order_id);
        }

        // Buat invoice Duitku
        $invoice = $this->duitku->createInvoice($order);
        $order->update(['payment_reference' => $invoice['reference'] ?? null]);

        return view('pages.checkout', [
            'order'      => $order,
            'product'    => $product,
            'paymentUrl' => $invoice['paymentUrl'],
        ]);
    }

    /**
     * Halaman instruksi transfer manual + form upload bukti pembayaran.
     */
    public function manualPayment(Order $order)
    {
        abort_unless(strtolower($order->minecraft_username) === session('verified_username'), 403);

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

        if ($order->status !== 'pending') {
            return redirect()->route('orders.invoice', $order->order_id);
        }

        $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'proof.required' => 'Upload bukti pembayaran dulu ya.',
            'proof.image'    => 'File harus berupa gambar (JPG/PNG/WEBP).',
            'proof.mimes'    => 'Format gambar harus JPG, PNG, atau WEBP.',
            'proof.max'      => 'Ukuran gambar maksimal 5MB.',
            'proof.uploaded' => 'Gagal upload, kemungkinan ukuran filenya kegedean. Coba kompres/screenshot ulang lalu upload lagi.',
        ]);

        // Nama file sendiri (bukan nama asli upload) supaya gak bisa ditebak/di-enumerate.
        $filename = $order->order_id . '-' . Str::random(20) . '.' . $request->file('proof')->extension();
        $path = $request->file('proof')->storeAs('payment-proofs', $filename, 'local');

        $order->update([
            'payment_proof' => $path,
            'payment_type'  => 'manual_transfer',
        ]);

        $this->discord->notifyNewOrder($order);

        return redirect()->route('checkout.manual.uploaded', $order->order_id);
    }

    public function manualUploaded(Order $order)
    {
        abort_unless(strtolower($order->minecraft_username) === session('verified_username'), 403);

        return view('pages.checkout-result', ['status' => 'manual_pending', 'order' => $order]);
    }

    public function notification(Request $request)
    {
        $payload = $request->all();
        Log::info('Duitku callback: ', $payload);

        if (!$this->duitku->verifyCallbackSignature($payload)) {
            Log::warning('Invalid Duitku signature for order: ' . ($payload['merchantOrderId'] ?? '?'));
            return response('Invalid signature', 400);
        }

        $order = Order::where('order_id', $payload['merchantOrderId'] ?? null)->first();
        if (!$order) return response('Order not found', 404);

        // Callback cuma dipakai sebagai "sinyal untuk cek ulang" — statusnya
        // sendiri diambil dari transactionStatus API (statusCode 00/01/lainnya
        // punya arti yang jelas & konsisten), bukan dari field resultCode di
        // body callback yang di dokumentasi Duitku artinya ambigu/berubah-ubah.
        $this->reconcileOrder($order);

        return response('OK', 200);
    }

    /**
     * Cek ulang status transaksi ke Duitku (transactionStatus API) dan
     * terapkan ke order. Dipakai baik oleh notification() (webhook) maupun
     * halaman success/pending (fallback kalau webhook belum/tidak sampai,
     * mis. dev di localhost).
     */
    private function reconcileOrder(Order $order): Order
    {
        if (in_array($order->status, ['delivered', 'failed'])) {
            return $order;
        }

        try {
            $payload = $this->duitku->getTransactionStatus($order->order_id);
            $statusCode = $payload['statusCode'] ?? null;

            if ($statusCode === '00') {
                $this->handleSuccessfulPayment($order, $payload);
            } elseif ($statusCode === '01' || $statusCode === null) {
                $order->update(['payment_status' => 'pending']);
            } else {
                $order->update(['status' => 'failed', 'payment_status' => $payload['statusMessage'] ?? $statusCode]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal reconcile order ' . $order->order_id . ': ' . $e->getMessage());
        }

        return $order->fresh();
    }

    private function handleSuccessfulPayment(Order $order, array $payload): void
    {
        if ($order->status === 'delivered') return;

        $order->update([
            'status'         => 'paid',
            'payment_type'   => $payload['paymentMethod'] ?? $payload['paymentCode'] ?? null,
            'payment_status' => 'success',
        ]);

        // Deliver produk via RCON
        $commands = $order->resolveCommands();
        if (!empty($commands)) {
            $results      = $this->minecraft->deliverProduct($commands);
            $allDelivered = collect($results)->every(fn ($r) => $r['success']);

            $order->update([
                'status'       => $allDelivered ? 'delivered' : 'paid',
                'delivered_at' => $allDelivered ? now() : null,
                'delivery_log' => $results,
            ]);

            if ($allDelivered) {
                $order->handleDeliverySuccess();
            } else {
                Log::warning('Delivery gagal untuk order ' . $order->order_id . ', RCON tidak reachable atau command gagal.');
            }
        }

        // Notif Discord
        $this->discord->notifyNewOrder($order);
        if ($order->status === 'delivered') {
            $this->discord->notifyDelivered($order);
        }
    }

    public function success(Request $request)
    {
        $order = Order::where('order_id', $request->query('order'))->first();
        if ($order) {
            $order = $this->reconcileOrder($order);
        }
        return view('pages.checkout-result', ['status' => 'success', 'order' => $order]);
    }

    public function pending(Request $request)
    {
        $order = Order::where('order_id', $request->query('order'))->first();
        if ($order) {
            $order = $this->reconcileOrder($order);
        }
        return view('pages.checkout-result', ['status' => 'pending', 'order' => $order]);
    }

    public function failed(Request $request)
    {
        $order = Order::where('order_id', $request->query('order'))->first();
        return view('pages.checkout-result', ['status' => 'failed', 'order' => $order]);
    }
}
