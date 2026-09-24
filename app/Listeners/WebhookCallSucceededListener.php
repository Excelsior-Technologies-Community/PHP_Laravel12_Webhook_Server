<?php

namespace App\Listeners;

use App\Models\WebhookDelivery;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

class WebhookCallSucceededListener
{
    public function handle(
        WebhookCallSucceededEvent $event
    ): void {

        $delivery = WebhookDelivery::where(
            'webhook_uuid',
            $event->uuid
        )->first();

        if (! $delivery) {
            return;
        }

        $delivery->update([
            'status' => 'success',

            'attempts' => $event->attempt,

            'response_status' =>
                $event->response?->getStatusCode(),

            'error_message' => null,

            'last_attempt_at' => now(),

            'delivered_at' => now(),
        ]);
    }
}