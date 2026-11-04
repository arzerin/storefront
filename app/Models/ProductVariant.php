<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $fillable = ['color', 'size'];

    public static function swatchColor(?string $color): string
    {
        return match (strtolower((string) $color)) {
            'forest green', 'green', 'olive' => '#3d5140',
            'deep olive' => '#303b2b',
            'sage' => '#8a947a',
            'ivory', 'cream', 'ecru' => '#f2eadc',
            'white' => '#f9f8f3',
            'oat', 'sand', 'stone' => '#c9bca8',
            'camel', 'tan' => '#b78d61',
            'cognac' => '#995a31',
            'espresso', 'chocolate' => '#4b3329',
            'black' => '#222722',
            'charcoal' => '#555753',
            'slate' => '#6d7680',
            'ink' => '#27364b',
            'indigo' => '#243d61',
            'sky blue' => '#a9c8d6',
            'mist blue' => '#a6bdc9',
            'terracotta' => '#b66e55',
            'red' => '#aa655e',
            'orange' => '#c88960',
            'blue' => '#60788f',
            default => '#d8d3c7',
        };
    }
}
