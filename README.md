#  PHP_Laravel12_Webhook_Server

<p align="center">
<img src="https://img.shields.io/badge/Laravel-12-red" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP Version">
<img src="https://img.shields.io/badge/Webhook-Spatie-green" alt="Webhook Package">
<img src="https://img.shields.io/badge/Queue-Database-orange" alt="Queue">
</p>

---

This project demonstrates how to build a **Webhook Sender System** using **Laravel 12** and **Spatie Laravel Webhook Server**.

The application allows users to:

* Create an order using a web form
* Validate customer data securely
* Store orders in the database
* Automatically send webhook notifications to external systems
* Process webhook requests asynchronously using Laravel queues

---

##  Overview

This project is a practical implementation of a **Webhook Sender System** in Laravel.
When an order is created from a web form, Laravel automatically sends a signed webhook request to an external endpoint using background queue processing.

---

##  Features

* Laravel 12 setup
* Web form (Blade UI)
* Customer name validation (letters only)
* Database storage
* Queue-based webhook processing
* Signed webhook requests
* Production-ready structure

---

##  Folder Structure

```
app/
 ├── Models/
 │     └── Order.php
 └── Http/
       └── Controllers/
             └── OrderController.php

resources/
 └── views/
       └── order-form.blade.php

routes/
 └── web.php

config/
 └── webhook-server.php
```

---

##  Requirements

* PHP ≥ 8.2
* Composer
* MySQL
* Node (optional)
* XAMPP / Laravel Herd / Local server

---

## STEP 1 — Create Laravel Project

```bash
composer create-project laravel/laravel webhook-server
```

Start server:

```bash
php artisan serve
```

Open:

```
http://127.0.0.1:8000
```

---

## STEP 2 — Database Configuration

Create database:

```sql
CREATE DATABASE laravel;
```

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

---

## STEP 3 — Install Webhook Package

```bash
composer require spatie/laravel-webhook-server
```

Publish configuration:

```bash
php artisan vendor:publish --provider="Spatie\WebhookServer\WebhookServerServiceProvider"
```

---

## STEP 4 — Environment Setup

Update `.env`:

```env
QUEUE_CONNECTION=database
WEBHOOK_SECRET=my_secret_123
```

---

## STEP 5 — Queue Setup

```bash
php artisan queue:table

php artisan migrate
```

Run worker:

```bash
php artisan queue:work
```

(Keep this terminal running)

---

## STEP 6 — Create Order Model

```bash
php artisan make:model Order -m
```

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->decimal('amount',10,2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

```

Run:

```bash
php artisan migrate
```

### Model

`app/Models/Order.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_name',
        'amount'
    ];
}
```

---

## STEP 7 — Create Controller

```bash
php artisan make:controller OrderController
```

### Controller Code

`app/Http/Controllers/OrderController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Spatie\WebhookServer\WebhookCall;

class OrderController extends Controller
{
    // Show Form
    public function create()
    {
        return view('order-form');
    }

    // Store Order
    public function store(Request $request)
    {
        // ✅ VALIDATION
        $request->validate([
            'customer_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[A-Za-z\s]+$/'
            ],
            'amount' => 'required|numeric|min:1'
        ], [
            'customer_name.regex' =>
                'Customer name must contain only letters and spaces.'
        ]);

        // ✅ CLEAN NAME (remove extra spaces)
        $name = trim(preg_replace('/\s+/', ' ', $request->customer_name));

        // Save Order
        $order = Order::create([
            'customer_name' => $name,
            'amount' => $request->amount,
        ]);

        // ✅ SEND WEBHOOK
        WebhookCall::create()
            ->url('https://webhook.site/12fe3aae-dcc6-468f-bf90-e6183dbaafbf')
            ->payload([
                'event' => 'order.created',
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'amount' => $order->amount,
            ])
            ->useSecret(config('webhook-server.signing_secret'))
            ->dispatch();

        return redirect()->back()
            ->with('success', 'Order Created & Webhook Sent!');
    }
}
```

---

## STEP 8 — Routes

`routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::get('/order-form', [OrderController::class, 'create']);

Route::post('/orders', [OrderController::class, 'store'])
        ->name('orders.store'); 
```

---

## STEP 9 — Blade View

Create:

```
resources/views/order-form.blade.php
```
```
<!DOCTYPE html>
<html>
<head>
<title>Create Order</title>

<style>
body{
    font-family:Arial;
    background:linear-gradient(135deg,#667eea,#764ba2);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:0;
}

.card{
    background:#fff;
    padding:30px;
    width:380px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

h2{text-align:center;margin-bottom:20px;}

label{font-weight:bold;}

input{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

input:focus{
    border-color:#667eea;
    outline:none;
    box-shadow:0 0 5px rgba(102,126,234,0.5);
}

button{
    width:100%;
    padding:12px;
    background:#667eea;
    color:white;
    border:none;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
}

button:hover{background:#5563d6;}

.success{
    background:#e6ffed;
    color:#1a7f37;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    text-align:center;
}

.error{
    color:red;
    font-size:13px;
    margin-bottom:10px;
}
</style>
</head>

<body>

<div class="card">

<h2>Create Order</h2>

@if(session('success'))
<div class="success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('orders.store') }}">
@csrf

<label>Customer Name</label>
<input type="text"
       name="customer_name"
       value="{{ old('customer_name') }}"
       placeholder="Enter customer name"
       required
       oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'')">

@error('customer_name')
<div class="error">{{ $message }}</div>
@enderror


<label>Amount</label>
<input type="number"
       name="amount"
       value="{{ old('amount') }}"
       placeholder="Enter amount"
       required>

@error('amount')
<div class="error">{{ $message }}</div>
@enderror

<button type="submit">Create Order</button>

</form>

</div>

</body>
</html>
```

---

## STEP 10 — Webhook Configuration

`config/webhook-server.php`

```php
<?php

return [

    'queue' => 'default',
    'connection' => 'database',

    'signing_secret' => env('WEBHOOK_SECRET'),

    'http_verb' => 'post',

    'proxy' => null,

    'signer' => \Spatie\WebhookServer\Signer\DefaultSigner::class,

    'signature_header_name' => 'Signature',
    'timestamp_header_name' => 'Timestamp',

    'headers' => [
        'Content-Type' => 'application/json',
        'X-App' => 'LaravelWebhookServer',
    ],

    'timeout_in_seconds' => 10,

    'tries' => 5,

    'backoff_strategy' =>
        \Spatie\WebhookServer\BackoffStrategy\ExponentialBackoffStrategy::class,

    'webhook_job' =>
        \Spatie\WebhookServer\CallWebhookJob::class,

    'verify_ssl' => true,

    'throw_exception_on_failure' => true,

    'tags' => ['webhook'],
];
```

Clear config cache:

```bash
php artisan optimize:clear
```

---

## STEP 11 — Testing

1. Visit:

```
http://127.0.0.1:8000/order-form
```

2. Enter valid data
   
   <img width="437" height="309" alt="Screenshot 2026-02-23 110529" src="https://github.com/user-attachments/assets/18fef912-50b0-469b-b1ec-1d5df05541ed" />

3. Submit form

   <img width="533" height="408" alt="Screenshot 2026-02-23 110543" src="https://github.com/user-attachments/assets/62986292-bef4-4815-a02d-407945e24685" />

4. Open https://webhook.site
5. Confirm webhook request received

   <img width="1919" height="736" alt="Screenshot 2026-02-23 110619" src="https://github.com/user-attachments/assets/648cadc9-9f54-470f-8b71-8c4422815a15" />


---

## Troubleshooting

### Webhook not sent?

```bash
php artisan queue:work
```

### Config not updating?

```bash
php artisan config:clear
```

### Signature error?

Ensure:

```
WEBHOOK_SECRET exists in .env
```

---
