<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\WebhookDelivery;
use Spatie\WebhookServer\WebhookCall;

class OrderController extends Controller
{
    /**
     * Display the order creation form.
     */
    public function create()
    {
        return view('order-form');
    }

    /**
     * Handle order submission and send webhook.
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'customer_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z\s]+$/',
            ],
            'amount' => 'required|numeric|min:1',
        ], [
            'customer_name.regex' =>
                'Customer name must contain only letters and spaces.',
        ]);

        // Remove extra spaces from customer name
        $name = trim(
            preg_replace('/\s+/', ' ', $request->customer_name)
        );

        // Create order
        $order = Order::create([
            'customer_name' => $name,
            'amount' => $request->amount,
        ]);

        // Webhook payload
        $payload = [
            'event' => 'order.created',
            'order_id' => $order->id,
            'customer_name' => $order->customer_name,
            'amount' => $order->amount,
        ];

        // Webhook receiver URL
        $webhookUrl = 'https://webhook.site/541740c7-fe01-4c31-9b16-137acf62908a';

        // Create the Spatie webhook call
        $webhook = WebhookCall::create()
            ->url($webhookUrl)
            ->payload($payload)
            ->useSecret(config('webhook-server.signing_secret'));

        // Get the unique webhook UUID before dispatching
        $webhookUuid = $webhook->getUuid();

        // Create our delivery tracking record
        WebhookDelivery::create([
            'webhook_uuid' => $webhookUuid,
            'order_id' => $order->id,
            'event_name' => 'order.created',
            'webhook_url' => $webhookUrl,
            'status' => 'pending',
            'attempts' => 0,
            'payload' => $payload,
        ]);

        // Dispatch webhook to Laravel queue
        $webhook->dispatch();

        return redirect()
            ->back()
            ->with(
                'success',
                'Order Created & Webhook Queued Successfully!'
            );
    }
}