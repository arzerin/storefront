<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    private array $images = [
        '/media/example1.webp',
        '/media/example2.webp',
        '/media/example3.webp',
        '/media/example4.webp',
        '/media/example5.webp',
        '/media/example6.webp',
        '/media/example7.webp',
        '/media/example8.webp',
        '/media/example9.webp',
        '/media/example10.webp',
        '/media/example11.webp',
        '/media/example12.webp'
    ];


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => fake()
                ->unique()
                ->randomElement($this->images),
        ];
    }
}
