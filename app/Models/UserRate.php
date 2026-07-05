<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\traits\HasLocalization;

class UserRate extends Model
{
     use HasLocalization;
      protected $fillable = [
        'name_ar',
         'name_en',
        'type',
        'target_type',
        'max_score',
    ];

}
