<?php

namespace App\Http\Controllers;

use App\Services\TelegramCallbackHandler;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Menerima tombol yang ditekan admin di notif bot Telegram (production).
 *
 * Keamanan: request wajib bawa secret header dari Telegram (diset lewat
 * `php artisan telegram:set-webhook`), dan tombolnya cuma bisa dipakai oleh
 * akun Telegram yang ID-nya = TELEGRAM_ADMIN_CHAT_ID (dicek di handler).
 */
class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramService $telegram,
        private TelegramCallbackHandler $callbacks,
    ) {}

    public function __invoke(Request $request): Response
    {
        $secret = $this->telegram->webhookSecret();
        $sentSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token');

        abort_unless($secret !== null && hash_equals($secret, $sentSecret), 403);

        $callback = $request->input('callback_query');

        if (is_array($callback)) {
            $this->callbacks->handle($callback);
        }

        return response()->noContent();
    }
}
