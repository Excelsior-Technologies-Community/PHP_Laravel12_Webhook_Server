<?php

namespace App\Listeners;

use App\Models\WebhookDelivery;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;

class FinalWebhookCallFailedListener
{
    public function handle(
        FinalWebhookCallFailedEvent $event
    ): void {

        $delivery = WebhookDelivery::where(
            'webhook_uuid',
            $event->uuid
        )->first();

        if (! $delivery) {
            return;
        }

        $delivery->update([
            'status' => 'failed',

            'attempts' => $event->attempt,

            'response_status' =>
                $event->response?->getStatusCode(),

            'last_attempt_at' => now(),

            'error_message' =>
                $event->response
                    ? 'Final webhook attempt returned HTTP ' .
                      $event->response->getStatusCode()
                    : 'Final webhook attempt failed or timed out.',
        ]);
    }
}