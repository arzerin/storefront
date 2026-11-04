<?php

namespace App\Livewire;

use App\Factories\CartFactory;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowCart extends Component
{
    #[Computed]
    public function cart(): Cart
    {
        return CartFactory::make();
    }

    #[Computed]
    public function items(): Collection
    {
        return CartFactory::make()->items()->with(['variant', 'product.images'])->get();
    }

    #[Computed]
    public function recommendations(): Collection
    {
        $productIds = $this->items->pluck('product.id')->filter()->all();

        return Product::where('is_published', true)->whereNotIn('id', $productIds)
            ->with(['featured_image', 'variants'])->limit(3)->get();
    }

    public function addRecommendation(int $productId): void
    {
        $variant = Product::where('is_published', true)->findOrFail($productId)->variants()->firstOrFail();
        app(\App\Actions\Webshop\AddProductVariantToCart::class)->add($variant->id);
        $this->dispatch('product.added');
    }

    public function increment($itemId): void
    {
        CartFactory::make()->items()->find($itemId)?->increment('quantity');
        $this->dispatch('quantity.incremented');
    }

    public function decrement($itemId): void
    {
        $item = CartFactory::make()->items()->find($itemId);

        if($item && $item->quantity > 1)
        {
            $item->decrement('quantity');
            $this->dispatch('quantity.decremented');
        }
    }

    public function deleteItem($itemId): void
    {
        CartFactory::make()->items()->where('id', $itemId)->delete();

        $this->dispatch('product.deleted');
    }

    public function checkout()
    {
        return $this->redirectRoute('checkout.form');
    }

    public function render(): View
    {
        return view('livewire.show-cart');
    }
}
