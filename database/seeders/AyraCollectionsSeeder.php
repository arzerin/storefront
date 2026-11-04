<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AyraCollectionsSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            ['New Arrivals', 'new-arrivals', 'Fresh pieces for the season.', ['Crescent Crossbody Bag', 'Cotton Poplin Shirt', 'Fine-Knit Cardigan', 'Cropped Trench Jacket']],
            ['The Work Edit', 'the-work-edit', 'Considered pieces for the everyday routine.', ['Structured Everyday Tote', 'Cotton Poplin Shirt', 'Tailored Straight-Leg Jean', 'Double-Faced Wrap Coat']],
            ['Weekend Layers', 'weekend-layers', 'Soft layers for slower days.', ['Ribbed Turtleneck Sweater', 'Fine-Knit Cardigan', 'Quilted Light Jacket', 'Everyday Silk Scarf']],
            ['End of Season', 'end-of-season', 'Last-chance favorites, thoughtfully selected.', ['Wool Belted Coat', 'Draped Midi Dress', 'Textured Knit Vest', 'Sculpted Leather Belt']],
        ];

        foreach ($collections as [$name, $slug, $description, $names]) {
            $collection = Collection::firstOrCreate(['slug' => $slug], compact('name', 'description') + ['is_published' => true]);
            $collection->products()->syncWithoutDetaching(Product::whereIn('name', $names)->pluck('id')->all());
        }
    }
}
