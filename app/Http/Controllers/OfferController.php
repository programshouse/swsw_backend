<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MealOffer;
use App\Models\PointTransaction;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    public function kitchenOffers(Request $request)
    {
        $user = $request->user();

        $offers = MealOffer::with([
            'meal',
            'meal.kitchen',
            'meal.category',
        ])
            ->where('status', 1)
            ->get();

        return response()->json([
            'user_id' => $user->id,
            'user_area_id' => $user->area_id,

            'offers' => $offers->map(function ($offer) {
                return [
                    'offer_id' => $offer->id,
                    'meal_id' => $offer->meal_id,
                    'meal_image' => $offer->meal->image,
                    'percentage' => $offer->percentage,
                    'meal_exists' => $offer->meal ? true : false,

                    'kitchen_id' => $offer->meal?->kitchen?->id,
                    'kitchen_area_id' => $offer->meal?->kitchen?->area_id,

                    'start_date' => $offer->start_date,
                    'end_date' => $offer->end_date,
                    'status' => $offer->status,
                ];
            }),
        ]);
    }


    public function myPoints(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $ownerType = get_class($user);

        $transactions = PointTransaction::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $user->id)
            ->latest('id')
            ->get();

        $totalPoints = (int) $transactions->sum('points');

        return response()->json([
            'status' => true,
            'data' => [
                'user_id' => $user->id,
                'user_type' => class_basename($ownerType),
                'total_points' => $totalPoints,

                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'source' => $transaction->source,
                        'points' => (int) $transaction->points,
                        'notes' => $transaction->notes,
                        'reference_type' => $transaction->reference_type
                            ? class_basename($transaction->reference_type)
                            : null,
                        'reference_id' => $transaction->reference_id,
                        'created_at' => $transaction->created_at?->toDateTimeString(),
                    ];
                })->values(),
            ],
        ]);
    }
}
