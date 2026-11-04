<?php

namespace App\Livewire;

use App\Actions\Webshop\CreateCashOnDeliveryOrder;
use App\Actions\Webshop\CreateStripeCheckoutSession;
use App\Factories\CartFactory;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Checkout extends Component
{
    public string $paymentMethod = 'card';
    public string $customerName = '';
    public string $shippingName = '';
    public string $phone = '';
    public string $addressLine1 = '';
    public string $addressLine2 = '';
    public string $city = '';
    public string $state = '';
    public string $postalCode = '';
    public string $country = '';

    public function mount(): void
    {
        $this->customerName = auth()->user()->name ?? '';
        $this->shippingName = $this->customerName;
    }

    #[Computed]
    public function cart()
    {
        return CartFactory::make();
    }

    #[Computed]
    public function items()
    {
        return $this->cart->items()->with(['product.images', 'variant'])->get();
    }

    public function submit(CreateStripeCheckoutSession $stripe, CreateCashOnDeliveryOrder $cod)
    {
        $this->validate(['paymentMethod' => 'required|in:card,cod']);

        if ($this->items->isEmpty()) {
            $this->addError('cart', 'Your cart is empty. Add a product before checking out.');
            return null;
        }

        if ($this->paymentMethod === 'card') {
            return $stripe->createFromCart($this->cart);
        }

        $this->validate([
            'customerName' => 'required|string|max:255',
            'shippingName' => 'required|string|max:255',
            'phone' => 'required|string|max:40',
            'addressLine1' => 'required|string|max:255',
            'addressLine2' => 'nullable|string|max:255',
            'city' => 'required|string|max:120',
            'state' => 'nullable|string|max:120',
            'postalCode' => 'nullable|string|max:40',
            'country' => 'required|string|max:120',
        ]);

        $order = $cod->handle(auth()->id(), [
            'customer_name' => $this->customerName,
            'shipping_name' => $this->shippingName,
            'phone' => $this->phone,
            'address_line1' => $this->addressLine1,
            'address_line2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
        ]);

        return $this->redirectRoute('order.show', ['orderId' => $order->id]);
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
