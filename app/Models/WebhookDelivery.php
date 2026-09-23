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
     * Get the related order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check whether delivery was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check whether delivery failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check whether delivery is still pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}