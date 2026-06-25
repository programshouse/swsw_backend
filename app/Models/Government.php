<?php

namespace App\Models;

use App\Models\Area;
use Illuminate\Database\Eloquent\Model;
use App\traits\HasLocalization;

class Government extends Model
{
     use HasLocalization;
    protected $fillable = [
        'name',
         'is_active',
    ];


    public function areas() {
        return $this->hasMany(Area::class);
    }
    protected $casts = [
    'is_active' => 'boolean',
];


}
