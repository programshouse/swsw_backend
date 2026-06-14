<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{

    public function index(Request $request)
{
    $orders = Order::latest()->get(['id']);

    $order_history = OrderHistory::with([
        'order.user',
        'order.kitchen'
    ])
    ->when($request->order_id, function ($q, $orderId) {
        $q->where('order_id', $orderId);
    })
    ->latest()
    ->get();

    return view('admin.orders.index', compact('order_history', 'orders'));
}
}
