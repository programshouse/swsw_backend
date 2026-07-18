<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFeeRule extends Model
{
    protected $fillable = [
        'min_order_amount',
        'max_order_amount',
        'kitchen_service_fee',
        'client_service_fee',
        'is_active',
    ];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'kitchen_service_fee' => 'decimal:2',
        'client_service_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
