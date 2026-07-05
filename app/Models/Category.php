<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\traits\HasLocalization;

class Category extends Model
{
    use HasLocalization;

    protected $fillable = [
        'name_en',
        'name_ar',
    ];

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }
}
