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
        'active',
    ];

    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
    ];
}
