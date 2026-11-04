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
            'forest green', 'green', 'olive' => '#173b2a',
            'ivory', 'cream' => '#f2eadc',
            'oat', 'sand', 'stone' => '#c9bca8',
            'black' => '#222722',
            'red' => '#aa655e',
            'orange' => '#c88960',
            'blue' => '#60788f',
            default => '#d8d3c7',
        };
    }
}
