<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientRateResource;
use App\Http\Resources\KitchenRateResource;
use App\Models\RateStore;
use App\Models\Point;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\UserRate;

class RateStoreController extends Controller
{

    public function rates_by_kitchen()
    {
        $all_delivery_rates = RateStore::where('rated_type', 'delivery')->where('rater_type', 'kitchen')->with('order')->get();

        return response()->json([
            'data' => KitchenRateResource::collection($all_delivery_rates),
        ]);
    }


    public function rates_by_client()
    {
        $all_delivery_rates = RateStore::where('rated_type', 'delivery')->where('rater_type', 'client')->with('order')->get();

        return response()->json([
            'data' => ClientRateResource::collection($all_delivery_rates),
        ]);
    }





    public function getRates(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:client,kitchen,delivery',
            'target_type' => 'required|in:client,kitchen,delivery|different:type',
        ]);

        $rates = UserRate::where('type', $validated['type'])
            ->where('target_type', $validated['target_type'])
            ->get()
            ->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'type' => $rate->type,
                    'target_type' => $rate->target_type,
                    'max_score' => $rate->max_score,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $rates,
        ], 200);
    }



   public function storeRates(Request $request)
{
    $delivery = auth('api_delivery')->user();

    $validated = $request->validate([
        'order_id' => 'required|exists:orders,id',
        'rated_type' => 'required|in:client,kitchen',
        'details' => 'nullable|string',

        'rates' => 'required|array|min:1',
        'rates.*.user_rate_id' => 'required|exists:user_rates,id',
        'rates.*.score' => 'required|integer|min:1|max:5',
    ]);

    $order = Order::findOrFail($validated['order_id']);

    foreach ($validated['rates'] as $rate) {
        RateStore::updateOrCreate(
            [
                'order_id' => $order->id,
                'delivery_user_id' => $delivery->id,
                'user_rate_id' => $rate['user_rate_id'],
                'rated_type' => $validated['rated_type'],
                'rater_type' => 'delivery',
            ],
            [
                'user_id' => $validated['rated_type'] === 'client'
                    ? $order->user_id
                    : $order->kitchen_id,

                'score' => $rate['score'],
                'details' => $validated['details'] ?? null,
            ]
        );
    }

    return response()->json([
        'status' => true,
        'message' => 'Rates saved successfully.'
    ]);
}






    public function myRewards()
    {
        $points = Point::latest()->get()->map(function ($point) {
            return [
                'id' => $point->id,
                'name' => $point->name,
                'points' => $point->number,
                'amount' => $point->amount,

            ];
        });

        return response()->json([
            'status' => true,
            'points' => $points,
        ], 200);
    }
}
