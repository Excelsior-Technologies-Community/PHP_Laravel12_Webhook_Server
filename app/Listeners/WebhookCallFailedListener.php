<?php

namespace App\Listeners;

use App\Models\WebhookDelivery;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;

class WebhookCallFailedListener
{
    public function handle(
        WebhookCallFailedEvent $event
    ): void {

        $delivery = WebhookDelivery::where(
            'webhook_uuid',
            $event->uuid
        )->first();

        if (! $delivery) {
            return;
        }

        $delivery->update([
            'status' => 'pending',

            'attempts' => $event->attempt,

            'response_status' =>
                $event->response?->getStatusCode(),

            'last_attempt_at' => now(),

            'error_message' =>
                $event->response
                    ? 'Webhook returned HTTP ' .
                      $event->response->getStatusCode()
                    : 'Webhook request failed or timed out.',
        ]);
    }
}