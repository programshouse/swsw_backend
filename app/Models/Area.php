<?php

namespace App\Models;

use App\Models\Government;
use App\traits\HasLocalization;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasLocalization;

    protected $fillable = [
        'name_en',
        'name_ar',
        'government_id',
        'is_active',
        'lat',
        'lng',
        'polygon',
    ];
    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'polygon' => 'array',
        'is_active' => 'boolean',
    ];

    public function government()
    {
        return $this->belongsTo(Government::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
