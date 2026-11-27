<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => 'MRP-'.strtoupper(Str::random(8)),
            'minecraft_username' => fake()->regexify('[A-Za-z0-9_]{6,12}'),
            'minecraft_uuid' => fake()->uuid(),
            'product_id' => Product::factory(),
            'amount' => fake()->numberBetween(10, 200) * 1000,
            'status' => 'pending',
            'terms_accepted_at' => now(),
            'expires_at' => now()->addMinutes(Order::PAYMENT_EXPIRY_MINUTES),
        ];
    }

    /**
     * Order yang customer-nya udah upload bukti pembayaran QRIS dan
     * nunggu diverifikasi admin.
     */
    public function awaitingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_type' => 'qris',
            'payment_proof' => 'payment-proofs/'.$attributes['order_id'].'-proof.jpg',
        ]);
    }
}
