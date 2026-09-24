<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\WebhookServer\WebhookCall;

class OrderController extends Controller
{
    /**
     * Show order creation form.
     */
    public function create()
    {
        return view('order-form');
    }

    /**
     * Store a new order and dispatch webhook.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z]+(?:\s+[A-Za-z]+)*$/',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],
        ], [
            'customer_name.required' => 'Customer name is required.',
            'customer_name.min' => 'Customer name must contain at least 2 characters.',
            'customer_name.max' => 'Customer name cannot exceed 255 characters.',
            'customer_name.regex' => 'Customer name may contain only letters and spaces.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 1.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Customer Name
        |--------------------------------------------------------------------------
        */

        $customerName = preg_replace(
            '/\s+/',
            ' ',
            trim($request->customer_name)
        );

        /*
        |--------------------------------------------------------------------------
        | Create Order
        |--------------------------------------------------------------------------
        */

        $order = Order::create([
            'customer_name' => $customerName,
            'amount' => $request->amount,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Webhook Payload
        |--------------------------------------------------------------------------
        */

        $payload = [
            'event' => 'order.created',
            'order_id' => $order->id,
            'customer_name' => $order->customer_name,
            'amount' => (float) $order->amount,
            'created_at' => $order->created_at?->toISOString(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Webhook URL
        |--------------------------------------------------------------------------
        |
        | Add WEBHOOK_URL to your .env file:
        |
        | WEBHOOK_URL=https://webhook.site/your-webhook-id
        |
        */

        $webhookUrl = config('services.webhook.url');

        /*
        |--------------------------------------------------------------------------
        | Validate Webhook URL
        |--------------------------------------------------------------------------
        */

        if (empty($webhookUrl)) {
            return back()
                ->with('error', 'Webhook URL is not configured.')
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Webhook UUID
        |--------------------------------------------------------------------------
        */

        $webhookUuid = (string) Str::uuid();

        /*
        |--------------------------------------------------------------------------
        | Create Webhook Delivery Record
        |--------------------------------------------------------------------------
        */

        $delivery = WebhookDelivery::create([
            'webhook_uuid' => $webhookUuid,
            'order_id' => $order->id,
            'event_name' => 'order.created',
            'webhook_url' => $webhookUrl,
            'status' => 'pending',
            'attempts' => 0,
            'response_status' => null,
            'error_message' => null,
            'payload' => $payload,
            'last_attempt_at' => null,
            'delivered_at' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Dispatch Webhook
        |--------------------------------------------------------------------------
        */

        try {
            WebhookCall::create()
                ->url($webhookUrl)
                ->payload($payload)
                ->useSecret(config('webhook-server.signing_secret'))
                ->dispatch();

            return redirect()
                ->route('orders.create')
                ->with(
                    'success',
                    'Order created successfully and webhook dispatched.'
                );
        } catch (\Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | Update Delivery When Dispatch Fails Immediately
            |--------------------------------------------------------------------------
            */

            $delivery->update([
                'status' => 'failed',
                'attempts' => 1,
                'error_message' => $e->getMessage(),
                'last_attempt_at' => now(),
            ]);

            return redirect()
                ->route('orders.create')
                ->with(
                    'error',
                    'Order was created, but the webhook could not be dispatched.'
                );
        }
    }
}