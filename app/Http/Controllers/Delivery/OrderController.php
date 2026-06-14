<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryOrderResource;
use App\Http\Resources\OrderDetailResource;
use App\Models\DeliveryOrder;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $delivery = $request->user();

        $orders = DeliveryOrder::with([
            'order.user',
            'order.kitchen',
            'order.userAddress',
            'order.orderItems.meal'
        ])
            ->where('delivery_user_id', $delivery->id)
            ->where('status', '!=', 'rejected')
            ->get();

        return response()->json([
            'message' => 'success',
            'data' => [
                'orders details' => DeliveryOrderResource::collection($orders),
            ]
        ]);
    }


    public function updateStatus(Request $request, Order $order)
    {
        $delivery = $request->user();

        $deliveryOrder = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if (!$deliveryOrder) {

            $delivery->orders()->attach($order->id, [
                'status' => 'accepted',
            ]);

            $order->update([
                'status' => 'accepted_by_delivery',
            ]);

            return response()->json([
                'message' => 'order accepted',
                'status' => 'accepted'
            ]);
        }

        if ($deliveryOrder->status === 'accepted') {

            $delivery->orders()->updateExistingPivot($order->id, [
                'status' => 'picked_up'
            ]);

            $order->update([
                'status' => 'received_by_delivery',
            ]);

            return response()->json([
                'message' => 'order picked up',
            ]);
        }

        if ($deliveryOrder->status === 'picked_up') {

            $delivery->orders()->updateExistingPivot($order->id, [
                'status' => 'on_the_way'
            ]);

            $order->update([
                'status' => 'on_the_way',
            ]);

            return response()->json([
                'message' => 'order on the way',
            ]);
        } elseif ($deliveryOrder->status === 'on_the_way') {

            $delivery->orders()->updateExistingPivot($order->id, [
                'status' => 'delivered'
            ]);

            $order->update([
                'status' => 'delivered',
            ]);

            return response()->json([
                'message' => 'order delivered',
            ]);
        }

        return response()->json([
            'message' => 'order already completed',
            'status' => $deliveryOrder->status
        ]);
    }



    public function accept(Request $request, Order $order)
    {
        $delivery = $request->user();

        $delivery_order =  DeliveryOrder::where('order_id', $order->id)->where('delivery_user_id', $delivery->id)->first();

        if ($delivery_order && $delivery_order->status === 'accepted') {
            return response()->json([
                'message' => 'you already accepted this order.',
                'data' => [
                    'orders details' => new OrderDetailResource($order),
                ]
            ]);
        }

        if (!$delivery_order) {
            $delivery->orders()->attach($order->id, [
                'status' => 'accepted',
                'rejected_at' => null
            ]);

            $order->update([
                'status' => 'accepted_by_delivery'
            ]);
        }

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

        $delivery_order = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if ($delivery_order && $delivery_order->status === 'rejected') {
            return response()->json([
                'message' => 'you already rejected this order.',
            ]);
        }

        $last_order = DeliveryOrder::where('delivery_user_id', $delivery->id)
            ->latest('id')
            ->first();

        $isBreak = null;

        if ($last_order && $last_order->status === 'rejected') {

            $diffInMinutes = $last_order->rejected_at
                ? $last_order->rejected_at->diffInMinutes(now())
                : null;

            if ($diffInMinutes !== null && $diffInMinutes <= 2) {

                $delivery->update([
                    'is_break' => 1,
                    'break_time' => 15,
                    'break_started_at' => now(),
                ]);

                $isBreak = 'you are now on break after consecutive rejections';
            }
        }

        if ($delivery_order) {
            $delivery->orders()->updateExistingPivot($order->id, [
                'status' => 'rejected',
                'rejected_at' => now()
            ]);

        } else {
            $delivery->orders()->attach($order->id, [
                'status' => 'rejected',
                'rejected_at' => now()
            ]);
        }

        return response()->json([
            'message' => 'you rejected this order',
            'isBreak' => $isBreak
        ]);
    }
}
