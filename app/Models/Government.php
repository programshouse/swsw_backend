<?php

namespace App\Models;

use App\Models\Area;
use Illuminate\Database\Eloquent\Model;

class Government extends Model
{
    protected $fillable = [
        'name',
    ];


    public function areas() {
        return $this->hasMany(Area::class);
    }


}
