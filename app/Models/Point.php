<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $fillable = ['name', 'number', 'amount'];

    public function deliveryUsers()
    {
        return $this->belongsToMany(
            DeliveryUser::class,
            'delivery_points'
        )->withTimestamps();
    }

    public function deliveryPoints()
    {
        return $this->hasMany(DeliveryPoint::class);
    }
}
