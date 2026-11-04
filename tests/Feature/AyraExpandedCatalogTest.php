<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\AyraDemoCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AyraExpandedCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_category_has_four_products_with_real_images_for_every_color(): void
    {
        $this->seed(AyraDemoCatalogSeeder::class);
        $this->seed(AyraDemoCatalogSeeder::class);

        foreach (['Accessories', 'Clothing', 'Knitwear', 'Outerwear'] as $category) {
            $products = Product::where('category', $category)->where('is_published', true)
                ->with(['images', 'variants'])->get();

            $this->assertCount(4, $products);

            foreach ($products as $product) {
                foreach ($product->variants->pluck('color')->unique() as $color) {
                    $image = $product->imageForColor($color);
                    $this->assertNotNull($image, "Missing {$color} image for {$product->name}");
                    $this->assertSame($color, $image->color);
                    $this->assertFileExists(public_path($image->path));
                }
            }
        }
    }
}
