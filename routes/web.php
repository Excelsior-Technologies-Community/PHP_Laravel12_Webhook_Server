<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Order Routes
|--------------------------------------------------------------------------
*/

Route::get('/order-form', [
    OrderController::class,
    'create',
])->name('orders.create');

Route::post('/orders', [
    OrderController::class,
    'store',
])->name('orders.store');


/*
|--------------------------------------------------------------------------
| Webhook Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/webhooks', [
    WebhookController::class,
    'index',
])->name('webhooks.index');


/*
|--------------------------------------------------------------------------
| Webhook Details
|--------------------------------------------------------------------------
*/

Route::get('/webhooks/{delivery}', [
    WebhookController::class,
    'show',
])->name('webhooks.show');


/*
|--------------------------------------------------------------------------
| Webhook Retry
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/{delivery}/retry', [
    WebhookController::class,
    'retry',
])->name('webhooks.retry');


/*
|--------------------------------------------------------------------------
| Webhook CSV Export
|--------------------------------------------------------------------------
*/

Route::get('/webhooks-export', [
    WebhookController::class,
    'export',
])->name('webhooks.export');