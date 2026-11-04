<?php

namespace App\Listeners;

use App\Actions\Webshop\HandleCheckoutSessionCompleted;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Exception\ApiErrorException;

class StripeEventListener
{
    /**
     * Handle the event.
     * @throws ApiErrorException
     */
    public function handle(WebhookReceived $event): void
    {
        if(in_array($event->payload['type'], ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            (new HandleCheckoutSessionCompleted())->handle($event->payload['data']['object']['id']);
        }
    }
}
