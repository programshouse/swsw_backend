<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\traits\HasLocalization;

class Shift extends Model
{
     use HasLocalization;
    protected $fillable = [
        'name_en',
        'name_ar',
        'from_time',
        'to_time',
    ];
}
