<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordService
{
    public function notifyNewOrder(Order $order): void
    {
        $webhookUrl = config('minecraft.discord_webhook_url');
        if (!$webhookUrl) return;

        try {
            Http::post($webhookUrl, [
                'embeds' => [[
                    'title'       => '💰 Pembelian Baru!',
                    'color'       => 0x57F287,
                    'fields'      => [
                        ['name' => 'Player',  'value' => $order->minecraft_username, 'inline' => true],
                        ['name' => 'Produk',  'value' => $order->product->name,      'inline' => true],
                        ['name' => 'Harga',   'value' => $order->formatted_amount,   'inline' => true],
                        ['name' => 'Order ID','value' => $order->order_id,           'inline' => false],
                    ],
                    'timestamp' => now()->toIso8601String(),
                ]],
            ]);
        } catch (\Exception $e) {
            Log::error('Discord webhook error: ' . $e->getMessage());
        }
    }

    public function notifyDelivered(Order $order): void
    {
        $webhookUrl = config('minecraft.discord_webhook_url');
        if (!$webhookUrl) return;

        try {
            Http::post($webhookUrl, [
                'embeds' => [[
                    'title'  => '✅ Produk Terkirim!',
                    'color'  => 0x5865F2,
                    'fields' => [
                        ['name' => 'Player', 'value' => $order->minecraft_username, 'inline' => true],
                        ['name' => 'Produk', 'value' => $order->product->name,      'inline' => true],
                    ],
                    'timestamp' => now()->toIso8601String(),
                ]],
            ]);
        } catch (\Exception $e) {
            Log::error('Discord webhook error: ' . $e->getMessage());
        }
    }
}
