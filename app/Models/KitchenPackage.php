<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenPackage extends Model
{
    protected $fillable = [
        'name',
        'desc',
        'features',
        'price',
        'duration',
        'duration_unit',
        'active',
        'meals_limit',
        'orders_limit',
    ];

    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
        'price' => 'decimal:2',
        'duration' => 'integer',
        'meals_limit' => 'integer',
        'orders_limit' => 'integer',
    ];



    public function subscriptions()
    {
        return $this->hasMany(KitchenPackageSubscription::class, 'kitchen_package_id');
    }
}
