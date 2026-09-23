<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Webhook Delivery Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(
                135deg,
                #667eea,
                #764ba2
            );

            color: white;

            padding: 30px;

            border-radius: 14px;

            margin-bottom: 25px;

            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .stat-number {
            font-size: 30px;
            font-weight: bold;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }

        .dashboard-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .badge-failed {
            background: #f8d7da;
            color: #842029;
        }

        .uuid {
            font-size: 11px;
            word-break: break-all;
        }

        .payload-box {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            font-size: 11px;
            max-width: 300px;
            max-height: 120px;
            overflow: auto;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

    </style>

</head>

<body>

<div class="container-fluid py-4">

    {{-- Header --}}

    <div class="dashboard-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h2 class="mb-1">
                    📊 Webhook Delivery Dashboard
                </h2>

                <p class="mb-0">
                    Monitor webhook delivery status, attempts and failures.
                </p>

            </div>

            <a
                href="{{ route('orders.create') }}"
                class="btn btn-light"
            >
                + Create Order
            </a>

        </div>

    </div>


    {{-- Success Message --}}

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    {{-- Error Message --}}

    @if(session('error'))

        <div class="alert alert-danger">
            {{ session('error') }}
        </div>

    @endif


    {{-- Statistics --}}

    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number">
                        {{ $total }}
                    </div>

                    <div class="stat-label">
                        Total Deliveries
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number text-success">
                        {{ $successful }}
                    </div>

                    <div class="stat-label">
                        Successful
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number text-warning">
                        {{ $pending }}
                    </div>

                    <div class="stat-label">
                        Pending
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number text-danger">
                        {{ $failed }}
                    </div>

                    <div class="stat-label">
                        Failed
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number">
                        {{ $totalAttempts }}
                    </div>

                    <div class="stat-label">
                        Total Attempts
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number text-primary">
                        {{ $successRate }}%
                    </div>

                    <div class="stat-label">
                        Success Rate
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Search & Filter --}}

    <div class="card dashboard-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('webhooks.index') }}"
            >

                <div class="row g-3 align-items-end">

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Search UUID, order ID, customer, event..."
                        >

                    </div>


                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            Delivery Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Statuses
                            </option>

                            <option
                                value="pending"
                                {{ $status === 'pending' ? 'selected' : '' }}
                            >
                                Pending
                            </option>

                            <option
                                value="success"
                                {{ $status === 'success' ? 'selected' : '' }}
                            >
                                Successful
                            </option>

                            <option
                                value="failed"
                                {{ $status === 'failed' ? 'selected' : '' }}
                            >
                                Failed
                            </option>

                        </select>

                    </div>


                    <div class="col-md-3 d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🔎 Search / Filter
                        </button>

                        <a
                            href="{{ route('webhooks.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Delivery Table --}}

    <div class="card dashboard-card">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    Webhook Delivery History
                </h5>

                <span class="text-muted">
                    {{ $deliveries->total() }} record(s)
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-bordered mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Order</th>

                            <th>Customer</th>

                            <th>Event</th>

                            <th>Status</th>

                            <th>Attempts</th>

                            <th>Response</th>

                            <th>Last Attempt</th>

                            <th>Payload</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    @forelse($deliveries as $delivery)

                        <tr>

                            <td>
                                #{{ $delivery->id }}
                            </td>


                            <td>

                                @if($delivery->order)

                                    #{{ $delivery->order->id }}

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                @if($delivery->order)

                                    {{ $delivery->order->customer_name }}

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                <strong>
                                    {{ $delivery->event_name }}
                                </strong>

                                <div class="uuid mt-1">
                                    {{ $delivery->webhook_uuid }}
                                </div>

                            </td>


                            <td>

                                @if($delivery->status === 'success')

                                    <span class="badge badge-success">
                                        ✓ Success
                                    </span>

                                @elseif($delivery->status === 'failed')

                                    <span class="badge badge-failed">
                                        ✕ Failed
                                    </span>

                                @else

                                    <span class="badge badge-pending">
                                        ⏳ Pending
                                    </span>

                                @endif

                            </td>


                            <td>
                                {{ $delivery->attempts }}
                            </td>


                            <td>

                                @if($delivery->response_status)

                                    <span class="badge bg-secondary">
                                        HTTP {{ $delivery->response_status }}
                                    </span>

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                @if($delivery->last_attempt_at)

                                    {{ $delivery->last_attempt_at->format('d M Y, h:i:s A') }}

                                @else

                                    -

                                @endif

                            </td>


                            <td>

                                <div class="payload-box">

                                    <pre class="mb-0">{{ json_encode($delivery->payload, JSON_PRETTY_PRINT) }}</pre>

                                </div>

                            </td>


                            <td>

                                @if($delivery->status === 'failed')

                                    <form
                                        method="POST"
                                        action="{{ route('webhooks.retry', $delivery) }}"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Retry this webhook delivery?')"
                                        >
                                            🔁 Retry
                                        </button>

                                    </form>

                                @else

                                    <span class="text-muted">
                                        No action
                                    </span>

                                @endif

                            </td>

                        </tr>


                        @if($delivery->error_message)

                            <tr>

                                <td colspan="10">

                                    <div class="alert alert-danger mb-0">

                                        <strong>
                                            Delivery Error:
                                        </strong>

                                        {{ $delivery->error_message }}

                                    </div>

                                </td>

                            </tr>

                        @endif

                    @empty

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5"
                            >

                                <h5>
                                    No webhook deliveries found
                                </h5>

                                <p class="text-muted mb-0">
                                    Create an order to generate a webhook delivery.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}

        @if($deliveries->hasPages())

            <div class="card-footer bg-white">

                {{ $deliveries->links() }}

            </div>

        @endif

    </div>

</div>

</body>

</html>