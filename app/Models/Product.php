<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    public function casts(): array
    {
        return [
            'price' => MoneyCast::class
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function featured_image(): HasOne
    {
        return $this->hasOne(Image::class)->ofMany('featured', 'max');
    }
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function imageForColor(?string $color): ?Image
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        return $images->first(fn (Image $image) => $image->color === $color)
            ?? $images->first(fn (Image $image) => $image->color === null);
    }
}
