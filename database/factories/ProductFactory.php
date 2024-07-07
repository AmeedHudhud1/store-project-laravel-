<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $manufacturerNames = ['Company A', 'Company B', 'Company C', 'Company D', 'Company E'];
        return [
            'Name' => $this->faker->words(2, true),
            'Price' => $this->faker->randomFloat(2, 10, 100),
            'Category' => $this->faker->randomElement(['game', 'film', 'Books', 'electronic']),
            'Description' => $this->faker->sentence,
            // 'Image' => $this->faker->imageUrl(""),
            'Image' => '/images/test.jpg',
            'Number_of_times_requested' => $this->faker->numberBetween(1, 100),
            'manufacturer_name' => $this->faker->randomElement($manufacturerNames),
            'remaining_quantity' => $this->faker->numberBetween(0, 100),
        ];
    }
}
