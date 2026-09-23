<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryDistanceRule extends Model
{
     protected $fillable = [
        'min_distance',
        'max_distance',
        'price_per_km',
    ];
}
