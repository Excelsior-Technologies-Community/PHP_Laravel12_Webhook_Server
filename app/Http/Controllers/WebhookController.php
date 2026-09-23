<?php

namespace App\Http\Controllers;

use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookServer\WebhookCall;

class WebhookController extends Controller
{
    /**
     * Display webhook monitoring dashboard.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = WebhookDelivery::with('order')
            ->latest();

        /*
         * Search by:
         * - webhook UUID
         * - event name
         * - webhook URL
         * - customer name
         * - order ID
         */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('webhook_uuid', 'like', "%{$search}%")
                    ->orWhere('event_name', 'like', "%{$search}%")
                    ->orWhere('webhook_url', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where(
                            'customer_name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        /*
         * Filter by webhook status.
         */
        if ($status && in_array(
            $status,
            ['pending', 'success', 'failed'],
            true
        )) {
            $query->where('status', $status);
        }

        $deliveries = $query
            ->paginate(10)
            ->withQueryString();

        /*
         * Dashboard statistics.
         */
        $total = WebhookDelivery::count();

        $successful = WebhookDelivery::where(
            'status',
            'success'
        )->count();

        $pending = WebhookDelivery::where(
            'status',
            'pending'
        )->count();

        $failed = WebhookDelivery::where(
            'status',
            'failed'
        )->count();

        $totalAttempts = WebhookDelivery::sum('attempts');

        $successRate = $total > 0
            ? round(($successful / $total) * 100, 2)
            : 0;

        return view('webhook-dashboard', compact(
            'deliveries',
            'search',
            'status',
            'total',
            'successful',
            'pending',
            'failed',
            'totalAttempts',
            'successRate'
        ));
    }

    /**
     * Retry a failed webhook delivery.
     */
    public function retry(WebhookDelivery $delivery)
    {
        if ($delivery->status !== 'failed') {
            return redirect()
                ->route('webhooks.index')
                ->with(
                    'error',
                    'Only failed webhook deliveries can be retried.'
                );
        }

        $payload = $delivery->payload;

        if (!is_array($payload)) {
            return redirect()
                ->route('webhooks.index')
                ->with(
                    'error',
                    'Webhook payload is unavailable for retry.'
                );
        }

        /*
         * Create a completely new webhook call.
         *
         * This gives the retry its own UUID and delivery lifecycle.
         */
        $webhook = WebhookCall::create()
            ->url($delivery->webhook_url)
            ->payload($payload)
            ->useSecret(config('webhook-server.signing_secret'));

        $newUuid = $webhook->getUuid();

        /*
         * Create a new tracking record for the retry.
         */
        WebhookDelivery::create([
            'webhook_uuid' => $newUuid,
            'order_id' => $delivery->order_id,
            'event_name' => $delivery->event_name,
            'webhook_url' => $delivery->webhook_url,
            'status' => 'pending',
            'attempts' => 0,
            'payload' => $payload,
        ]);

        /*
         * Dispatch the retry through the same
         * Spatie webhook queue mechanism.
         */
        $webhook->dispatch();

        return redirect()
            ->route('webhooks.index')
            ->with(
                'success',
                'Webhook retry has been queued successfully.'
            );
    }
}