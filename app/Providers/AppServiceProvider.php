<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;

use App\Listeners\WebhookCallSucceededListener;
use App\Listeners\WebhookCallFailedListener;
use App\Listeners\FinalWebhookCallFailedListener;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(
            WebhookCallSucceededEvent::class,
            WebhookCallSucceededListener::class
        );

        Event::listen(
            WebhookCallFailedEvent::class,
            WebhookCallFailedListener::class
        );

        Event::listen(
            FinalWebhookCallFailedEvent::class,
            FinalWebhookCallFailedListener::class
        );
    }
}