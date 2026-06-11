<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderItemsResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $delivery = $request->user();
        $orders = Order::where('delivery_user_id', $delivery->id)->get();
        
        foreach ($orders as $order) {
            $order_items = $order->items;
        }

        return response()->json([
            'message' => 'success',
            'data' => [
                'orders details' => OrderDetailResource::collection($orders),
                'order items' => OrderItemsResource::collection($order_items)
            ]
        ]);
    }


    public function updateStatus(Request $request, Order $order)
    {
        $delivery = $request->user();

        if ($order->status === 'pending') {
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
                'message' => 'status already changed to delivered',
            ]);
        }
        return response()->json([
            'message' => 'order status changed successfully',
            'status' => $order->status
        ]);
    }
}
