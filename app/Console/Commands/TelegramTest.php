<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramTest extends Command
{
    protected $signature = 'telegram:test';

    protected $description = 'Cek bot Telegram admin: tampilkan chat ID kamu, atau kirim pesan test kalau udah disetting';

    public function handle(TelegramService $telegram): int
    {
        if (blank(config('services.telegram.bot_token'))) {
            $this->error('TELEGRAM_BOT_TOKEN belum diisi di .env. Bikin bot dulu lewat @BotFather di Telegram.');

            return self::FAILURE;
        }

        $chatId = $telegram->adminChatId();

        if ($chatId === null) {
            return $this->showRecentChatIds($telegram);
        }

        $response = $telegram->sendMessage($chatId, '✅ Bot Telegram <b>Mariposa Store</b> tersambung! Notif bukti pembayaran QRIS bakal masuk ke sini.');

        if ($response->failed()) {
            $this->error('Gagal kirim pesan: '.$response->json('description', $response->body()));
            $this->line('Pastikan kamu udah pencet Start / kirim pesan apa aja ke bot-nya dulu, dan TELEGRAM_ADMIN_CHAT_ID benar.');

            return self::FAILURE;
        }

        $this->info("Pesan test terkirim ke chat {$chatId}. Cek Telegram kamu!");

        return self::SUCCESS;
    }

    /**
     * Tampilkan chat ID dari orang yang barusan ngirim pesan ke bot, biar
     * admin tinggal copy ID-nya sendiri ke TELEGRAM_ADMIN_CHAT_ID.
     */
    private function showRecentChatIds(TelegramService $telegram): int
    {
        $response = $telegram->getUpdates();

        if ($response->failed()) {
            $this->error('Gagal ambil pesan dari bot: '.$response->json('description', $response->body()));
            $this->line('Kalau webhook udah pernah diset, getUpdates gak bisa dipakai. Ambil chat ID kamu dari @userinfobot aja.');

            return self::FAILURE;
        }

        $chats = collect($response->json('result', []))
            ->map(fn (array $update) => data_get($update, 'message.from'))
            ->filter()
            ->unique('id')
            ->values();

        if ($chats->isEmpty()) {
            $this->warn('Belum ada pesan masuk ke bot. Buka bot kamu di Telegram, pencet Start (atau kirim pesan apa aja), lalu jalankan perintah ini lagi.');

            return self::FAILURE;
        }

        $this->info('Orang yang barusan chat bot kamu:');
        $this->table(['Chat ID', 'Nama', 'Username'], $chats->map(fn (array $from) => [
            $from['id'],
            trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? '')),
            isset($from['username']) ? '@'.$from['username'] : '-',
        ])->all());
        $this->line('Copy Chat ID milik kamu ke TELEGRAM_ADMIN_CHAT_ID di .env, lalu jalankan `php artisan telegram:test` lagi.');

        return self::SUCCESS;
    }
}
