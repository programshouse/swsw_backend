<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryPoint;

class PointController extends Controller
{
    public function index(Request $request)
{
    $delivery = $request->user();

    $points = DeliveryPoint::with([
        'point',
        'order.user',
        'order.kitchen',
    ])
        ->where('delivery_user_id', $delivery->id)
        ->latest()
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,

                'point' => [
                    'id' => $item->point?->id,
                    'name' => $item->point?->name,
                    'number' => $item->point?->number,
                    'amount' => $item->point?->amount,
                ],

                'order' => [
                    'id' => $item->order?->id,
                    'number' => $item->order?->number,
                    'total' => $item->order?->total,
                    'status' => $item->order?->status,
                    'payment_method' => $item->order?->payment_method,
                    'created_at' => $item->order?->created_at,

                    'client' => [
                        'id' => $item->order?->user?->id,
                        'name' => $item->order?->user?->name,
                        'phone' => $item->order?->user?->phone,
                    ],

                    'kitchen' => [
                        'id' => $item->order?->kitchen?->id,
                        'name' => $item->order?->kitchen?->name,
                        'phone' => $item->order?->kitchen?->phone,
                    ],
                ],

                'created_at' => $item->created_at,
            ];
        });

    return response()->json([
        'status' => true,
        'data' => $points,
    ], 200);
}



 ///////////////////new
public function myPoints(Request $request)
{
    $user = $request->user();

    $transactions = $user
        ->pointTransactions()
        ->latest('id')
        ->get();

    return response()->json([
        'status' => true,

        'total_points' => (int) $transactions->sum('points'),

        'total_added' => (int) $transactions
            ->where('points', '>', 0)
            ->sum('points'),

        'total_deducted' => abs(
            (int) $transactions
                ->where('points', '<', 0)
                ->sum('points')
        ),

        'transactions' => $transactions->map(function ($item) {
            return [
                'id' => $item->id,

                'type' => $item->points > 0
                    ? 'added'
                    : 'deducted',

                'source' => $item->source,

                'points' => (int) $item->points,

                'points_value' => abs((int) $item->points),

                'reference_type' => $item->reference_type,

                'reference_id' => $item->reference_id,

                'notes' => $item->notes,

                'created_at' => optional($item->created_at)
                    ->format('Y-m-d H:i'),
            ];
        })->values(),
    ]);
}
}
