<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowOrder extends Component
{
    public $orderId;

    public function mount($orderId): void
    {
        $this->orderId = $orderId;
    }

    #[Computed]
    public function order()
    {
        return auth()->user()->orders()->findOrFail($this->orderId);
    }

    public function render(): View
    {
        return view('livewire.show-order');
    }
}
