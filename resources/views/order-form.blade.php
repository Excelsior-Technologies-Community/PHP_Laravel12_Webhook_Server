<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Order</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(
                135deg,
                #667eea,
                #764ba2
            );

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            margin: 0;
        }

        .card {
            background: #fff;

            padding: 30px;

            width: 380px;

            border-radius: 12px;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;

            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input {
            width: 100%;

            padding: 10px;

            margin-top: 5px;

            margin-bottom: 10px;

            border-radius: 6px;

            border: 1px solid #ccc;

            box-sizing: border-box;
        }

        input:focus {
            border-color: #667eea;

            outline: none;

            box-shadow:
                0 0 5px rgba(102, 126, 234, 0.5);
        }

        button {
            width: 100%;

            padding: 12px;

            background: #667eea;

            color: white;

            border: none;

            border-radius: 6px;

            font-size: 16px;

            cursor: pointer;
        }

        button:hover {
            background: #5563d6;
        }

        .dashboard-button {
            display: block;

            width: 100%;

            padding: 11px;

            margin-top: 10px;

            background: #212529;

            color: white;

            text-decoration: none;

            border-radius: 6px;

            text-align: center;

            box-sizing: border-box;
        }

        .dashboard-button:hover {
            background: #343a40;
        }

        .success {
            background: #e6ffed;

            color: #1a7f37;

            padding: 10px;

            border-radius: 6px;

            margin-bottom: 15px;

            text-align: center;
        }

        .error {
            color: red;

            font-size: 13px;

            margin-bottom: 10px;
        }

    </style>

</head>

<body>

<div class="card">

    <h2>
        Create Order
    </h2>


    @if(session('success'))

        <div class="success">
            {{ session('success') }}
        </div>

    @endif


    <form
        method="POST"
        action="{{ route('orders.store') }}"
    >

        @csrf


        <label>
            Customer Name
        </label>

        <input
            type="text"
            name="customer_name"
            value="{{ old('customer_name') }}"
            placeholder="Enter customer name"
            required
            oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')"
        >


        @error('customer_name')

            <div class="error">
                {{ $message }}
            </div>

        @enderror


        <label>
            Amount
        </label>

        <input
            type="number"
            name="amount"
            value="{{ old('amount') }}"
            placeholder="Enter amount"
            min="1"
            step="0.01"
            required
        >


        @error('amount')

            <div class="error">
                {{ $message }}
            </div>

        @enderror


        <button type="submit">
            Create Order & Send Webhook
        </button>

    </form>


    <a
        href="{{ route('webhooks.index') }}"
        class="dashboard-button"
    >
        📊 Webhook Delivery Dashboard
    </a>

</div>

</body>

</html>