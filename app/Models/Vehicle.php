<?php

namespace App\Models;
use App\traits\HasLocalization;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{ 
    use HasLocalization;
    protected $fillable = [
        'name_ar',
        'name_en',
        'max_km',
        'zone',
        'price_distance_meters',
'price',
'estimated_time_minutes',
    ];

    public function levels()
    {
        return $this->hasMany(Level::class);
    }
}
