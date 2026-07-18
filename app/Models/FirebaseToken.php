<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FirebaseToken extends Model
{
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'token',
        'app_type',
        'language',
        'device_id',
        'device_type',
        'device_name',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

     public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }
}
