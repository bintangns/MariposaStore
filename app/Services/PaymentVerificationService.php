<?php

namespace App\Services;

use App\Models\Order;

/**
 * Logika verifikasi bukti pembayaran QRIS & pengiriman produk, dipakai
 * bareng oleh panel admin dan tombol konfirmasi di bot Telegram supaya
 * dua-duanya selalu berperilaku sama persis.
 */
class PaymentVerificationService
{
    public function __construct(
        private MinecraftService $minecraft,
        private DiscordService $discord,
    ) {}

    /**
     * Tandai order "paid" secara atomik. Cuma berhasil kalau order masih
     * "pending" dan sudah ada bukti pembayaran — kalau tombol verifikasi
     * kepencet dua kali (atau diproses dari panel & Telegram barengan),
     * cuma satu yang lolos, jadi produk gak pernah terkirim dobel.
     */
    public function claimForVerification(Order $order): bool
    {
        $claimed = Order::whereKey($order->getKey())
            ->where('status', 'pending')
            ->whereNotNull('payment_proof')
            ->update(['status' => 'paid']);

        if ($claimed === 0) {
            return false;
        }

        $order->refresh();

        return true;
    }

    /**
     * Tolak order yang masih "pending" (mis. bukti gak valid/palsu).
     * Atomik dengan alasan yang sama kayak claimForVerification().
     */
    public function reject(Order $order): bool
    {
        $rejected = Order::whereKey($order->getKey())
            ->where('status', 'pending')
            ->update(['status' => 'failed']);

        if ($rejected === 0) {
            return false;
        }

        $order->refresh();

        return true;
    }

    /**
     * Kirim command RCON produk untuk order ini & update status-nya.
     * Return true kalau semua command sukses terkirim.
     */
    public function deliver(Order $order): bool
    {
        $results = $this->minecraft->deliverProduct($order->resolveCommands());
        $allDelivered = collect($results)->every(fn (array $result) => $result['success']);

        $order->update([
            'status' => $allDelivered ? 'delivered' : 'paid',
            'delivered_at' => $allDelivered ? now() : $order->delivered_at,
            'delivery_log' => $results,
        ]);

        if ($allDelivered) {
            $order->handleDeliverySuccess();
            $this->discord->notifyDelivered($order);
        }

        return $allDelivered;
    }
}
