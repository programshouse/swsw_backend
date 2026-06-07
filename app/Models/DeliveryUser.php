<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DeliveryUser extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'birthdate',
        'password',
        'government_id',
        'level_id',
        'area_id',
        'shift_id',
        'type',
        'has_vehicle',
        'vehicle_id',
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
