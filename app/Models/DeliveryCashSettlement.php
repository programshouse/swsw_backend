<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryCashSettlement extends Model
{
    protected $fillable = [
        'delivery_user_id',
        'amount',
        'status',
        'merchant_reference',
        'provider',
        'provider_status',
        'session_id',
        'session_url',
        'transaction_id',
        'expires_at',
        'paid_at',
        'failed_at',
        'failure_reason',
        'provider_response',
        'verification_response',
        'callback_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'provider_response' => 'array',
        'verification_response' => 'array',
        'callback_payload' => 'array',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'delivery_user_id'
        );
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(
            DeliveryOrder::class,
            'cash_settlement_id'
        );
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(
            PaymentTransaction::class,
            'delivery_cash_settlement_id'
        );
    }
}
