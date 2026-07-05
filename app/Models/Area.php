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
    ];


    public function government() {
        return $this->belongsTo(Government::class);
    }

       public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
