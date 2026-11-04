<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class AyraDemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['Wool Belted Coat', 'A softly structured layer for every kind of day. Wrap yourself in rich forest green wool and a silhouette that feels as considered as it looks.', 32900, 'Outerwear', '/media/ayra/coat.webp', ['Forest Green', 'Stone'], ['XS', 'S', 'M', 'L']],
            ['Ribbed Turtleneck Sweater', 'Warm, comfortable and endlessly versatile. The ribbed texture and relaxed fit make it an easy favorite.', 8900, 'Knitwear', '/media/ayra/sweater.webp', ['Ivory', 'Oat'], ['XS', 'S', 'M', 'L', 'XL']],
            ['Structured Everyday Tote', 'A clean-lined tote with room for the essentials, crafted for the pace of everyday life.', 19900, 'Accessories', '/media/ayra/tote.webp', ['Olive', 'Black'], ['One Size']],
            ['Relaxed Wide-Leg Trouser', 'Fluid tailoring that moves with you. An easy foundation for workdays and weekends alike.', 11800, 'Clothing', '/media/ayra/trousers.webp', ['Ivory', 'Sand'], ['XS', 'S', 'M', 'L', 'XL']],
        ];

        foreach ($samples as [$name, $description, $price, $category, $image, $colors, $sizes]) {
            Category::firstOrCreate(['name' => $category]);
            $product = Product::firstOrCreate(['name' => $name], [
                'description' => $description,
                'price' => $price,
                'category' => $category,
                'is_published' => true,
            ]);

            if ($product->images()->where('path', $image)->doesntExist()) {
                $product->images()->create(['path' => $image, 'featured' => true]);
            }

            if ($name === 'Wool Belted Coat') {
                foreach (['/media/ayra/coat-back.webp', '/media/ayra/coat-detail.webp'] as $galleryImage) {
                    if ($product->images()->where('path', $galleryImage)->doesntExist()) {
                        $product->images()->create(['path' => $galleryImage, 'featured' => false]);
                    }
                }
            }

            $colorImages = match ($name) {
                'Wool Belted Coat' => [
                    '/media/ayra/coat.webp' => 'Forest Green',
                    '/media/ayra/coat-back.webp' => 'Forest Green',
                    '/media/ayra/coat-detail.webp' => 'Forest Green',
                    '/media/ayra/coat-stone.webp' => 'Stone',
                ],
                'Ribbed Turtleneck Sweater' => [
                    '/media/ayra/sweater.webp' => 'Ivory',
                    '/media/ayra/sweater-oat.webp' => 'Oat',
                ],
                'Structured Everyday Tote' => [
                    '/media/ayra/tote.webp' => 'Olive',
                    '/media/ayra/tote-black.webp' => 'Black',
                ],
                'Relaxed Wide-Leg Trouser' => [
                    '/media/ayra/trousers.webp' => 'Ivory',
                    '/media/ayra/trousers-sand.webp' => 'Sand',
                ],
            };

            foreach ($colorImages as $path => $color) {
                $product->images()->updateOrCreate(
                    ['path' => $path],
                    ['color' => $color, 'featured' => $path === $image],
                );
            }

            if ($product->variants()->doesntExist()) {
                foreach ($colors as $color) {
                    foreach ($sizes as $size) {
                        $product->variants()->create(compact('color', 'size'));
                    }
                }
            }
        }

        Product::whereIn('name', ['Tshirt', 'Laravel Cap', 'Laravel Scrunchy', 'Sweater'])
            ->whereNotIn('name', array_column($samples, 0))
            ->update(['is_published' => false]);

        $this->call(AyraExpandedCatalogSeeder::class);
        $this->call(AyraCollectionsSeeder::class);
        $this->call(AyraManufacturersSeeder::class);
        $this->call(AyraSiteContentSeeder::class);
        $this->call(AyraSiteBuilderSeeder::class);
    }
}
