<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\WebhookServer\WebhookCall;

class WebhookController extends Controller
{
    /**
     * Display webhook monitoring dashboard.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        $minAmount = $request->input('min_amount');
        $maxAmount = $request->input('max_amount');

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $quickFilter = $request->input('quick_filter');

        $sort = $request->input('sort', 'oldest');

        $perPage = (int) $request->input('per_page', 5);

        /*
        |--------------------------------------------------------------------------
        | Validate per-page value
        |--------------------------------------------------------------------------
        */

        if (! in_array($perPage, [5, 10, 25, 50], true)) {
            $perPage = 5;
        }

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = WebhookDelivery::with('order');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($q) use ($search) {

                $q->where('webhook_uuid', 'like', "%{$search}%")
                    ->orWhere('event_name', 'like', "%{$search}%")
                    ->orWhere('webhook_url', 'like', "%{$search}%")
                    ->orWhere('order_id', 'like', "%{$search}%")
                    ->orWhere('response_status', 'like', "%{$search}%");

                $q->orWhereHas('order', function ($orderQuery) use ($search) {

                    $orderQuery->where(
                        'customer_name',
                        'like',
                        "%{$search}%"
                    );
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if (
            $status &&
            in_array(
                $status,
                ['pending', 'success', 'failed'],
                true
            )
        ) {
            $query->where('status', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Amount
        |--------------------------------------------------------------------------
        */

        if ($minAmount !== null && $minAmount !== '') {

            $query->whereHas('order', function ($q) use ($minAmount) {

                $q->where('amount', '>=', $minAmount);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Amount
        |--------------------------------------------------------------------------
        */

        if ($maxAmount !== null && $maxAmount !== '') {

            $query->whereHas('order', function ($q) use ($maxAmount) {

                $q->where('amount', '<=', $maxAmount);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */

        if ($fromDate) {

            $query->whereDate(
                'created_at',
                '>=',
                $fromDate
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($toDate) {

            $query->whereDate(
                'created_at',
                '<=',
                $toDate
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Quick Date Filters
        |--------------------------------------------------------------------------
        */

        if ($quickFilter === 'today') {

            $query->whereDate(
                'created_at',
                now()->toDateString()
            );
        }

        if ($quickFilter === 'month') {

            $query->whereMonth(
                'created_at',
                now()->month
            )->whereYear(
                'created_at',
                now()->year
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        switch ($sort) {

            case 'oldest':

                $query->orderBy(
                    'created_at',
                    'asc'
                );

                break;

            case 'attempts_high':

                $query->orderBy(
                    'attempts',
                    'desc'
                );

                break;

            case 'attempts_low':

                $query->orderBy(
                    'attempts',
                    'asc'
                );

                break;

            case 'amount_high':

                $query->orderByDesc(
                    Order::select('amount')
                        ->whereColumn(
                            'orders.id',
                            'webhook_deliveries.order_id'
                        )
                );

                break;

            case 'amount_low':

                $query->orderBy(
                    Order::select('amount')
                        ->whereColumn(
                            'orders.id',
                            'webhook_deliveries.order_id'
                        )
                );

                break;

            case 'latest':
            default:

                $query->orderBy(
                    'created_at',
                    'desc'
                );

                break;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $deliveries = $query
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Webhook Statistics
        |--------------------------------------------------------------------------
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

        $totalAttempts = WebhookDelivery::sum(
            'attempts'
        );

        $successRate = $total > 0
            ? round(
                ($successful / $total) * 100,
                2
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Order Statistics
        |--------------------------------------------------------------------------
        */

        $totalOrders = Order::count();

        $totalOrderAmount = Order::sum(
            'amount'
        );

        $averageOrderAmount = Order::avg(
            'amount'
        );

        /*
        |--------------------------------------------------------------------------
        | Filtered Result Count
        |--------------------------------------------------------------------------
        */

        $filteredCount = $deliveries->total();

        return view(
            'webhook-dashboard',
            compact(
                'deliveries',
                'search',
                'status',
                'minAmount',
                'maxAmount',
                'fromDate',
                'toDate',
                'quickFilter',
                'sort',
                'perPage',
                'total',
                'successful',
                'pending',
                'failed',
                'totalAttempts',
                'successRate',
                'totalOrders',
                'totalOrderAmount',
                'averageOrderAmount',
                'filteredCount'
            )
        );
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

        if (! is_array($payload)) {

            return redirect()
                ->route('webhooks.index')
                ->with(
                    'error',
                    'Webhook payload is unavailable for retry.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate retry while another retry is pending
        |--------------------------------------------------------------------------
        */

        $existingPending = WebhookDelivery::where(
            'order_id',
            $delivery->order_id
        )
            ->where(
                'event_name',
                $delivery->event_name
            )
            ->where(
                'status',
                'pending'
            )
            ->exists();

        if ($existingPending) {

            return redirect()
                ->route('webhooks.index')
                ->with(
                    'error',
                    'A retry for this webhook is already pending.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Create new webhook
        |--------------------------------------------------------------------------
        */

        $webhook = WebhookCall::create()
            ->url($delivery->webhook_url)
            ->payload($payload)
            ->useSecret(
                config(
                    'webhook-server.signing_secret'
                )
            );

        $newUuid = $webhook->getUuid();

        /*
        |--------------------------------------------------------------------------
        | Track retry
        |--------------------------------------------------------------------------
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

        $webhook->dispatch();

        return redirect()
            ->route('webhooks.index')
            ->with(
                'success',
                'Webhook retry has been queued successfully.'
            );
    }

    /**
     * Display a single webhook delivery.
     */
    public function show(WebhookDelivery $delivery)
    {
        $delivery->load('order');

        return view(
            'webhook-details',
            compact('delivery')
        );
    }

    /**
     * Export webhook deliveries as CSV.
     */
    public function export(Request $request)
    {
        $query = WebhookDelivery::with('order')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input('search')
        );

        if ($search !== '') {

            $query->where(function ($q) use ($search) {

                $q->where(
                    'webhook_uuid',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'event_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'webhook_url',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'order_id',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'order',
                        function ($orderQuery) use ($search) {

                            $orderQuery->where(
                                'customer_name',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $request->status &&
            in_array(
                $request->status,
                ['pending', 'success', 'failed'],
                true
            )
        ) {

            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Amount filters
        |--------------------------------------------------------------------------
        */

        if (
            $request->min_amount !== null &&
            $request->min_amount !== ''
        ) {

            $query->whereHas(
                'order',
                function ($q) use ($request) {

                    $q->where(
                        'amount',
                        '>=',
                        $request->min_amount
                    );
                }
            );
        }

        if (
            $request->max_amount !== null &&
            $request->max_amount !== ''
        ) {

            $query->whereHas(
                'order',
                function ($q) use ($request) {

                    $q->where(
                        'amount',
                        '<=',
                        $request->max_amount
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date filters
        |--------------------------------------------------------------------------
        */

        if ($request->from_date) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->to_date) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        if ($request->quick_filter === 'today') {

            $query->whereDate(
                'created_at',
                now()->toDateString()
            );
        }

        if ($request->quick_filter === 'month') {

            $query->whereMonth(
                'created_at',
                now()->month
            )->whereYear(
                'created_at',
                now()->year
            );
        }

        $deliveries = $query->get();

        /*
        |--------------------------------------------------------------------------
        | CSV
        |--------------------------------------------------------------------------
        */

        $filename =
            'webhook-deliveries-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        $headers = [
            'Content-Type' =>
                'text/csv; charset=UTF-8',

            'Content-Disposition' =>
                'attachment; filename="' .
                $filename .
                '"',
        ];

        $callback = function () use ($deliveries) {

            $file = fopen(
                'php://output',
                'w'
            );

            fputcsv($file, [
                'ID',
                'Order ID',
                'Customer',
                'Amount',
                'Webhook UUID',
                'Event',
                'Webhook URL',
                'Status',
                'Attempts',
                'Response Status',
                'Last Attempt',
                'Delivered At',
                'Created At',
            ]);

            foreach ($deliveries as $delivery) {

                fputcsv($file, [
                    $delivery->id,
                    $delivery->order_id,
                    $delivery->order?->customer_name,
                    $delivery->order?->amount,
                    $delivery->webhook_uuid,
                    $delivery->event_name,
                    $delivery->webhook_url,
                    $delivery->status,
                    $delivery->attempts,
                    $delivery->response_status,
                    optional(
                        $delivery->last_attempt_at
                    )->format('Y-m-d H:i:s'),
                    optional(
                        $delivery->delivered_at
                    )->format('Y-m-d H:i:s'),
                    optional(
                        $delivery->created_at
                    )->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream(
            $callback,
            200,
            $headers
        );
    }
}