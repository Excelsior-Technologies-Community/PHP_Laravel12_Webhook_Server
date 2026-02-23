<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Spatie\WebhookServer\WebhookCall;

class OrderController extends Controller
{
    // Display the order creation form
    public function create()
    {
        return view('order-form');
    }

    // Handle order submission and send webhook
    public function store(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'customer_name' => [
                'required',      // Name is required
                'string',        // Must be a string
                'min:2',         // Minimum 2 characters
                'max:255',       // Maximum length
                'regex:/^[A-Za-z\s]+$/' // Allow only letters and spaces
            ],
            'amount' => 'required|numeric|min:1' // Amount must be numeric and greater than 0
        ], [
            'customer_name.regex' =>
                'Customer name must contain only letters and spaces.'
        ]);

        // Remove extra spaces from customer name
        $name = trim(preg_replace('/\s+/', ' ', $request->customer_name));

        // Create a new order record in database
        $order = Order::create([
            'customer_name' => $name,
            'amount' => $request->amount,
        ]);

        // Send webhook notification after order creation
        WebhookCall::create()
            ->url('https://webhook.site/12fe3aae-dcc6-468f-bf90-e6183dbaafbf') // Webhook receiver URL
            ->payload([
                'event' => 'order.created',           // Event name
                'order_id' => $order->id,             // Order ID
                'customer_name' => $order->customer_name, // Customer name
                'amount' => $order->amount,           // Order amount
            ])
            ->useSecret(config('webhook-server.signing_secret')) // Add signature security
            ->dispatch(); // Dispatch webhook job to queue

        // Redirect back with success message
        return redirect()->back()
            ->with('success', 'Order Created & Webhook Sent!');
    }
}