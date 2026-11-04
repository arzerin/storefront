<?php

namespace App\Actions\Webshop;

use App\Models\Cart;

class MigrateSessionCart
{
    public function migrate(Cart $sessionCart, Cart $userCart): void
    {
        $sessionCart->items->each(fn($item) => (new AddProductVariantToCart())->add(
            variantId: $item->product_variant_id,
            quantity: $item->quantity,
            cart: $userCart,
        ));

        // cleanup after migrate
        $sessionCart->items()->delete();
        $sessionCart->delete();
    }
}
