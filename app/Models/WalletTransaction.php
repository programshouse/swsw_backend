<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'order_id',

        'user_id',
        'actor_id',
        'actor_type',

        'type',
        'status',
        'amount',

        'reference',
        'idempotency_key',
        'balance_before',
        'balance_after',
        'description',
        'meta',

        'wallet_debit_request_id',
    ];

     protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function debitRequest()
    {
        return $this->belongsTo(
            WalletDebitRequest::class,
            'wallet_debit_request_id'
        );
    }
}
