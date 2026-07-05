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

    public function offer()
{
    return $this->hasOne(MealOffer::class)
        ->where('status', 1)
        ->where(function ($q) {
            $q->whereNull('start_date')
              ->orWhereDate('start_date', '<=', now());
        })
        ->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhereDate('end_date', '>=', now());
        });
}

public function getFinalPriceAttribute()
{
    $offer = $this->offer;

    if (!$offer) {
        return $this->price;
    }

    return round($this->price - (($this->price * $offer->percentage) / 100), 2);
}
}
