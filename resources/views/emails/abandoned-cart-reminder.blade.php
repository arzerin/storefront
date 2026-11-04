@component('mail::message')

Hey {{ $cart->user->name }},

You still have items in your cart. Click the button below to continue checkout.



@component('mail::button', ['url' => route('cart.show')])
    Continue Checkout
@endcomponent

@endcomponent
