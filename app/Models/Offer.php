<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\traits\HasLocalization;

class Offer extends Model
{   use HasLocalization;
     protected $fillable = [
        'name_ar',
        'name_en',
        'description_en',
         'description_ar',
        'points',
        'is_active',
    ];
}
