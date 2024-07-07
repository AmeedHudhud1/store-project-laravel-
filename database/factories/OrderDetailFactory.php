<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(50, 100),
            'product_id' => function () {
                return \App\Models\Product::factory()->create()->id;
            },
            'order_id' => function () {
                return \App\Models\Order::factory()->create()->id;
            },
        ];
    }
}
