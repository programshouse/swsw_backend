<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'birthdate',
        'password',
        'government_id',
        'area_id',
        'shift_id',
        'type',
        'has_vehicle',
        'vehicle_type',
        'image',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
