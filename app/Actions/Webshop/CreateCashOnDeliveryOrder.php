<?php

namespace App\Actions\Webshop;

use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CreateCashOnDeliveryOrder
{
    public function handle(int $userId, array $details): Order
    {
        $order = DB::transaction(function () use ($userId, $details) {
            $cart = Cart::query()->where('user_id', $userId)->lockForUpdate()->first();
            $items = $cart?->items()->with(['product', 'variant'])->get();

            if (! $items || $items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty. Add a product before checking out.']);
            }

            $total = $items->sum(fn ($item) => $item->product->unitPriceCentsForQuantity($item->quantity) * $item->quantity);
            $address = [
                'name' => $details['shipping_name'],
                'phone' => $details['phone'],
                'line1' => $details['address_line1'],
                'line2' => $details['address_line2'] ?? null,
                'city' => $details['city'],
                'state' => $details['state'] ?? null,
                'postal_code' => $details['postal_code'] ?? null,
                'country' => $details['country'],
            ];

            $order = Order::query()->create([
                'user_id' => $userId,
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'amount_shipping' => 0,
                'amount_discount' => 0,
                'amount_tax' => 0,
                'amount_subtotal' => $total,
                'amount_total' => $total,
                'billing_address' => array_merge($address, ['name' => $details['customer_name']]),
                'shipping_address' => $address,
            ]);

            foreach ($items as $item) {
                $price = $item->product->unitPriceCentsForQuantity($item->quantity);
                $order->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product->name,
                    'description' => "Size: {$item->variant->size} Color: {$item->variant->color}",
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'amount_discount' => 0,
                    'amount_tax' => 0,
                    'amount_subtotal' => $price * $item->quantity,
                    'amount_total' => $price * $item->quantity,
                ]);
            }

            $cart->items()->delete();
            $cart->delete();

            return $order;
        });

        try {
            Mail::to($order->user)->send(new OrderConfirmation($order));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $order;
    }
}
