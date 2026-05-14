<?php

namespace App\Models;

use App\Models\Category;
use App\Models\KitchenProfile;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'category_id',
        'kitchen_profile_id',
        'name',
        'description',
        'quantity',
        'price',
        'image',
        'available_delivery_today',
        'recipe',
        'approved',
        'preparation_time',
        'availability'
    ];

    public function kitchen() {
        return $this->belongsTo(KitchenProfile::class , 'kitchen_profile_id');
    }
    public function category() {
        return $this->belongsTo(Category::class );
    }


}
