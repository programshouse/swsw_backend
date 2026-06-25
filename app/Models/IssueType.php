<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\traits\HasLocalization;

class IssueType extends Model
{
    use HasLocalization;
     protected $fillable = [
        'name_ar',
        'name_en',
        'is_active',
    ];
}
