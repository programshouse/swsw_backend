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
    ];

    protected $casts = [
        'rejected_at' => 'datetime',
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
}
