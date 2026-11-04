<?php

namespace App\Livewire;

use App\Actions\Webshop\AddProductVariantToCart;
use Illuminate\Contracts\View\View;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use App\Models\Product;

class ShowProduct extends Component
{
    use InteractsWithBanner;

    public Product $product;
    public $variant;

    public $rules = [
        'variant' => ['required', 'exists:App\Models\ProductVariant,id'],
    ];

    public function addToCart(AddProductVariantToCart $action): void
    {
        $this->validate();

        $action->add(variantId: $this->variant);

        $this->banner('Product added to cart successfully!');

        $this->dispatch('product.added');
    }

    public function render(): View
    {
        return view('livewire.product');
    }
}
