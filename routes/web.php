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
| Webhook Monitoring Routes
|--------------------------------------------------------------------------
*/

Route::get('/webhooks', [
    WebhookController::class,
    'index',
])->name('webhooks.index');

Route::post('/webhooks/{delivery}/retry', [
    WebhookController::class,
    'retry',
])->name('webhooks.retry');