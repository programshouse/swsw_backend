<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $delivery = $request->user();

        $orders = Order::with([
            'user',
            'kitchen',
            'userAddress',
            'orderItems.meal'
        ])
            ->where('delivery_user_id', $delivery->id)
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => [
                'orders details' => OrderDetailResource::collection($orders),
            ]
        ]);
    }


    public function updateStatus(Request $request, Order $order)
    {
        $delivery = $request->user();

        if ($order->status === 'accepted') {
            $order->update([
                'delivery_user_id' => $delivery->id,
                'status' => 'assigned'
            ]);
        } elseif ($order->status === 'assigned') {
            $order->update([
                'status' => 'picked_up'
            ]);
        } elseif ($order->status === 'picked_up') {
            $order->update([
                'status' => 'delivered'
            ]);
        } else {
            return response()->json([
                'message' => 'order already delivered',
            ]);
        }
        return response()->json([
            'message' => 'order status changed successfully',
            'status' => $order->status
        ]);
    }



    public function accept(Request $request, Order $order)
    {
        $delivery = $request->user();

        if ($order->status == 'accepted') {
            return response()->json([
                'message' => 'you already accepted this order.',
                'data' => [
                    'orders details' => new OrderDetailResource($order),
                ]
            ]);
        }

        $order->update([
            'delivery_user_id' => $delivery->id,
            'status' => 'accepted'
        ]);

        return response()->json([
            'message' => 'you accepted this order.',
            'data' => [
                'orders details' => new OrderDetailResource($order),
            ]
        ]);
    }

    public function reject(Request $request, Order $order)
    {
        $delivery = $request->user();

        if ($order->status == 'rejected') {
            return response()->json([
                'message' => 'you already rejected this order.',
            ]);
        }


        $last_order = Order::where('delivery_user_id', $delivery->id)
            ->latest('id')
            ->first();

        $isBreak = null;

        if ($last_order && $last_order->status === 'rejected') {

            $diffInMinutes = $last_order->rejected_at->diffInMinutes(now());

            if ($diffInMinutes <= 2) {
                $delivery->update([
                    'is_break' => 1,
                    'break_time' => 15,
                    'break_started_at' => now(),
                ]);

                $isBreak = 'you are now on break after consecutive rejections';
            }
        }

        $order->update([
            'delivery_user_id' => $delivery->id,
            'status' => 'rejected',
            'rejected_at' => now()
        ]);

        return response()->json([
            'message' => 'you rejected this order',
            'isBreak' => $isBreak
        ]);
    }
}
