<?php

namespace App\Services;

use App\Models\Order;

/**
 * Proses tombol yang ditekan admin di notif bot Telegram (callback_query).
 * Dipakai bareng oleh webhook (production) dan `php artisan telegram:poll`
 * (development di laptop, karena Telegram gak bisa manggil localhost).
 */
class TelegramCallbackHandler
{
    public function __construct(
        private TelegramService $telegram,
        private PaymentVerificationService $payments,
    ) {}

    /**
     * @param  array<string, mixed>  $callback  Objek callback_query dari Telegram.
     */
    public function handle(array $callback): void
    {
        $callbackId = (string) ($callback['id'] ?? '');

        if ($this->telegram->adminChatId() === null || (string) data_get($callback, 'from.id') !== $this->telegram->adminChatId()) {
            $this->telegram->answerCallback($callbackId, 'Kamu gak punya akses buat aksi ini.', true);

            return;
        }

        $chatId = (string) data_get($callback, 'message.chat.id');
        $messageId = (int) data_get($callback, 'message.message_id');
        $hasCaption = data_get($callback, 'message.caption') !== null;

        [$action, $orderId] = array_pad(explode(':', (string) ($callback['data'] ?? ''), 2), 2, null);
        $order = $orderId ? Order::with('product')->where('order_id', $orderId)->first() : null;

        if (! $order || $chatId === '' || $messageId === 0) {
            $this->telegram->answerCallback($callbackId, 'Order gak ditemukan (mungkin udah dihapus).', true);

            return;
        }

        $isAwaitingVerification = $order->status === 'pending' && $order->payment_proof;

        if (! $isAwaitingVerification && in_array($action, [TelegramService::ACTION_VERIFY, TelegramService::ACTION_REJECT, TelegramService::ACTION_BACK], true)) {
            $this->resolveAsAlreadyProcessed($callbackId, $chatId, $messageId, $hasCaption, $order);

            return;
        }

        match ($action) {
            TelegramService::ACTION_VERIFY => $this->askConfirmation($callbackId, $chatId, $messageId, $this->telegram->confirmVerifyKeyboard($order)),
            TelegramService::ACTION_REJECT => $this->askConfirmation($callbackId, $chatId, $messageId, $this->telegram->confirmRejectKeyboard($order)),
            TelegramService::ACTION_BACK => $this->askConfirmation($callbackId, $chatId, $messageId, $this->telegram->decisionKeyboard($order)),
            TelegramService::ACTION_VERIFY_CONFIRM => $this->verify($callbackId, $chatId, $messageId, $hasCaption, $order),
            TelegramService::ACTION_REJECT_CONFIRM => $this->reject($callbackId, $chatId, $messageId, $hasCaption, $order),
            default => $this->telegram->answerCallback($callbackId, 'Aksi gak dikenal.'),
        };
    }

    /**
     * @param  array{inline_keyboard: array<int, array<int, array{text: string, callback_data: string}>>}  $keyboard
     */
    private function askConfirmation(string $callbackId, string $chatId, int $messageId, array $keyboard): void
    {
        $this->telegram->answerCallback($callbackId);
        $this->telegram->replaceKeyboard($chatId, $messageId, $keyboard);
    }

    private function verify(string $callbackId, string $chatId, int $messageId, bool $hasCaption, Order $order): void
    {
        if (! $this->payments->claimForVerification($order)) {
            $this->resolveAsAlreadyProcessed($callbackId, $chatId, $messageId, $hasCaption, $order->refresh());

            return;
        }

        // Jawab dulu biar loading di tombol Telegram berhenti, karena kirim
        // command RCON-nya bisa makan waktu beberapa detik.
        $this->telegram->answerCallback($callbackId, 'Diverifikasi, ngirim rank...');

        $allDelivered = $this->payments->deliver($order);
        $time = now()->timezone('Asia/Jakarta')->format('H:i');

        $statusLine = $allDelivered
            ? "✅ <b>Diverifikasi</b>, rank udah terkirim ({$time} WIB)."
            : '⚠️ <b>Diverifikasi</b>, tapi '.count($order->fresh()->failed_commands).' command gagal terkirim. Kirim ulang dari panel admin → Orders.';

        $this->telegram->resolveMessage($chatId, $messageId, $hasCaption, $order, $statusLine);
    }

    private function reject(string $callbackId, string $chatId, int $messageId, bool $hasCaption, Order $order): void
    {
        if (! $this->payments->reject($order)) {
            $this->resolveAsAlreadyProcessed($callbackId, $chatId, $messageId, $hasCaption, $order->refresh());

            return;
        }

        $time = now()->timezone('Asia/Jakarta')->format('H:i');

        $this->telegram->answerCallback($callbackId, 'Order ditolak.');
        $this->telegram->resolveMessage($chatId, $messageId, $hasCaption, $order, "❌ <b>Ditolak</b> ({$time} WIB).");
    }

    private function resolveAsAlreadyProcessed(string $callbackId, string $chatId, int $messageId, bool $hasCaption, Order $order): void
    {
        $this->telegram->answerCallback($callbackId, 'Order ini udah diproses sebelumnya.', true);
        $this->telegram->resolveMessage($chatId, $messageId, $hasCaption, $order, 'ℹ️ Udah diproses sebelumnya (status: '.e($order->status_label).').');
    }
}
