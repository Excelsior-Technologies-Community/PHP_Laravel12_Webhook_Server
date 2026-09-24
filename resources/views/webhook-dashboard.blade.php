<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

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
            box-shadow:
                0 8px 20px
                rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            border: none;
            border-radius: 14px;
            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .stat-number {
            font-size: 27px;
            font-weight: bold;
        }

        .stat-label {
            color: #6c757d;
            font-size: 13px;
        }

        .dashboard-card {
            border: none;
            border-radius: 14px;
            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.08);
        }

        .uuid {
            font-size: 10px;
            word-break: break-all;
        }

        .payload-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            font-size: 10px;
            max-width: 240px;
            max-height: 100px;
            overflow: auto;
        }

        .table td {
            vertical-align: middle;
        }

        .quick-filter {
            text-decoration: none;
        }

        /* Number-only pagination */
        .number-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .number-pagination .page-item {
            list-style: none;
        }

        .number-pagination .page-link {
            min-width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid #dee2e6;
            background: #fff;
            color: #667eea;
            font-weight: 600;
            padding: 0 10px;
        }

        .number-pagination .page-link:hover {
            background: #667eea;
            color: #fff;
            border-color: #667eea;
        }

        .number-pagination .page-item.active .page-link {
            background: #667eea;
            color: #fff;
            border-color: #667eea;
        }

        .number-pagination .page-item.disabled .page-link {
            color: #adb5bd;
            background: #f8f9fa;
            border-color: #dee2e6;
            cursor: default;
        }
    </style>
</head>

<body>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="dashboard-header">

        <div
            class="d-flex justify-content-between align-items-center flex-wrap gap-3"
        >

            <div>
                <h2 class="mb-1">
                    📊 Webhook Delivery Dashboard
                </h2>

                <p class="mb-0">
                    Monitor, filter, export and inspect webhook deliveries.
                </p>
            </div>

            <div class="d-flex gap-2">

                <a
                    href="{{ route('orders.create') }}"
                    class="btn btn-light"
                >
                    + Create Order
                </a>

                <a
                    href="{{ route('webhooks.export', request()->query()) }}"
                    class="btn btn-success"
                >
                    📥 Export CSV
                </a>

            </div>

        </div>

    </div>


    <!-- Messages -->

    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger">
            {{ session('error') }}
        </div>

    @endif


    <!-- Statistics -->

    <div class="row g-3 mb-4">

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
                        {{ $totalOrders }}
                    </div>

                    <div class="stat-label">
                        Total Orders
                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-2">

            <div class="card stat-card">

                <div class="card-body">

                    <div class="stat-number text-primary">
                        ₹{{ number_format($totalOrderAmount, 2) }}
                    </div>

                    <div class="stat-label">
                        Total Order Amount
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- Secondary Statistics -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <strong>
                        Total Attempts
                    </strong>

                    <h4 class="mt-2">
                        {{ $totalAttempts }}
                    </h4>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <strong>
                        Success Rate
                    </strong>

                    <h4 class="mt-2 text-success">
                        {{ $successRate }}%
                    </h4>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card dashboard-card">

                <div class="card-body">

                    <strong>
                        Average Order Amount
                    </strong>

                    <h4 class="mt-2">
                        ₹{{ number_format($averageOrderAmount ?? 0, 2) }}
                    </h4>

                </div>

            </div>

        </div>

    </div>


    <!-- Filters -->

    <div class="card dashboard-card mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('webhooks.index') }}"
            >

                <div class="row g-3">

                    <!-- Search -->

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            🔎 Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="UUID, order ID, customer, event..."
                        >

                    </div>


                    <!-- Status -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            Status
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
                                {{ $status == 'pending' ? 'selected' : '' }}
                            >
                                Pending
                            </option>

                            <option
                                value="success"
                                {{ $status == 'success' ? 'selected' : '' }}
                            >
                                Successful
                            </option>

                            <option
                                value="failed"
                                {{ $status == 'failed' ? 'selected' : '' }}
                            >
                                Failed
                            </option>

                        </select>

                    </div>


                    <!-- Per Page -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            Records Per Page
                        </label>

                        <select
                            name="per_page"
                            class="form-select"
                        >

                            @foreach([5, 10, 25, 50] as $number)

                                <option
                                    value="{{ $number }}"
                                    {{ $perPage == $number ? 'selected' : '' }}
                                >
                                    {{ $number }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <!-- Minimum Amount -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            💰 Minimum Amount
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="min_amount"
                            value="{{ $minAmount }}"
                            class="form-control"
                            placeholder="Minimum"
                        >

                    </div>


                    <!-- Maximum Amount -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            💰 Maximum Amount
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="max_amount"
                            value="{{ $maxAmount }}"
                            class="form-control"
                            placeholder="Maximum"
                        >

                    </div>


                    <!-- From Date -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            📅 From Date
                        </label>

                        <input
                            type="date"
                            name="from_date"
                            value="{{ $fromDate }}"
                            class="form-control"
                        >

                    </div>


                    <!-- To Date -->

                    <div class="col-md-3">

                        <label class="form-label fw-bold">
                            📅 To Date
                        </label>

                        <input
                            type="date"
                            name="to_date"
                            value="{{ $toDate }}"
                            class="form-control"
                        >

                    </div>


                    <!-- Sorting -->

                    <div class="col-md-4">

                        <label class="form-label fw-bold">
                            ↕️ Sort By
                        </label>

                        <select
                            name="sort"
                            class="form-select"
                        >

                            <option
                                value="latest"
                                {{ $sort == 'latest' ? 'selected' : '' }}
                            >
                                Newest First
                            </option>

                            <option
                                value="oldest"
                                {{ $sort == 'oldest' ? 'selected' : '' }}
                            >
                                Oldest First
                            </option>

                            <option
                                value="amount_high"
                                {{ $sort == 'amount_high' ? 'selected' : '' }}
                            >
                                Highest Amount
                            </option>

                            <option
                                value="amount_low"
                                {{ $sort == 'amount_low' ? 'selected' : '' }}
                            >
                                Lowest Amount
                            </option>

                            <option
                                value="attempts_high"
                                {{ $sort == 'attempts_high' ? 'selected' : '' }}
                            >
                                Most Attempts
                            </option>

                            <option
                                value="attempts_low"
                                {{ $sort == 'attempts_low' ? 'selected' : '' }}
                            >
                                Fewest Attempts
                            </option>

                        </select>

                    </div>


                    <!-- Buttons -->

                    <div class="col-md-8 d-flex align-items-end gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            🔎 Apply Filters
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


            <!-- Quick Filters -->

            <hr>

            <div>

                <strong>
                    Quick Filters:
                </strong>

                <a
                    href="{{ route('webhooks.index', ['quick_filter' => 'today']) }}"
                    class="btn btn-sm btn-outline-primary ms-2 quick-filter"
                >
                    📅 Today
                </a>

                <a
                    href="{{ route('webhooks.index', ['quick_filter' => 'month']) }}"
                    class="btn btn-sm btn-outline-primary ms-2 quick-filter"
                >
                    📅 This Month
                </a>

                <a
                    href="{{ route('webhooks.index', ['status' => 'failed']) }}"
                    class="btn btn-sm btn-outline-danger ms-2 quick-filter"
                >
                    ✕ Failed
                </a>

                <a
                    href="{{ route('webhooks.index', ['status' => 'success']) }}"
                    class="btn btn-sm btn-outline-success ms-2 quick-filter"
                >
                    ✓ Successful
                </a>

                <a
                    href="{{ route('webhooks.index', ['status' => 'pending']) }}"
                    class="btn btn-sm btn-outline-warning ms-2 quick-filter"
                >
                    ⏳ Pending
                </a>

            </div>

        </div>

    </div>


    <!-- Filter Count -->

    <div class="mb-3">

        <span class="text-muted">

            Showing

            <strong>
                {{ $filteredCount }}
            </strong>

            matching delivery record(s).

        </span>

    </div>


    <!-- Table -->

    <div class="card dashboard-card">

        <div class="card-header bg-white py-3">

            <div
                class="d-flex justify-content-between align-items-center"
            >

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
                            <th>Amount</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Response</th>
                            <th>Last Attempt</th>
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
                                #{{ $delivery->order?->id ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->order?->customer_name ?? '-' }}
                            </td>

                            <td>

                                @if($delivery->order)

                                    ₹{{ number_format(
                                        (float) $delivery->order->amount,
                                        2
                                    ) }}

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

                                    <span class="badge bg-success">
                                        ✓ Success
                                    </span>

                                @elseif($delivery->status === 'failed')

                                    <span class="badge bg-danger">
                                        ✕ Failed
                                    </span>

                                @else

                                    <span class="badge bg-warning text-dark">
                                        ⏳ Pending
                                    </span>

                                @endif

                            </td>

                            <td>
                                {{ $delivery->attempts }}
                            </td>

                            <td>

                                @if($delivery->response_status)

                                    HTTP
                                    {{ $delivery->response_status }}

                                @else

                                    -

                                @endif

                            </td>

                            <td>

                                @if($delivery->last_attempt_at)

                                    {{ $delivery->last_attempt_at->format(
                                        'd M Y, h:i:s A'
                                    ) }}

                                @else

                                    -

                                @endif

                            </td>

                            <td>

                                <div class="d-flex gap-1">

                                    <a
                                        href="{{ route(
                                            'webhooks.show',
                                            $delivery
                                        ) }}"
                                        class="btn btn-sm btn-primary"
                                    >
                                        👁️
                                    </a>


                                    @if($delivery->status === 'failed')

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'webhooks.retry',
                                                $delivery
                                            ) }}"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm(
                                                    'Retry this webhook delivery?'
                                                )"
                                            >
                                                🔁
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>


                        @if($delivery->error_message)

                            <tr>

                                <td colspan="10">

                                    <div class="alert alert-danger mb-0">

                                        <strong>
                                            Error:
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

                                <p class="text-muted">
                                    Try changing your filters.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Number-only Pagination -->

        @if($deliveries->hasPages())

            <div class="card-footer bg-white">

                <ul class="number-pagination">

                    {{-- Previous arrow only --}}
                    @if($deliveries->onFirstPage())

                        <li class="page-item disabled">
                            <span class="page-link">‹</span>
                        </li>

                    @else

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $deliveries->previousPageUrl() }}"
                            >
                                ‹
                            </a>

                        </li>

                    @endif


                    {{-- Page Numbers --}}

                    @foreach($deliveries->getUrlRange(
                        max(1, $deliveries->currentPage() - 2),
                        min(
                            $deliveries->lastPage(),
                            $deliveries->currentPage() + 2
                        )
                    ) as $page => $url)

                        <li
                            class="page-item
                            {{ $page == $deliveries->currentPage() ? 'active' : '' }}"
                        >

                            <a
                                class="page-link"
                                href="{{ $url }}"
                            >
                                {{ $page }}
                            </a>

                        </li>

                    @endforeach


                    {{-- Next arrow only --}}

                    @if($deliveries->hasMorePages())

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $deliveries->nextPageUrl() }}"
                            >
                                ›
                            </a>

                        </li>

                    @else

                        <li class="page-item disabled">

                            <span class="page-link">
                                ›
                            </span>

                        </li>

                    @endif

                </ul>

            </div>

        @endif

    </div>

</div>

</body>

</html>
