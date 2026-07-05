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
        'verified_at',
        'referral_code',
        'is_reserve',
        'shift_code'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'break_started_at' => 'datetime',
    ];

    public function getMonthlyPointsAttribute()
    {
        return $this->points()
            ->wherePivotBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->sum('amount');
    }

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


   

    public function deliveryOrders()
    {
        return $this->hasMany(
            DeliveryOrder::class,
            'delivery_user_id'
        );
    }

    public function points()
    {
        return $this->belongsToMany(
            Point::class,
            'delivery_points'
        )->withTimestamps();
    }

    public function deliveryPoints()
    {
        return $this->hasMany(
            DeliveryPoint::class,
            'delivery_user_id'
        );
    }

    public function pointTransactions()
{
    return $this->morphMany(PointTransaction::class, 'owner');
}

public function getTotalPointsAttribute()
{
    return $this->pointTransactions()->sum('points');
}



public function shiftLogs()
{
    return $this->hasMany(DeliveryShiftLog::class, 'delivery_user_id');
}

public function orders()
{
    return $this->belongsToMany(Order::class, 'delivery_orders')
        ->withPivot('status', 'cash_settled')
        ->withTimestamps();
}
}
