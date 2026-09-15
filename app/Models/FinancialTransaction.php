<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    protected $fillable = [

        'order_id',

        'wallet_type',
        'wallet_id',

        'transaction_type',
         'payment_channel',

        'credit',
        'debit',

        'balance_after',

        'reference',

        'description',

        'meta',
    ];

    protected $casts = [

        'credit'=>'decimal:2',

        'debit'=>'decimal:2',

        'balance_after'=>'decimal:2',

        'meta'=>'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
