<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends Model
{

    protected $fillable = [
        'order_id',
        'delivery_user_id',
        'status',
        'rejected_at',
        'cash_settled',
        'cash_settlement_id',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'transferred_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'on_the_way_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cash_settled' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery()
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'delivery_user_id'
        );
    }

    public function deliveryUser(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'delivery_user_id'
        );
    }


    public function cashSettlement()
    {
        return $this->belongsTo(
            DeliveryCashSettlement::class,
            'cash_settlement_id'
        );
    }
}
