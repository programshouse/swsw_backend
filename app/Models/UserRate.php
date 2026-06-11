<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRate extends Model
{
      protected $fillable = [
        'name',
        'type',
        'max_score',
    ];

}
