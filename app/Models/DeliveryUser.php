<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
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
        'is_break',
        'break_time',
        'break_started_at',
        'code',
        'code_expires_at',
        'verified_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'break_started_at' => 'datetime',
    ];


    public function pendingDelivery()
    {
        return $this->hasOne(
            PendingDelivery::class,
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
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }


    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'delivery_orders'
        );
    }

    public function deliveryOrders()
    {
        return $this->hasMany(
            DeliveryOrder::class,
            'delivery_user_id'
        );
    }
}
