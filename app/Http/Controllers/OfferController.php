<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MealOffer;

class OfferController extends Controller
{
    public function kitchenOffers(Request $request)
    {
        $user = $request->user();

    $offers = MealOffer::with([
            'meal.category',
            'meal.kitchen.user'
        ])
        ->where('status', 1)
        ->whereHas('meal.kitchen', function ($q) use ($user) {
            $q->where('area_id', $user->area_id);
        })
        ->where(function ($q) {
            $q->whereNull('start_date')
              ->orWhereDate('start_date', '<=', now());
        })
        ->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhereDate('end_date', '>=', now());
        })
        ->latest()
        ->get();

    return response()->json([
        'status' => true,
        'offers' => $offers->map(function ($offer) {

            $meal = $offer->meal;

            if (!$meal) {
                return null;
            }

            $price = (float) $meal->price;
            $discount = round(($price * $offer->percentage) / 100, 2);
            $finalPrice = round($price - $discount, 2);

                return [
                    'id' => $offer->id,

                    'percentage' => $offer->percentage,

                    'start_date' => $offer->start_date,

                    'end_date' => $offer->end_date,

                    'meal' => [
                        'id' => $meal->id,
                        'name' => $meal->name,
                        'description' => $meal->description,
                        'image' => asset($meal->image),

                        'price' => $price,
                        'price_after_discount' => $finalPrice,

                        'category' => [
                            'id' => $meal->category?->id,
                            'name' => $meal->category?->name,
                        ],

                        'kitchen' => [
                            'id' => $meal->kitchen?->id,
                            'name' => $meal->kitchen?->user?->name,
                        ],
                    ],
                ];
            }),
        ]);
    }
}
