<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        $order->load([
            'user',
            'kitchen.user',
            'userAddress.area',
            'userAddress.government',
            'items.meal',
            'deliveryOrders.deliveryUser',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        if (in_array($order->status, [
            'delivered',
            'cancelled_by_client',
            'cancelled_by_kitchen',
            'cancelled_by_delivery',
        ])) {
            return back()->with('error', 'لا يمكن إلغاء هذا الطلب');
        }

        $order->update([
            'status' => 'cancelled_by_client',
            'cancel_date' => now(),
        ]);

        $order->deliveryOrders()->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'تم إلغاء الطلب بنجاح');
    }
}
