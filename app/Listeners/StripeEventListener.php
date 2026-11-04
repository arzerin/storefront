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
        if($event->payload['type'] === 'checkout.session.completed') {
            (new HandleCheckoutSessionCompleted())->handle($event->payload['data']['object']['id']);
        }
    }
}
