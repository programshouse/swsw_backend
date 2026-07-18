<?php

namespace App\Http\Controllers\rate;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\Request;
use App\Models\RateStore;
use App\Models\Order;
use App\Models\UserRate;
use App\Models\DeliveryOrder;

class RateController extends Controller
{

    // create rating
    function rate_kitchen(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'kitchen_profile_id' => 'required|exists:kitchen_profiles,id',
            'star' => 'required|min:1|max:5|integer',
            'note' => 'nullable|string'
        ]);

        Rate::create([
            'user_id' => $user->id,
            'role' =>  'kitchen',
            'kitchen_profile_id' => $validated['kitchen_profile_id'],
            'star' => $validated['star'],
            'note' => $validated['note']
        ]);

        return response()->json([
            'message' => 'rate added successfully'
        ], 203);
    }

    function rate_delivery(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'kitchen_profile_id' => 'required|exists:kitchen_profiles,id',
            'star' => 'required|min:1|max:5|integer',
            'note' => 'nullable|string'
        ]);

        Rate::create([
            'user_id' => $user->id,
            'role' =>  'delivery',
            'kitchen_profile_id' => $validated['kitchen_profile_id'],
            'star' => $validated['star'],
            'note' => $validated['note']
        ]);

        return response()->json([
            'message' => 'rate added successfully'
        ], 203);
    }

    /////////////////////aya 

    public function kitchenRate(Request $request, Order $order)
    {
        $kitchen = $request->user();

        if ($kitchen->role !== 'kitchen') {
            return response()->json([
                'status' => false,
                'message' => 'Only kitchen can rate.',
            ], 403);
        }

        $validated = $request->validate([
            'rated_type' => 'required|in:client,delivery',
            'user_id' => 'required_if:rated_type,client|exists:users,id',
            'delivery_user_id' => 'required_if:rated_type,delivery|exists:delivery_users,id',
            'score' => 'required|integer|min:1|max:5',
            'details' => 'nullable|string',
        ]);

        $exists = RateStore::where('order_id', $order->id)
            ->where('user_id', $kitchen->id)
            ->where('rater_type', 'kitchen')
            ->where('rated_type', $validated['rated_type'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Already rated.',
            ], 422);
        }

        $rate = RateStore::create([
            'order_id' => $order->id,

            // الشخص اللي بيعمل rate هو المطبخ
            'user_id' => $validated['rated_type'] === 'client'
                ? $validated['user_id']
                : null,

            'delivery_user_id' => $validated['rated_type'] === 'delivery'
                ? $validated['delivery_user_id']
                : null,

            'user_rate_id' => null,
            'rater_type' => 'kitchen',
            'rated_type' => $validated['rated_type'],
            'score' => $validated['score'],
            'details' => $validated['details'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Rating submitted successfully.',
            'data' => $rate,
        ]);
    }

    public function userRate(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->role !== 'client') {
            return response()->json([
                'status' => false,
                'message' => 'Only client can rate.',
            ], 403);
        }

        if ($order->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'rated_type' => 'required|in:kitchen,delivery',
            'details' => 'nullable|string',

            'rates' => 'required|array|min:1',
            'rates.*.user_rate_id' => 'required|exists:user_rates,id',
            'rates.*.score' => 'required|integer|min:1|max:5',
        ]);

        $order->load('kitchen');

        $ratedKitchenUserId = null;
        $ratedDeliveryUserId = null;

        if ($validated['rated_type'] === 'kitchen') {
            $ratedKitchenUserId = $order->kitchen?->user_id;

            if (!$ratedKitchenUserId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kitchen user not found for this order.',
                ], 422);
            }
        }

        if ($validated['rated_type'] === 'delivery') {
            $deliveryOrder = DeliveryOrder::where('order_id', $order->id)
                ->whereNotNull('delivery_user_id')
                ->latest('id')
                ->first();

            if (!$deliveryOrder) {
                return response()->json([
                    'status' => false,
                    'message' => 'Delivery user not found for this order.',
                ], 422);
            }

            $ratedDeliveryUserId = $deliveryOrder->delivery_user_id;
        }

        foreach ($validated['rates'] as $rate) {
            RateStore::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'rater_type' => 'client',
                    'rated_type' => $validated['rated_type'],
                    'user_rate_id' => $rate['user_rate_id'],
                ],
                [
                    'user_id' => $validated['rated_type'] === 'kitchen'
                        ? $ratedKitchenUserId
                        : $user->id,

                    'delivery_user_id' => $validated['rated_type'] === 'delivery'
                        ? $ratedDeliveryUserId
                        : null,

                    'score' => $rate['score'],
                    'details' => $validated['details'] ?? null,
                ]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Rating submitted successfully.',
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





    public function kitchenRates($kitchenId)
    {
        $rates = RateStore::with('user:id,name')
            ->where('rated_type', 'kitchen')
            ->where('rater_type', 'client')
            ->where('user_id', $kitchenId)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'average_rate' => round($rates->avg('score'), 1),
            'total_rates' => $rates->count(),
            'rates' => $rates->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'order_id' => $rate->order_id,
                    'client' => [
                        'id' => $rate->client?->id,
                        'name' => $rate->client?->name,
                        'image' => $rate->client?->image,
                    ],
                    'score' => (int) $rate->score,
                    'details' => $rate->details,
                    'created_at' => $rate->created_at?->format('Y-m-d H:i'),
                ];
            })->values(),
        ]);
    }
}
