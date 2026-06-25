<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryShiftLog extends Model
{
    protected $fillable = [
        'delivery_user_id',
        'start_time',
        'end_time',
        'status',
         'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
    ];
}
