<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;
    private string $baseUrl;

    public function __construct()
    {
        $this->serverKey    = config('minecraft.midtrans_server_key');
        $this->clientKey    = config('minecraft.midtrans_client_key');
        $this->isProduction = config('minecraft.midtrans_is_production', false);
        $this->baseUrl      = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Buat transaksi Midtrans Snap
     */
    public function createTransaction(Order $order): array
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_id,
                'gross_amount' => $order->amount,
            ],
            'customer_details' => [
                'first_name' => $order->minecraft_username,
                'email'      => $order->minecraft_username . '@mariposa.id',
            ],
            'item_details' => [
                [
                    'id'       => $order->product->id,
                    'price'    => $order->amount,
                    'quantity' => 1,
                    'name'     => $order->product->name,
                ],
            ],
            'callbacks' => [
                'finish'  => route('checkout.success') . '?order=' . $order->order_id,
                'error'   => route('checkout.failed') . '?order=' . $order->order_id,
                'pending' => route('checkout.pending') . '?order=' . $order->order_id,
            ],
        ];

        $response = $this->post('/transactions', $params);
        return $response;
    }

    /**
     * Verifikasi signature dari Midtrans notification
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signature): bool
    {
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return hash_equals($expected, $signature);
    }

    /**
     * Cek status transaksi
     */
    public function getTransactionStatus(string $orderId): array
    {
        $baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->serverKey, '')
            ->get("{$baseUrl}/{$orderId}/status");

        return $response->json();
    }

    private function post(string $endpoint, array $data): array
    {
        $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->serverKey, '')
            ->post($this->baseUrl . $endpoint, $data);

        if (!$response->successful()) {
            Log::error('Midtrans error: ' . $response->body());
            throw new \Exception('Midtrans request failed: ' . $response->body());
        }

        return $response->json();
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }
}
