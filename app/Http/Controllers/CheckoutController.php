<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use App\Services\MinecraftService;
use App\Services\DiscordService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private MidtransService  $midtrans,
        private MinecraftService $minecraft,
        private DiscordService   $discord,
    ) {}

    public function create(Request $request, Product $product)
    {
        $request->validate(['username' => ['required', 'string', 'regex:/^\.?[a-zA-Z0-9_]{3,16}$/']]);

        $username = $request->username;

        // Pastikan username sudah verified
        if (session('verified_username') !== strtolower($username)) {
            return back()->with('error', 'Username belum diverifikasi!');
        }

        // Kalau produk ini punya opsi durasi, wajib pilih salah satu
        $duration = null;
        if ($product->durations->isNotEmpty()) {
            $duration = $product->durations()->find($request->input('duration_id'));
            if (!$duration) {
                return back()->with('error', 'Pilih durasi rank terlebih dahulu.');
            }
        }

        // Buat order
        $order = Order::create([
            'order_id'            => 'MRP-' . strtoupper(Str::random(8)),
            'minecraft_username'  => $username,
            'minecraft_uuid'      => session('verified_uuid'),
            'product_id'          => $product->id,
            'product_duration_id' => $duration?->id,
            'duration_label'      => $duration?->label,
            'duration_days'       => $duration?->days,
            'duration_commands'   => $duration?->commands,
            'amount'              => $duration?->price ?? $product->price,
            'status'              => 'pending',
        ]);

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
            $results      = $this->minecraft->deliverProduct($order->minecraft_username, $commands);
            $allDelivered = collect($results)->every(fn ($r) => $r['success']);

            $order->update([
                'status'       => $allDelivered ? 'delivered' : 'paid',
                'delivered_at' => $allDelivered ? now() : null,
                'delivery_log' => json_encode($results),
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
