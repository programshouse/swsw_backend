<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitchenProfile;
use App\Models\Order;
use App\Models\KitchenPackageSubscription;
use App\Models\Meal;

class KitchenAppHomeController extends Controller
{
    public function index(Request $request) {
        $user = $request->user();
        $kitchen = KitchenProfile::find($user->profile->id) ;
        $subscription = KitchenPackageSubscription::with('package')
    ->where('kitchen_id', $kitchen->id)
    ->where('status', 'active')
    ->latest('id')
    ->first();


$packageData = null;


if ($subscription && $subscription->package) {

    $package = $subscription->package;


    // عدد الوجبات المضافة حاليا
    $usedMeals = Meal::where(
        'kitchen_profile_id',
        $kitchen->id
    )->count();


    // عدد الأوردرات خلال فترة الاشتراك
    $usedOrders = Order::where(
        'kitchen_id',
        $kitchen->id
    )
    ->whereBetween('created_at', [
        $subscription->starts_at,
        $subscription->expires_at
    ])
    ->count();



    $packageData = [

        'package_name' => $package->name,

        'meals' => [
            'limit' => $package->meals_limit,
            'used' => $usedMeals,
            'remaining' => max(
                $package->meals_limit - $usedMeals,
                0
            ),
        ],


        'orders' => [
            'limit' => $package->orders_limit,
            'used' => $usedOrders,
            'remaining' => max(
                $package->orders_limit - $usedOrders,
                0
            ),
        ],


        'expires_at' => $subscription->expires_at,
    ];
}

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
            'package_usage' => $packageData,
            'orders' => Order::where('kitchen_id', $kitchen->id)->where('status', 'delivered')->whereDate('delivered_at', now())->get()
        ] , 200);
    }
}
