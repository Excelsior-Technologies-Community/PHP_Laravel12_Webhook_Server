<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Webhook Details</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .header {
            background: linear-gradient(
                135deg,
                #667eea,
                #764ba2
            );

            color: white;

            padding: 30px;

            border-radius: 14px;

            margin-bottom: 25px;
        }

        .card {
            border: none;

            border-radius: 14px;

            box-shadow:
                0 5px 15px
                rgba(0, 0, 0, 0.08);
        }

        pre {
            white-space: pre-wrap;

            word-break: break-word;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <div class="header">

        <div
            class="d-flex justify-content-between align-items-center"
        >

            <div>

                <h2>
                    👁️ Webhook Delivery Details
                </h2>

                <p class="mb-0">
                    Delivery #{{ $delivery->id }}
                </p>

            </div>

            <a
                href="{{ route('webhooks.index') }}"
                class="btn btn-light"
            >
                ← Back
            </a>

        </div>

    </div>


    <div class="row g-4">

        <!-- Basic Information -->

        <div class="col-md-6">

            <div class="card">

                <div class="card-body">

                    <h5 class="mb-4">
                        📋 Delivery Information
                    </h5>


                    <table class="table">

                        <tr>

                            <th>
                                ID
                            </th>

                            <td>
                                #{{ $delivery->id }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Order
                            </th>

                            <td>
                                #{{ $delivery->order?->id ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Customer
                            </th>

                            <td>
                                {{ $delivery->order?->customer_name ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Amount
                            </th>

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

                        </tr>


                        <tr>

                            <th>
                                Event
                            </th>

                            <td>
                                {{ $delivery->event_name }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Status
                            </th>

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

                        </tr>


                        <tr>

                            <th>
                                Attempts
                            </th>

                            <td>
                                {{ $delivery->attempts }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Response
                            </th>

                            <td>
                                {{ $delivery->response_status ?? '-' }}
                            </td>

                        </tr>


                        <tr>

                            <th>
                                Created
                            </th>

                            <td>

                                {{ $delivery->created_at?->format(
                                    'd M Y, h:i:s A'
                                ) }}

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>


        <!-- Webhook Information -->

        <div class="col-md-6">

            <div class="card">

                <div class="card-body">

                    <h5 class="mb-4">
                        🌐 Webhook Information
                    </h5>


                    <p>

                        <strong>
                            UUID
                        </strong>

                    </p>

                    <div class="alert alert-secondary">

                        {{ $delivery->webhook_uuid }}

                    </div>


                    <p>

                        <strong>
                            Webhook URL
                        </strong>

                    </p>

                    <div class="alert alert-secondary">

                        {{ $delivery->webhook_url }}

                    </div>


                    <p>

                        <strong>
                            Last Attempt
                        </strong>

                    </p>

                    <div>

                        {{ $delivery->last_attempt_at?->format(
                            'd M Y, h:i:s A'
                        ) ?? '-' }}

                    </div>


                    <p class="mt-3">

                        <strong>
                            Delivered At
                        </strong>

                    </p>

                    <div>

                        {{ $delivery->delivered_at?->format(
                            'd M Y, h:i:s A'
                        ) ?? '-' }}

                    </div>

                </div>

            </div>

        </div>


        <!-- Payload -->

        <div class="col-12">

            <div class="card">

                <div class="card-body">

                    <h5>
                        📦 Webhook Payload
                    </h5>

                    <pre class="bg-light p-3 rounded">{{ json_encode(
                        $delivery->payload,
                        JSON_PRETTY_PRINT
                    ) }}</pre>

                </div>

            </div>

        </div>


        <!-- Error -->

        @if($delivery->error_message)

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <h5 class="text-danger">
                            ❌ Error Message
                        </h5>

                        <div class="alert alert-danger">

                            {{ $delivery->error_message }}

                        </div>

                    </div>

                </div>

            </div>

        @endif


        <!-- Retry -->

        @if($delivery->status === 'failed')

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

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
                                class="btn btn-danger"
                                onclick="return confirm(
                                    'Retry this webhook delivery?'
                                )"
                            >
                                🔁 Retry Webhook
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

</body>

</html>