<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\MinecraftService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class TelegramPollCommandTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_CHAT_ID = '123456789';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.telegram.bot_token' => 'test-bot-token',
            'services.telegram.admin_chat_id' => self::ADMIN_CHAT_ID,
            'minecraft.discord_webhook_url' => null,
        ]);
    }

    public function test_poll_processes_new_button_taps_and_skips_stale_ones(): void
    {
        $staleOrder = Order::factory()->awaitingVerification()->create();
        $newOrder = Order::factory()->awaitingVerification()->create();

        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldReceive('deliverProduct')
            ->once()
            ->andReturn([['target' => 'default', 'command' => 'lp user x parent set vip', 'success' => true]]));

        Http::fake([
            'api.telegram.org/*/getWebhookInfo' => Http::response(['ok' => true, 'result' => ['url' => '']]),
            'api.telegram.org/*/getUpdates*' => Http::sequence()
                ->push(['ok' => true, 'result' => [$this->callbackUpdate(10, $staleOrder, TelegramService::ACTION_VERIFY_CONFIRM)]])
                ->push(['ok' => true, 'result' => [$this->callbackUpdate(11, $newOrder, TelegramService::ACTION_VERIFY_CONFIRM)]]),
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);

        $this->artisan('telegram:poll', ['--once' => true])->assertSuccessful();

        $this->assertSame('pending', $staleOrder->fresh()->status);
        $this->assertSame('delivered', $newOrder->fresh()->status);
    }

    public function test_poll_refuses_to_run_when_bot_uses_webhook(): void
    {
        Http::fake([
            'api.telegram.org/*/getWebhookInfo' => Http::response(['ok' => true, 'result' => ['url' => 'https://example.com/telegram/webhook']]),
        ]);

        $this->artisan('telegram:poll', ['--once' => true])->assertFailed();

        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/getUpdates'));
    }

    /**
     * @return array<string, mixed>
     */
    private function callbackUpdate(int $updateId, Order $order, string $action): array
    {
        return [
            'update_id' => $updateId,
            'callback_query' => [
                'id' => 'callback-'.$updateId,
                'from' => ['id' => (int) self::ADMIN_CHAT_ID, 'first_name' => 'Admin'],
                'message' => [
                    'message_id' => $updateId,
                    'chat' => ['id' => (int) self::ADMIN_CHAT_ID],
                    'caption' => 'Bukti Pembayaran QRIS Masuk',
                ],
                'data' => $action.':'.$order->order_id,
            ],
        ];
    }
}
