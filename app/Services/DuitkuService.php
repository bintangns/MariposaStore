<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi Duitku POP (https://docs.duitku.com/pop/id/). Formula
 * signature & endpoint mengikuti SDK resmi duitku/duitku-php 1:1:
 * - createInvoice: sha256(merchantCode . timestamp . apiKey)
 * - transactionStatus & callback: md5(merchantCode . [merchantOrderId|amount+merchantOrderId] . apiKey)
 */
class DuitkuService
{
    private ?string $merchantCode;
    private ?string $apiKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct()
    {
        // Nullable: service ini di-construct otomatis lewat dependency
        // injection tiap kali CheckoutController dipanggil — termasuk pas
        // Mode Pembayaran Manual aktif, di mana Duitku sama sekali gak
        // dipakai. Jangan crash di sini kalau kredensialnya belum diisi;
        // validasinya baru dilakukan pas method yang beneran manggil API
        // Duitku (createInvoice/getTransactionStatus) dipanggil.
        $this->merchantCode = config('minecraft.duitku_merchant_code');
        $this->apiKey       = config('minecraft.duitku_api_key');
        $this->isProduction = (bool) config('minecraft.duitku_is_production', false);
        $this->baseUrl      = $this->isProduction
            ? 'https://api-prod.duitku.com'
            : 'https://api-sandbox.duitku.com';
    }

    private function ensureConfigured(): void
    {
        if (!$this->merchantCode || !$this->apiKey) {
            throw new \Exception('Duitku belum dikonfigurasi — isi DUITKU_MERCHANT_CODE dan DUITKU_API_KEY di .env.');
        }
    }

    /**
     * Buat invoice Duitku buat order ini. Response-nya berisi `paymentUrl`
     * (halaman pembayaran Duitku yang di-redirect ke customer) dan
     * `reference` (ID transaksi Duitku, disimpan buat reconcile/status check).
     */
    public function createInvoice(Order $order): array
    {
        $this->ensureConfigured();

        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash('sha256', $this->merchantCode . $timestamp . $this->apiKey);

        $payload = [
            'paymentAmount'   => $order->amount,
            'merchantOrderId' => $order->order_id,
            'productDetails'  => $order->product->name . ($order->duration_label ? ' - ' . $order->duration_label : ''),
            'email'           => $order->minecraft_username . '@mariposa.id',
            'customerVaName'  => $order->minecraft_username,
            'callbackUrl'     => route('payment.notification'),
            'returnUrl'       => route('checkout.success') . '?order=' . $order->order_id,
            'expiryPeriod'    => 60,
        ];

        $response = Http::withHeaders([
            'x-duitku-signature'    => $signature,
            'x-duitku-timestamp'    => $timestamp,
            'x-duitku-merchantcode' => $this->merchantCode,
        ])->post($this->baseUrl . '/api/merchant/createInvoice', $payload);

        if (!$response->successful()) {
            Log::error('Duitku createInvoice error: ' . $response->body());
            throw new \Exception('Duitku request failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Verifikasi signature dari callback (notification) Duitku.
     */
    public function verifyCallbackSignature(array $payload): bool
    {
        $expected = md5(
            ($payload['merchantCode'] ?? '') .
            ($payload['amount'] ?? '') .
            ($payload['merchantOrderId'] ?? '') .
            $this->apiKey
        );

        return hash_equals($expected, $payload['signature'] ?? '');
    }

    /**
     * Cek ulang status transaksi ke Duitku — dipakai sebagai fallback kalau
     * callback belum/tidak sampai (mis. dev di localhost).
     */
    public function getTransactionStatus(string $merchantOrderId): array
    {
        $this->ensureConfigured();

        $signature = md5($this->merchantCode . $merchantOrderId . $this->apiKey);

        $response = Http::post($this->baseUrl . '/api/merchant/transactionStatus', [
            'merchantCode'    => $this->merchantCode,
            'merchantOrderId' => $merchantOrderId,
            'signature'       => $signature,
        ]);

        return $response->json() ?? [];
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }
}
