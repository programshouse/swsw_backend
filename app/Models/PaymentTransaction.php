<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'provider',
        'merchant_reference',
        'payment_request_id',
        'provider_order_id',
        'transaction_id',
        'card_order_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'refunded_amount',
        'failure_reason',
        'paid_at',
        'verified_at',
        'expires_at',
        'create_response',
        'callback_payload',
        'verification_response',
        'session_id',
        'session_url',
        'provider_status',
        'kitchen_subscription_id',
        'delivery_cash_settlement_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',

        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',

        'create_response' => 'array',
        'callback_payload' => 'array',
        'verification_response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kitchenSubscription()
    {
        return $this->belongsTo(
            KitchenPackageSubscription::class,
            'kitchen_subscription_id'
        );
    }

    public function deliveryCashSettlement()
{
    return $this->belongsTo(
        DeliveryCashSettlement::class,
        'delivery_cash_settlement_id'
    );
}
}
