<?php

namespace App\Actions\Webshop;

use App\Factories\CartFactory;

class AddProductVariantToCart
{
    public function add($variantId, $quantity = 1, $cart = null): void
    {
        $item = ($cart ?: CartFactory::make())->items()->firstOrCreate(
            [
                'product_variant_id' => $variantId,
            ],
            [
                'quantity' => 0,
            ]
        );

        $item->increment('quantity', $quantity);
        $item->touch();
    }
}
