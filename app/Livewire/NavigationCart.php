<?php

namespace App\Livewire;

use Livewire\Attributes\{ Computed, On };
use App\Factories\CartFactory;
use Illuminate\View\View;
use Livewire\Component;

class NavigationCart extends Component
{
    public $isActive = false;

    public function mount(): void
    {
        $this->isActive = request()->routeIs('cart.show');
    }

    #[Computed]
    #[On('product.added')]
    #[On('product.deleted')]
    #[On('quantity.incremented')]
    #[On('quantity.decremented')]
    public function count()
    {
        return CartFactory::make()->items()->sum('quantity');
    }

    public function render(): View
    {
        return view('livewire.navigation-cart');
    }
}
