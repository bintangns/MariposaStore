<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\MidtransService;
use App\Services\MinecraftService;
use App\Services\DiscordService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    public function __construct(
        private MidtransService  $midtrans,
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

        // Produk cosmetics/custom nickname: validasi ketat karena string ini
        // langsung masuk ke command RCON. Whitelist-only: huruf, angka, spasi,
        // dan kode warna/format &0-9a-f / &k-o / &r — gak ada karakter lain
        // yang lolos (aman dari command injection / RCON packet corruption).
        $nickname = null;
        if ($product->requires_nickname) {
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
        }

        // Buat order
        $order = Order::create([
            'order_id'            => 'MRP-' . strtoupper(Str::random(8)),
            'minecraft_username'  => $username,
            'minecraft_uuid'      => session('verified_uuid'),
            'custom_nickname'     => $nickname,
            'terms_accepted_at'   => now(),
            'product_id'          => $product->id,
            'product_duration_id' => $duration?->id,
            'duration_label'      => $duration?->label,
            'duration_days'       => $duration?->days,
            'duration_commands'   => $duration?->commands,
            'amount'              => Setting::applyPromo($duration?->price ?? $product->price),
            'status'              => 'pending',
        ]);

        // Mode pembayaran manual (Midtrans dimatikan sementara): skip Snap,
        // arahkan ke halaman upload bukti transfer.
        if (Setting::isManualPaymentMode()) {
            return redirect()->route('checkout.manual', $order->order_id);
        }

        // Buat transaksi Midtrans
        $snap = $this->midtrans->createTransaction($order);
        $order->update(['midtrans_transaction_id' => $snap['token'] ?? null]);

        return view('pages.checkout', [
            'order'      => $order,
            'product'    => $product,
            'snapToken'  => $snap['token'],
            'clientKey'  => $this->midtrans->getClientKey(),
            'isProduction' => $this->midtrans->isProduction(),
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
        Log::info('Midtrans notification: ', $payload);

        // Verifikasi signature
        $valid = $this->midtrans->verifySignature(
            $payload['order_id'],
            $payload['status_code'],
            $payload['gross_amount'],
            $payload['signature_key']
        );

        if (!$valid) {
            Log::warning('Invalid Midtrans signature for order: ' . $payload['order_id']);
            return response('Invalid signature', 400);
        }

        $order = Order::where('order_id', $payload['order_id'])->first();
        if (!$order) return response('Order not found', 404);

        $this->applyTransactionStatus($order, $payload);

        return response('OK', 200);
    }

    /**
     * Terapkan status transaksi Midtrans ke order. Dipakai oleh notification()
     * dan reconcileOrder() (fallback saat webhook belum/tidak sampai).
     */
    private function applyTransactionStatus(Order $order, array $payload): void
    {
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus       = $payload['fraud_status'] ?? 'accept';

        if (in_array($transactionStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
            $this->handleSuccessfulPayment($order, $payload);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $order->update(['status' => 'failed', 'midtrans_status' => $transactionStatus]);
        } elseif ($transactionStatus === 'pending') {
            $order->update(['midtrans_status' => 'pending']);
        }
    }

    /**
     * Cek ulang status ke Midtrans jika order masih pending. Jaring pengaman
     * untuk kasus webhook notification tidak/belum sampai (mis. dev di localhost).
     */
    private function reconcileOrder(Order $order): Order
    {
        if (in_array($order->status, ['delivered', 'failed'])) {
            return $order;
        }

        try {
            $payload = $this->midtrans->getTransactionStatus($order->order_id);
            if (isset($payload['transaction_status'])) {
                $this->applyTransactionStatus($order, $payload);
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
            'payment_type'   => $payload['payment_type'] ?? null,
            'midtrans_status' => $payload['transaction_status'],
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

            if (!$allDelivered) {
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
