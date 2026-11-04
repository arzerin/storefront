<?php

namespace App\Factories;

use App\Models\Cart;

class CartFactory
{
    public static function make(): Cart
    {
        return match(auth('web')->guest()) {
            true => Cart::firstOrCreate(['session_id' => session()->getId()]),
            false => auth('web')->user()->cart ?: auth('web')->user()->cart()->create(),
        };
    }
}
