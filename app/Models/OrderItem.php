<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;


class OrderItem extends Model
{
    protected $guarded = [];

    public function casts(): array
    {
        return [
            'price' => MoneyCast::class,
            'amount_tax' => MoneyCast::class,
            'amount_discount' => MoneyCast::class,
            'amount_subtotal' => MoneyCast::class,
            'amount_total' => MoneyCast::class,
        ];
    }
}
