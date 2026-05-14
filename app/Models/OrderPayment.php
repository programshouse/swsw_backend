<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id' ,
        'amount' ,
        'payment_method' ,
        'payment_status' ,
        'transaction_id' ,
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
