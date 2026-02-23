<?php

return [

    'queue' => 'default',
    'connection' => 'database',

    'signing_secret' => env('WEBHOOK_SECRET'),

    'http_verb' => 'post',

    'proxy' => null,

    'signer' => \Spatie\WebhookServer\Signer\DefaultSigner::class,

    'signature_header_name' => 'Signature',
    'timestamp_header_name' => 'Timestamp',

    'headers' => [
        'Content-Type' => 'application/json',
        'X-App' => 'LaravelWebhookServer',
    ],

    'timeout_in_seconds' => 10,

    'tries' => 5,

    'backoff_strategy' =>
        \Spatie\WebhookServer\BackoffStrategy\ExponentialBackoffStrategy::class,

    'webhook_job' =>
        \Spatie\WebhookServer\CallWebhookJob::class,

    'verify_ssl' => true,

    'throw_exception_on_failure' => true,

    'tags' => ['webhook'],
];