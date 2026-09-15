<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenPackageSubscription extends Model
{

    protected $guarded = [];

    public function kitchen()
    {
        return $this->belongsTo(
            KitchenProfile::class,
            'kitchen_id'
        );
    }

    public function package()
    {
        return $this->belongsTo(
            KitchenPackage::class,
            'kitchen_package_id'
        );
    }

    public function paymentTransactions()
    {
        return $this->hasMany(
            PaymentTransaction::class,
            'kitchen_subscription_id'
        );
    }


    protected $casts = [
        'package_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
