<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryPoint extends Model
{

    protected $fillable = [
        'point_id',
        'delivery_user_id',
        'order_id',
        'number'
    ];


    public function point()
    {
        return $this->belongsTo(Point::class);
    }

    public function delivery()
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'delivery_user_id'
        );
    }

    public function order()
{
    return $this->belongsTo(Order::class);
}
}
