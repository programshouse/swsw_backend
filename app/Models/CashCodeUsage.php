<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CashCodeUsage extends Model
{
      protected $fillable = [
        'cash_code_id',
        'user_id',
        'order_id',
        'amount',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function cashCode(): BelongsTo
    {
        return $this->belongsTo(CashCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
