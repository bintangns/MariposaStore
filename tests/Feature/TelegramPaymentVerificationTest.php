<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\MinecraftService;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use Tests\TestCase;

class TelegramPaymentVerificationTest extends TestCase
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

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
    }

    public function test_uploading_payment_proof_sends_photo_with_decision_buttons_to_admin(): void
    {
        Storage::fake('local');
        $order = Order::factory()->create();

        $this->withSession(['verified_username' => strtolower($order->minecraft_username)])
            ->post(route('checkout.manual.upload', $order->order_id), [
                'proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect(route('checkout.manual.uploaded', $order->order_id));

        $order->refresh();
        $this->assertSame('qris', $order->payment_type);
        Storage::disk('local')->assertExists($order->payment_proof);

        Http::assertSent(function (Request $request) use ($order) {
            if (! str_ends_with($request->url(), '/sendPhoto')) {
                return false;
            }

            $fields = collect($request->data())->pluck('contents', 'name');

            return $fields['chat_id'] === self::ADMIN_CHAT_ID
                && $fields->has('photo')
                && str_contains($fields['caption'], $order->order_id)
                && str_contains($fields['reply_markup'], TelegramService::ACTION_VERIFY.':'.$order->order_id)
                && str_contains($fields['reply_markup'], TelegramService::ACTION_REJECT.':'.$order->order_id);
        });
    }

    public function test_webhook_rejects_requests_without_valid_secret(): void
    {
        $order = Order::factory()->awaitingVerification()->create();

        $this->postJson(route('telegram.webhook'), $this->callbackPayload($order, TelegramService::ACTION_VERIFY_CONFIRM))
            ->assertForbidden();

        $this->postJson(
            route('telegram.webhook'),
            $this->callbackPayload($order, TelegramService::ACTION_VERIFY_CONFIRM),
            ['X-Telegram-Bot-Api-Secret-Token' => 'tebakan-ngawur'],
        )->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_webhook_ignores_buttons_pressed_by_someone_other_than_admin(): void
    {
        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('deliverProduct'));
        $order = Order::factory()->awaitingVerification()->create();

        $this->sendCallback($order, TelegramService::ACTION_VERIFY_CONFIRM, fromId: '999')->assertNoContent();

        $this->assertSame('pending', $order->fresh()->status);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/answerCallbackQuery')
            && $request['show_alert'] === true);
    }

    public function test_first_tap_on_verify_only_asks_for_confirmation(): void
    {
        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('deliverProduct'));
        $order = Order::factory()->awaitingVerification()->create();

        $this->sendCallback($order, TelegramService::ACTION_VERIFY)->assertNoContent();

        $this->assertSame('pending', $order->fresh()->status);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/editMessageReplyMarkup')
            && str_contains($request['reply_markup'], TelegramService::ACTION_VERIFY_CONFIRM.':'.$order->order_id));
    }

    public function test_confirming_verification_delivers_product_and_updates_message(): void
    {
        $order = Order::factory()->awaitingVerification()->create();
        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldReceive('deliverProduct')
            ->once()
            ->andReturn([['target' => 'default', 'command' => 'lp user x parent set vip', 'success' => true]]));

        $this->sendCallback($order, TelegramService::ACTION_VERIFY_CONFIRM)->assertNoContent();

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/editMessageCaption')
            && str_contains($request['caption'], 'Diverifikasi')
            && $request['reply_markup'] === json_encode(['inline_keyboard' => []]));
    }

    public function test_confirming_verification_twice_only_delivers_once(): void
    {
        $order = Order::factory()->awaitingVerification()->create();
        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldReceive('deliverProduct')
            ->once()
            ->andReturn([['target' => 'default', 'command' => 'lp user x parent set vip', 'success' => true]]));

        $this->sendCallback($order, TelegramService::ACTION_VERIFY_CONFIRM)->assertNoContent();
        $this->sendCallback($order, TelegramService::ACTION_VERIFY_CONFIRM)->assertNoContent();

        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_confirming_rejection_marks_order_failed(): void
    {
        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('deliverProduct'));
        $order = Order::factory()->awaitingVerification()->create();

        $this->sendCallback($order, TelegramService::ACTION_REJECT_CONFIRM)->assertNoContent();

        $this->assertSame('failed', $order->fresh()->status);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/editMessageCaption')
            && str_contains($request['caption'], 'Ditolak'));
    }

    public function test_admin_panel_verification_still_delivers_product(): void
    {
        $order = Order::factory()->awaitingVerification()->create();
        $this->mock(MinecraftService::class, fn (MockInterface $mock) => $mock->shouldReceive('deliverProduct')
            ->once()
            ->andReturn([['target' => 'default', 'command' => 'lp user x parent set vip', 'success' => true]]));

        $this->actingAs(User::factory()->create())
            ->post(route('admin.orders.verify-payment', $order))
            ->assertSessionHas('success');

        $this->assertSame('delivered', $order->fresh()->status);
    }

    private function sendCallback(Order $order, string $action, string $fromId = self::ADMIN_CHAT_ID): TestResponse
    {
        return $this->postJson(
            route('telegram.webhook'),
            $this->callbackPayload($order, $action, $fromId),
            ['X-Telegram-Bot-Api-Secret-Token' => app(TelegramService::class)->webhookSecret()],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function callbackPayload(Order $order, string $action, string $fromId = self::ADMIN_CHAT_ID): array
    {
        return [
            'update_id' => 1,
            'callback_query' => [
                'id' => 'callback-1',
                'from' => ['id' => (int) $fromId, 'first_name' => 'Admin'],
                'message' => [
                    'message_id' => 42,
                    'chat' => ['id' => (int) self::ADMIN_CHAT_ID],
                    'caption' => 'Bukti Pembayaran QRIS Masuk',
                ],
                'data' => $action.':'.$order->order_id,
            ],
        ];
    }
}
