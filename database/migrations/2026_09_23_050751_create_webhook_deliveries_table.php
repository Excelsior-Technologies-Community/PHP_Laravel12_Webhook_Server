<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();

            $table->uuid('webhook_uuid')->unique();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('event_name')->default('order.created');

            $table->string('webhook_url');

            $table->string('status')->default('pending');

            $table->unsignedInteger('attempts')->default(0);

            $table->unsignedSmallInteger('response_status')->nullable();

            $table->text('error_message')->nullable();

            $table->json('payload')->nullable();

            $table->timestamp('last_attempt_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('event_name');
            $table->index('last_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};