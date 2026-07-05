<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'source',
        'points',
        'reference_type',
        'reference_id',
        'notes',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
