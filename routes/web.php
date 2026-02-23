<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

// Route to display the order creation form
Route::get('/order-form', [OrderController::class, 'create']);

// Route to handle order form submission and store order data
Route::post('/orders', [OrderController::class, 'store'])
        ->name('orders.store'); 