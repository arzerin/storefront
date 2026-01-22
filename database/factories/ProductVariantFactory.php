<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'color' => fake()->randomElement(['red', 'green', 'blue', 'orange']),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
        ];
    }
}
