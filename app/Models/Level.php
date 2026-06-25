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
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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
