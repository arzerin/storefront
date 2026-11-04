<?php

namespace App\Actions\Webshop;

use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;

class HandleCheckoutSessionCompleted
{
    public function handle(string $sessionId, ?int $expectedUserId = null): ?Order
    {
        $stripe = Cashier::stripe();
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        $userId = (int) ($session->metadata->user_id ?? 0);
        $cartId = (int) ($session->metadata->cart_id ?? 0);

        if ($session->status !== 'complete'
            || ! in_array($session->payment_status, ['paid', 'no_payment_required'], true)
            || $userId < 1
            || ($expectedUserId !== null && $userId !== $expectedUserId)) {
            return null;
        }

        $lineItems = $stripe->checkout->sessions->allLineItems($sessionId, ['limit' => 100]);
        $items = collect($lineItems->data)->map(function ($line) use ($stripe) {
            $product = $stripe->products->retrieve($line->price->product);

            return new OrderItem([
                'product_variant_id' => $product->metadata->product_variant_id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $line->price->unit_amount,
                'quantity' => $line->quantity,
                'amount_discount' => $line->amount_discount,
                'amount_tax' => $line->amount_tax,
                'amount_subtotal' => $line->amount_subtotal,
                'amount_total' => $line->amount_total,
            ]);
        });

        [$order, $created] = DB::transaction(function () use ($session, $sessionId, $userId, $cartId, $items) {
            // Lock the shopper so a webhook and a browser return cannot create two orders.
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $existing = $user->orders()->where('stripe_checkout_session_id', $sessionId)->first();
            if ($existing) {
                return [$existing, false];
            }

            $order = $user->orders()->create([
                'stripe_checkout_session_id' => $sessionId,
                'payment_method' => 'card',
                'payment_status' => 'paid',
                'order_status' => 'pending',
                'amount_shipping' => $session->total_details->amount_shipping ?? 0,
                'amount_discount' => $session->total_details->amount_discount ?? 0,
                'amount_tax' => $session->total_details->amount_tax ?? 0,
                'amount_subtotal' => $session->amount_subtotal ?? 0,
                'amount_total' => $session->amount_total ?? 0,
                'billing_address' => $this->address($session->customer_details),
                'shipping_address' => $this->address($session->shipping_details),
            ]);
            $order->items()->saveMany($items);

            $cart = Cart::query()->whereKey($cartId)->where('user_id', $userId)->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }

            return [$order, true];
        });

        if ($created) {
            try {
                Mail::to($order->user)->send(new OrderConfirmation($order));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $order;
    }

    private function address($details): array
    {
        return [
            'name' => $details->name ?? null,
            'line1' => $details->address->line1 ?? null,
            'line2' => $details->address->line2 ?? null,
            'city' => $details->address->city ?? null,
            'state' => $details->address->state ?? null,
            'postal_code' => $details->address->postal_code ?? null,
            'country' => $details->address->country ?? null,
        ];
    }
}
