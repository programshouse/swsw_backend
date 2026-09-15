<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientRateResource;
use App\Http\Resources\KitchenRateResource;
use App\Models\RateStore;
use App\Models\Point;
use App\Models\User;
use App\Models\Order;
use App\Models\DeliveryOrder;
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
        try {

            $user = $request->user();

            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'details' => 'nullable|string',

                'rates' => 'required|array|min:1',
                'rates.*.user_rate_id' => 'required|exists:user_rates,id',
                'rates.*.score' => 'required|integer|min:1|max:5',
            ]);

            $order = Order::findOrFail($validated['order_id']);

            // Get delivery user for this order
            $deliveryOrder = DeliveryOrder::where('order_id', $order->id)
                ->whereNotNull('delivery_user_id')
                ->latest('id')
                ->first();

            if (!$deliveryOrder) {
                return response()->json([
                    'status' => false,
                    'message' => 'No delivery user found for this order.',
                ], 422);
            }


            foreach ($validated['rates'] as $rate) {

                // Get rate target type from user_rates table
                $userRate = UserRate::find($rate['user_rate_id']);

                if (!$userRate) {
                    continue;
                }

                RateStore::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'user_rate_id' => $rate['user_rate_id'],
                        'rater_type' => 'delivery',
                        'rated_type' => $userRate->type,
                    ],
                    [
                        'user_id' => null,
                        'delivery_user_id' => $deliveryOrder->delivery_user_id,
                        'score' => $rate['score'],
                        'details' => $validated['details'] ?? null,
                    ]
                );
            }
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
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


    public function myRates(Request $request)
    {
        $delivery = $request->user();

        $rates = RateStore::query()
            ->with([
                'order.userAddress',
                'user:id,name,phone',
                'userRate',
            ])
            ->where('rated_type', 'delivery')
            ->where('rater_type', 'client')
            ->where('delivery_user_id', $delivery->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'points_count' => $delivery->points ?? 0,
            'average_rating' => round($rates->avg('score') ?? 0, 1),
            'ratings_count' => $rates->count(),
            'data' => ClientRateResource::collection($rates),
        ]);
    }
}
