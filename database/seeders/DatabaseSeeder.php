<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Sequence;
use App\Models\Product;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;



class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Product::factory(4)
            ->hasVariants(5)
            ->has(Image::factory(3)->sequence(fn (Sequence $sequence) => ['featured' => $sequence->index %3 === 0]))
            ->create();

        User::factory()->create([
            'email' => 'foo@foomail.com',
            'password' => bcrypt('password'),
        ]);
    }
}
