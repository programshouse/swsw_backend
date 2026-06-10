<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    
    protected $fillable = ['area_id','delivery_user_id', 'order_id', 'order_status', 'type', 'details', 'image'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function deliveryUser()
    {
        return $this->belongsTo(DeliveryUser::class);
    }
}
