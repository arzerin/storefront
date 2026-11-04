<?php

namespace App\Actions\Webshop;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Cashier\Checkout;

class CreateStripeCheckoutSession
{
    public function createFromCart(Cart $cart): Checkout
    {
        return $cart->user
            ->allowPromotionCodes()
            ->checkout(
            $this->formatCartItems($cart->items),
                [
                    'customer_update' => [
                        'shipping' => 'auto',
                    ],
                    'shipping_address_collection' => [
                        'allowed_countries' => [
                            'US', 'NL'
                        ]
                    ],
                    'success_url' => config('services.stripe_checkout.success_url') ?: route('checkout.status') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => config('services.stripe_checkout.cancel_url') ?: route('cart.show'),
                    'shipping_options' => config('services.stripe_checkout.shipping_rate_id')
                        ? [['shipping_rate' => config('services.stripe_checkout.shipping_rate_id')]]
                        : [],
                    'metadata' => [
                        'user_id' => $cart->user->id,
                        'cart_id' => $cart->id,
                    ]
                ]
        );
    }

    private function formatCartItems(Collection $items): array
    {
        return $items->loadMissing('product', 'variant')->map(function (CartItem $item) {
            // stripe docs line_items
            return
                [
                    'price_data' => [
                        'currency' => 'USD',
                        'unit_amount' => $item->product->price->getAmount(),
                        'product_data' => [
                            'name' => $item->product->name,
                            'description' => "Size: {$item->variant->size} Color: {$item->variant->color}",
                            'metadata' => [
                                'product_id' => $item->product->id,
                                'product_variant_id' => $item->product_variant_id,
                            ]
                        ]
                    ],
                    'quantity' => $item->quantity,
                ];
        })->toArray();
    }
}
