<?php

namespace App\Livewire;

use App\Actions\Webshop\CreateStripeCheckoutSession;
use App\Factories\CartFactory;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Laravel\Cashier\Checkout;
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
        return CartFactory::make()->items()->with(['variant', 'product'])->get();
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

    public function checkout(CreateStripeCheckoutSession $checkoutSession): Checkout
    {
        return $checkoutSession->createFromCart($this->cart);
    }

    public function render(): View
    {
        return view('livewire.show-cart');
    }
}
