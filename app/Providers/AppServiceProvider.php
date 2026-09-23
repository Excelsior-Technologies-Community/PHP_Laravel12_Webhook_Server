<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Models\WebhookDelivery;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | Webhook Successfully Delivered
        |--------------------------------------------------------------------------
        */

        Event::listen(
            WebhookCallSucceededEvent::class,
            function (WebhookCallSucceededEvent $event): void {
                $delivery = WebhookDelivery::where(
                    'webhook_uuid',
                    $event->uuid
                )->first();

                if (!$delivery) {
                    return;
                }

                $delivery->update([
                    'status' => 'success',
                    'attempts' => $event->attempt,
                    'response_status' => $event->response?->getStatusCode(),
                    'error_message' => null,
                    'last_attempt_at' => now(),
                    'delivered_at' => now(),
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Webhook Attempt Failed - Will Retry
        |--------------------------------------------------------------------------
        */

        Event::listen(
            WebhookCallFailedEvent::class,
            function (WebhookCallFailedEvent $event): void {
                $delivery = WebhookDelivery::where(
                    'webhook_uuid',
                    $event->uuid
                )->first();

                if (!$delivery) {
                    return;
                }

                $delivery->update([
                    'status' => 'pending',
                    'attempts' => $event->attempt,
                    'response_status' => $event->response?->getStatusCode(),
                    'error_message' => $event->errorMessage,
                    'last_attempt_at' => now(),
                ]);
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Webhook Permanently Failed
        |--------------------------------------------------------------------------
        */

        Event::listen(
            FinalWebhookCallFailedEvent::class,
            function (FinalWebhookCallFailedEvent $event): void {
                $delivery = WebhookDelivery::where(
                    'webhook_uuid',
                    $event->uuid
                )->first();

                if (!$delivery) {
                    return;
                }

                $delivery->update([
                    'status' => 'failed',
                    'attempts' => $event->attempt,
                    'response_status' => $event->response?->getStatusCode(),
                    'error_message' => $event->errorMessage
                        ?: 'Webhook delivery failed after all retry attempts.',
                    'last_attempt_at' => now(),
                ]);
            }
        );
    }
}