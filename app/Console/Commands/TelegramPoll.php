<?php

namespace App\Console\Commands;

use App\Services\TelegramCallbackHandler;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPoll extends Command
{
    protected $signature = 'telegram:poll {--once : Cek sekali lalu berhenti (dipakai di test)}';

    protected $description = 'Mode lokal: proses tombol Verifikasi/Tolak dari Telegram tanpa webhook (Telegram gak bisa manggil localhost)';

    public function handle(TelegramService $telegram, TelegramCallbackHandler $callbacks): int
    {
        if (! $telegram->isConfigured()) {
            $this->error('Isi TELEGRAM_BOT_TOKEN dan TELEGRAM_ADMIN_CHAT_ID di .env dulu.');

            return self::FAILURE;
        }

        $webhookUrl = $telegram->getWebhookInfo()->json('result.url');

        if (filled($webhookUrl)) {
            $this->error("Bot ini lagi pakai webhook ({$webhookUrl}), jadi mode polling gak bisa jalan.");
            $this->line('Mode ini cuma buat lokal. Biar gak bentrok sama website production, bikin bot kedua khusus testing di laptop.');

            return self::FAILURE;
        }

        $offset = $this->skipStaleTaps($telegram);
        $runOnce = (bool) $this->option('once');

        $this->info('Nunggu tombol ditekan di Telegram... (Ctrl+C buat berhenti)');

        do {
            $response = $telegram->getUpdates($offset, $runOnce ? 0 : 25);

            if ($response->failed()) {
                $this->warn('Gagal ambil update dari Telegram: '.$response->json('description', 'koneksi bermasalah').'. Coba lagi...');

                if ($runOnce) {
                    return self::FAILURE;
                }

                sleep(3);

                continue;
            }

            foreach ($response->json('result', []) as $update) {
                $offset = $update['update_id'] + 1;

                if (! isset($update['callback_query'])) {
                    continue;
                }

                $this->line('['.now()->timezone('Asia/Jakarta')->format('H:i:s').'] Tombol ditekan: '.($update['callback_query']['data'] ?? '-'));
                $callbacks->handle($update['callback_query']);
            }
        } while (! $runOnce);

        return self::SUCCESS;
    }

    /**
     * Lewati tap tombol yang ditekan sebelum mode ini dinyalain, biar tap
     * lama (mis. "Ya, verifikasi" dari kemarin) gak tiba-tiba dieksekusi.
     */
    private function skipStaleTaps(TelegramService $telegram): ?int
    {
        $lastUpdateId = $telegram->getUpdates(-1)->json('result.0.update_id');

        if ($lastUpdateId === null) {
            return null;
        }

        $this->warn('Tap tombol lama (sebelum perintah ini jalan) dilewati. Tekan ulang tombolnya di Telegram kalau masih perlu.');

        return $lastUpdateId + 1;
    }
}
