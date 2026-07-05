<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppPage extends Model
{
     protected $fillable = [
        'app_type',
        'page_type',
        'title_ar',
        'title_en',
        'content_ar',
        'content_en',
        'is_active',
    ];
}
