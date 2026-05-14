<?php

namespace App\Models;

use App\Models\Government;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
     protected $fillable = [
        'name',
        'government_id',
    ];


    public function government() {
        return $this->belongsTo(Government::class);
    }
}
