<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'order_id',
        'type',
        'amount',
        'user_id',
        'wallet_debit_request_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
