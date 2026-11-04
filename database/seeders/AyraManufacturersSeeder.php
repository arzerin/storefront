<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class AyraManufacturersSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            'Ayra Studio' => ['Wool Belted Coat', 'Crescent Crossbody Bag', 'Cotton Poplin Shirt', 'Fine-Knit Cardigan', 'Cropped Trench Jacket', 'Tailored Straight-Leg Jean'],
            'Ayra Atelier' => ['Ribbed Turtleneck Sweater', 'Structured Everyday Tote', 'Draped Midi Dress', 'Cashmere Crewneck Sweater', 'Quilted Light Jacket'],
            'Form & Field' => ['Relaxed Wide-Leg Trouser', 'Sculpted Leather Belt', 'Everyday Silk Scarf', 'Textured Knit Vest', 'Double-Faced Wrap Coat'],
        ];

        foreach ($samples as $name => $products) {
            $manufacturer = Manufacturer::firstOrCreate(['name' => $name]);
            Product::whereIn('name', $products)->whereNull('manufacturer_id')
                ->update(['manufacturer_id' => $manufacturer->id]);
        }
    }
}
