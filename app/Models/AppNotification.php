<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AppNotification extends Model
{
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'type',
        'title_ar',
        'title_en',
        'body_ar',
        'body_en',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
