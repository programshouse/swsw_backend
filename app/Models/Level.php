<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = [
        'name',
        'cash_money',
        'km',
        'vehicle_type',
    ];

    public function deliveries()
    {
        return $this->hasMany(DeliveryUser::class, 'level_id');
    }
    
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
