<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Money\Currency;
use Money\Money;

class Product extends Model
{
    use HasFactory;

    public function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'sale_price' => MoneyCast::class,
            'sale_starts_at' => 'datetime',
            'sale_ends_at' => 'datetime',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function quantityDiscounts(): HasMany
    {
        return $this->hasMany(QuantityDiscount::class)->orderBy('min_quantity');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function activeSalePriceCents(): ?int
    {
        if ($this->getRawOriginal('sale_price') === null
            || ($this->sale_starts_at && now()->lt($this->sale_starts_at))
            || ($this->sale_ends_at && now()->gte($this->sale_ends_at))) {
            return null;
        }

        return (int) $this->getRawOriginal('sale_price');
    }

    public function unitPriceCentsForQuantity(int $quantity): int
    {
        $regular = (int) $this->price->getAmount();
        $sale = $this->activeSalePriceCents();
        $base = $sale ?? $regular;
        if ($sale !== null && $this->discount_mode === 'sale_only') {
            return $base;
        }

        $tiers = $this->relationLoaded('quantityDiscounts') ? $this->quantityDiscounts : $this->quantityDiscounts()->get();
        $tier = $tiers->filter(fn (QuantityDiscount $row) => $row->min_quantity <= $quantity)
            ->sortByDesc('min_quantity')->first();
        if (! $tier) {
            return $base;
        }

        $value = (float) $tier->value;
        return match ($tier->type) {
            'percent' => max(1, (int) round($base * (1 - $value / 100))),
            'amount' => max(1, $base - (int) round($value * 100)),
            'fixed' => min($base, (int) round($value * 100)),
            default => $base,
        };
    }

    public function unitPriceForQuantity(int $quantity): Money
    {
        return new Money($this->unitPriceCentsForQuantity($quantity), new Currency('USD'));
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
