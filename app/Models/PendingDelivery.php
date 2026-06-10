<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PendingDelivery extends Authenticatable
{

    use HasApiTokens;

    protected $fillable = [
        'delivery_user_id',
        'name',
        'email',
        'phone',
        'birthdate',
        'government_id',
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

    public function delivery()
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'delivery_user_id'
        );
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

}
