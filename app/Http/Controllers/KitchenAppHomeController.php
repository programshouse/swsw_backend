<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitchenProfile;
use App\Models\Order;

class KitchenAppHomeController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $kitchen = KitchenProfile::find($user->profile->id) ;

        if ($user->role !== 'kitchen') {
            return response()->json([
                'message' => 'Unauthorized'
            ] , 403);
        }


        $total_completed_orders_today = Order::where('kitchen_id', $kitchen->id)->where('status', 'delivered')->whereDate('delivered_at', now())->count();


        $total_revenue_today = Order::where('kitchen_id', $kitchen->id)->where('status', 'delivered')->whereDate('delivered_at', now() )->sum('total');

        return response()->json([
            'total_completed_orders_today' => $total_completed_orders_today,
            'total_revenue_today' => $total_revenue_today,
            'orders' => Order::where('kitchen_id', $kitchen->id)->where('status', 'delivered')->whereDate('delivered_at', now())->get()
        ] , 200);
    }
}
