<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateStore extends Model
{

    protected $fillable = [
        'order_id',
        'delivery_user_id',
        'user_id',
        'user_rate_id',
        'rater_type',
        'rated_type',
        'score',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryUser()
    {
        return $this->belongsTo(DeliveryUser::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userRate()
    {
        return $this->belongsTo(UserRate::class);
    }

}
