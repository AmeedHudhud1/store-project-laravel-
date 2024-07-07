<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
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
            'order_date' => $this->faker->date,
            'status' => $this->faker->randomElement(['0', '1','2','3']),
            'delivery_address' => $this->faker->address,
            'customer_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
        ];
    }
}
