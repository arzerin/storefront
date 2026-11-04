<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class AyraExpandedCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Crescent Crossbody Bag', 'A compact sculptural bag with an easy curved shape and room for your daily essentials.', 14900, 'Accessories', ['One Size'], ['Espresso' => 'crossbody-espresso', 'Tan' => 'crossbody-tan']],
            ['Sculpted Leather Belt', 'A polished finishing touch in supple leather with a simple, considered buckle.', 5900, 'Accessories', ['S', 'M', 'L'], ['Black' => 'belt-black', 'Cognac' => 'belt-cognac']],
            ['Everyday Silk Scarf', 'Fluid silk with a soft sheen, made to wear at the neck, in your hair, or on a bag.', 7900, 'Accessories', ['One Size'], ['Sage' => 'scarf-sage', 'Terracotta' => 'scarf-terracotta']],
            ['Cotton Poplin Shirt', 'A crisp everyday shirt with a relaxed line and quietly refined details.', 8900, 'Clothing', ['XS', 'S', 'M', 'L', 'XL'], ['White' => 'shirt-white', 'Sky Blue' => 'shirt-sky-blue']],
            ['Draped Midi Dress', 'An effortless midi silhouette with fluid drape for days that turn into evenings.', 16800, 'Clothing', ['XS', 'S', 'M', 'L'], ['Black' => 'dress-black', 'Deep Olive' => 'dress-deep-olive']],
            ['Tailored Straight-Leg Jean', 'A clean straight leg in substantial denim, designed to sit comfortably all day.', 12800, 'Clothing', ['XS', 'S', 'M', 'L', 'XL'], ['Indigo' => 'jean-indigo', 'Ecru' => 'jean-ecru']],
            ['Fine-Knit Cardigan', 'A soft layer with a fine knit texture and an easy, versatile fit.', 11900, 'Knitwear', ['XS', 'S', 'M', 'L'], ['Oat' => 'cardigan-oat', 'Charcoal' => 'cardigan-charcoal']],
            ['Cashmere Crewneck Sweater', 'Lightweight warmth and a timeless crewneck shape in a soft cashmere feel.', 18900, 'Knitwear', ['XS', 'S', 'M', 'L'], ['Camel' => 'cashmere-camel', 'Mist Blue' => 'cashmere-mist-blue']],
            ['Textured Knit Vest', 'A richly textured vest made for easy layering over a shirt or under a coat.', 9900, 'Knitwear', ['XS', 'S', 'M', 'L'], ['Cream' => 'vest-cream', 'Chocolate' => 'vest-chocolate']],
            ['Cropped Trench Jacket', 'The familiar trench, reimagined with a shorter cut and relaxed modern shape.', 22900, 'Outerwear', ['XS', 'S', 'M', 'L'], ['Sand' => 'trench-sand', 'Slate' => 'trench-slate']],
            ['Quilted Light Jacket', 'A lightly quilted layer for changing days, with a relaxed, easy silhouette.', 19900, 'Outerwear', ['XS', 'S', 'M', 'L'], ['Olive' => 'quilted-olive', 'Ink' => 'quilted-ink']],
            ['Double-Faced Wrap Coat', 'A soft, full-length coat with a self-tie belt and beautifully simple lines.', 34900, 'Outerwear', ['XS', 'S', 'M', 'L'], ['Camel' => 'wrap-camel', 'Charcoal' => 'wrap-charcoal']],
        ];

        foreach ($products as [$name, $description, $price, $category, $sizes, $colors]) {
            Category::firstOrCreate(['name' => $category]);
            $product = Product::firstOrCreate(['name' => $name], [
                'description' => $description,
                'price' => $price,
                'category' => $category,
                'is_published' => true,
            ]);

            foreach ($colors as $color => $image) {
                $product->images()->updateOrCreate(
                    ['path' => "/media/ayra/{$image}.webp"],
                    ['color' => $color, 'featured' => $color === array_key_first($colors)],
                );

                foreach ($sizes as $size) {
                    $product->variants()->firstOrCreate(compact('color', 'size'));
                }
            }
        }
    }
}
