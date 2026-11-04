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
        $successUrl = config('services.stripe_checkout.success_url') ?: route('checkout.success');
        $successUrl .= (str_contains($successUrl, '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}';

        return $cart->user->checkout(
            $this->formatCartItems($cart->items),
                [
                    'allow_promotion_codes' => true,
                    'customer_update' => [
                        'shipping' => 'auto',
                    ],
                    'shipping_address_collection' => [
                        'allowed_countries' => [
                            'US', 'NL'
                        ]
                    ],
                    'success_url' => $successUrl,
                    'cancel_url' => config('services.stripe_checkout.cancel_url') ?: route('checkout.cancel'),
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
                        'unit_amount' => $item->product->unitPriceCentsForQuantity($item->quantity),
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
