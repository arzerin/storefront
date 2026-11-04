<?php

namespace App\Livewire;

use App\Actions\Webshop\AddProductVariantToCart;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowProduct extends Component
{
    use InteractsWithBanner;

    public Product $product;
    public string $selectedColor = '';
    public string $selectedSize = '';
    public int $quantity = 1;

    public function mount(): void
    {
        abort_unless($this->product->is_published, 404);
        $first = $this->product->variants()->first();
        $this->selectedColor = $first?->color ?? '';
        $this->selectedSize = $first?->size ?? '';
        $ids = array_values(array_unique(array_merge([$this->product->id], session('recently_viewed', []))));
        session(['recently_viewed' => array_slice($ids, 0, 6)]);
    }

    #[Computed]
    public function colors()
    {
        return $this->product->variants->pluck('color')->filter()->unique()->values();
    }

    #[Computed]
    public function sizes()
    {
        return $this->product->variants->where('color', $this->selectedColor)->pluck('size')->filter()->unique()->values();
    }

    #[Computed]
    public function galleryImages()
    {
        $images = $this->product->images;
        $matching = $images->where('color', $this->selectedColor)->values();

        return $matching->isNotEmpty()
            ? $matching
            : $images->whereNull('color')->values();
    }

    #[Computed]
    public function relatedProducts()
    {
        return Product::where('is_published', true)->whereKeyNot($this->product->id)
            ->when($this->product->category, fn ($query) => $query->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$this->product->category]))
            ->with('featured_image')->limit(3)->get();
    }

    public function selectColor(string $color): void
    {
        abort_unless($this->colors->contains($color), 404);
        $this->selectedColor = $color;
        unset($this->sizes, $this->galleryImages);
        if (! $this->sizes->contains($this->selectedSize)) {
            $this->selectedSize = $this->sizes->first() ?? '';
        }
        $this->dispatch('product-color-changed', path: $this->galleryImages->first()?->path ?? '/media/ayra/sweater.webp');
    }

    public function addToCart(AddProductVariantToCart $action): void
    {
        $this->validate([
            'selectedColor' => 'required|string',
            'selectedSize' => 'required|string',
            'quantity' => 'required|integer|min:1|max:20',
        ]);
        $variant = $this->product->variants()->where('color', $this->selectedColor)
            ->where('size', $this->selectedSize)->first();
        if (! $variant) {
            $this->addError('selectedSize', 'Choose an available option.');
            return;
        }
        $action->add(variantId: $variant->id, quantity: $this->quantity);
        $this->banner('Added to your bag.');
        $this->dispatch('product.added');
    }

    public function render(): View
    {
        return view('livewire.product');
    }
}
