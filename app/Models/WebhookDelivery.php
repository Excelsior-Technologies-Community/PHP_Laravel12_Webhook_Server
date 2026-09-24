<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_uuid',
        'order_id',
        'event_name',
        'webhook_url',
        'status',
        'attempts',
        'response_status',
        'error_message',
        'payload',
        'last_attempt_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'last_attempt_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Related order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(
            Order::class
        );
    }

    /**
     * Successful status.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Failed status.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Pending status.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Status badge class.
     */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {

            'success' => 'success',

            'failed' => 'danger',

            'pending' => 'warning',

            default => 'secondary',
        };
    }
}