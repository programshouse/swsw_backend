<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOffer extends Model
{
     protected $fillable = [
        'kitchen_profile_id',
        'meal_id',
        'percentage',
        'start_date',
        'end_date',
        'status',
    ];

    public function kitchen()
    {
        return $this->belongsTo(KitchenProfile::class, 'kitchen_profile_id');
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }
}
