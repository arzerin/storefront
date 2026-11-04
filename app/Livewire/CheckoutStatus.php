<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CheckoutStatus extends Component
{
    public $sessionId;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount(): void
    {
        $this->sessionId = request()->get('session_id');
    }

    #[Computed]
    public function order()
    {
        return auth()->user()->orders()->where('stripe_checkout_session_id', $this->sessionId)->firstOrFail();
    }

    public function render(): View
    {
        return view('livewire.checkout-status');
    }
}
