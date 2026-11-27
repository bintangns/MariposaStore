<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Bot Telegram buat admin: kirim notif + foto bukti pembayaran QRIS tiap
 * ada yang upload, lengkap dengan tombol Verifikasi / Tolak yang langsung
 * bisa ditekan dari chat (diproses lewat TelegramWebhookController).
 */
class TelegramService
{
    public const ACTION_VERIFY = 'verify';

    public const ACTION_VERIFY_CONFIRM = 'verify_ok';

    public const ACTION_REJECT = 'reject';

    public const ACTION_REJECT_CONFIRM = 'reject_ok';

    public const ACTION_BACK = 'back';

    public function isConfigured(): bool
    {
        return filled($this->botToken()) && filled($this->adminChatId());
    }

    public function adminChatId(): ?string
    {
        $chatId = config('services.telegram.admin_chat_id');

        return filled($chatId) ? (string) $chatId : null;
    }

    /**
     * Secret yang dikirim Telegram di header X-Telegram-Bot-Api-Secret-Token
     * setiap manggil webhook. Diturunkan dari bot token, jadi gak perlu
     * setting .env tambahan — cukup jalanin ulang telegram:set-webhook kalau
     * token-nya diganti.
     */
    public function webhookSecret(): ?string
    {
        $token = $this->botToken();

        return filled($token) ? hash('sha256', 'mariposa-telegram-webhook:'.$token) : null;
    }

    /**
     * Kirim notif bukti pembayaran baru ke admin. Gak pernah throw — kalau
     * Telegram error, cukup dicatat di log biar alur upload customer gak ikut gagal.
     */
    public function notifyPaymentProof(Order $order): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $payload = [
                'chat_id' => $this->adminChatId(),
                'caption' => $this->orderSummary($order),
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($this->decisionKeyboard($order)),
            ];

            $disk = Storage::disk('local');
            $response = null;

            if ($order->payment_proof && $disk->exists($order->payment_proof)) {
                $contents = $disk->get($order->payment_proof);
                $filename = basename($order->payment_proof);

                $response = Http::attach('photo', $contents, $filename)->post($this->endpoint('sendPhoto'), $payload);

                // Screenshot yang kepanjangan/kegedean bisa ditolak sebagai
                // "photo" — kirim ulang sebagai file biasa.
                if ($response->failed()) {
                    $response = Http::attach('document', $contents, $filename)->post($this->endpoint('sendDocument'), $payload);
                }
            }

            if (! $response || $response->failed()) {
                $response = Http::post($this->endpoint('sendMessage'), [
                    'chat_id' => $payload['chat_id'],
                    'text' => $payload['caption'],
                    'parse_mode' => 'HTML',
                    'reply_markup' => $payload['reply_markup'],
                ]);
            }

            if ($response->failed()) {
                Log::error('Telegram notifyPaymentProof gagal untuk order '.$order->order_id.': '.$response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Telegram notifyPaymentProof error: '.$e->getMessage());
        }
    }

    /**
     * Ganti tombol di bawah pesan (mis. jadi tombol konfirmasi "Yakin?").
     *
     * @param  array{inline_keyboard: array<int, array<int, array{text: string, callback_data: string}>>}  $keyboard
     */
    public function replaceKeyboard(string $chatId, int $messageId, array $keyboard): void
    {
        $this->call('editMessageReplyMarkup', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    /**
     * Tandai pesan notif sebagai selesai: tulis status akhirnya di bawah
     * ringkasan order & hapus semua tombolnya.
     */
    public function resolveMessage(string $chatId, int $messageId, bool $hasCaption, Order $order, string $statusLine): void
    {
        $text = $this->orderSummary($order)."\n\n".$statusLine;

        $this->call($hasCaption ? 'editMessageCaption' : 'editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            $hasCaption ? 'caption' : 'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => []]),
        ]);
    }

    public function answerCallback(string $callbackQueryId, ?string $text = null, bool $showAlert = false): void
    {
        $this->call('answerCallbackQuery', array_filter([
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert ?: null,
        ]));
    }

    public function sendMessage(string $chatId, string $text): Response
    {
        return Http::post($this->endpoint('sendMessage'), [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    /**
     * Ambil update (pesan / tap tombol) yang belum diproses. Cuma bisa dipakai
     * selama bot belum punya webhook. $timeout > 0 = long polling (detik).
     */
    public function getUpdates(?int $offset = null, int $timeout = 0): Response
    {
        return Http::timeout($timeout + 10)->get($this->endpoint('getUpdates'), array_filter([
            'offset' => $offset,
            'timeout' => $timeout,
        ], fn ($value) => $value !== null));
    }

    public function setWebhook(string $url): Response
    {
        return Http::post($this->endpoint('setWebhook'), [
            'url' => $url,
            'secret_token' => $this->webhookSecret(),
            'allowed_updates' => json_encode(['callback_query']),
            'drop_pending_updates' => true,
        ]);
    }

    public function getWebhookInfo(): Response
    {
        return Http::get($this->endpoint('getWebhookInfo'));
    }

    /**
     * @return array{inline_keyboard: array<int, array<int, array{text: string, callback_data: string}>>}
     */
    public function decisionKeyboard(Order $order): array
    {
        return ['inline_keyboard' => [[
            $this->button('✅ Verifikasi', self::ACTION_VERIFY, $order),
            $this->button('❌ Tolak', self::ACTION_REJECT, $order),
        ]]];
    }

    /**
     * @return array{inline_keyboard: array<int, array<int, array{text: string, callback_data: string}>>}
     */
    public function confirmVerifyKeyboard(Order $order): array
    {
        return ['inline_keyboard' => [
            [$this->button('✅ Ya, verifikasi & kirim rank', self::ACTION_VERIFY_CONFIRM, $order)],
            [$this->button('↩ Batal', self::ACTION_BACK, $order)],
        ]];
    }

    /**
     * @return array{inline_keyboard: array<int, array<int, array{text: string, callback_data: string}>>}
     */
    public function confirmRejectKeyboard(Order $order): array
    {
        return ['inline_keyboard' => [
            [$this->button('❌ Ya, tolak order ini', self::ACTION_REJECT_CONFIRM, $order)],
            [$this->button('↩ Batal', self::ACTION_BACK, $order)],
        ]];
    }

    public function orderSummary(Order $order): string
    {
        $product = $order->product?->name ?? '-';
        if ($order->duration_label) {
            $product .= ' - '.$order->duration_label;
        }

        $lines = [
            '🧾 <b>Bukti Pembayaran QRIS Masuk</b>',
            '',
            '<b>Order:</b> <code>'.e($order->order_id).'</code>',
            '<b>Player:</b> <code>'.e($order->minecraft_username).'</code>',
            '<b>Produk:</b> '.e($product),
        ];

        if ($order->custom_nickname) {
            $lines[] = '<b>Nickname:</b> <code>'.e($order->custom_nickname).'</code>';
        }

        $lines[] = '<b>Total:</b> '.e($order->formatted_amount);
        $lines[] = '<b>Upload:</b> '.$order->updated_at?->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB';

        return implode("\n", $lines);
    }

    /**
     * @return array{text: string, callback_data: string}
     */
    private function button(string $text, string $action, Order $order): array
    {
        return ['text' => $text, 'callback_data' => $action.':'.$order->order_id];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function call(string $method, array $payload): void
    {
        try {
            $response = Http::post($this->endpoint($method), $payload);

            if ($response->failed()) {
                Log::warning("Telegram {$method} gagal: ".$response->body());
            }
        } catch (\Throwable $e) {
            Log::error("Telegram {$method} error: ".$e->getMessage());
        }
    }

    private function endpoint(string $method): string
    {
        return 'https://api.telegram.org/bot'.$this->botToken().'/'.$method;
    }

    private function botToken(): ?string
    {
        return config('services.telegram.bot_token');
    }
}
