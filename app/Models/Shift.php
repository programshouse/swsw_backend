<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'from_time',
        'to_time',
    ];
}
