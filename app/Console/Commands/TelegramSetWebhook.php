<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {--url= : URL webhook lengkap (default: route telegram.webhook dari APP_URL)}';

    protected $description = 'Daftarkan webhook bot Telegram biar tombol Verifikasi/Tolak di notif bisa dipakai (jalankan di server production)';

    public function handle(TelegramService $telegram): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('Isi TELEGRAM_BOT_TOKEN dan TELEGRAM_ADMIN_CHAT_ID di .env dulu.');

            return self::FAILURE;
        }

        $url = $this->option('url') ?: route('telegram.webhook');

        if (! str_starts_with($url, 'https://')) {
            $this->error("Telegram cuma mau webhook HTTPS yang bisa diakses dari internet, sedangkan URL-nya: {$url}");
            $this->line('Jalankan perintah ini di server production (APP_URL harus https://domain-kamu), atau kasih --url=https://...');

            return self::FAILURE;
        }

        $response = $telegram->setWebhook($url);

        if ($response->failed() || ! $response->json('ok')) {
            $this->error('Gagal set webhook: '.$response->json('description', $response->body()));

            return self::FAILURE;
        }

        $this->info("Webhook aktif: {$url}");
        $this->line('Tombol Verifikasi/Tolak di notif Telegram sekarang udah bisa dipakai.');

        return self::SUCCESS;
    }
}
