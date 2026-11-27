<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['VIP', 'MVP', 'Legend']).' Rank',
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(10, 200) * 1000,
            'commands' => ['lp user {uuid} parent set vip'],
            'features' => [],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
