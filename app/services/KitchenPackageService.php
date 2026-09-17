<?php

namespace App\Services;

use App\Models\Order;
use App\Models\KitchenProfile;
use App\Models\KitchenPackageSubscription;

class KitchenPackageService
{

    public function checkOrderLimit(KitchenProfile $kitchen): array
    {
        $subscription = KitchenPackageSubscription::with('package')
            ->where('kitchen_id', $kitchen->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();


        if (!$subscription || !$subscription->package) {

            return [
                'allowed' => false,
                'message' => 'No active package found. Please renew your package.',
            ];
        }


        $package = $subscription->package;


        $ordersQuery = Order::where(
            'kitchen_id',
            $kitchen->id
        );


        if ($subscription->starts_at && $subscription->expires_at) {

            $ordersQuery->whereBetween('created_at', [
                $subscription->starts_at,
                $subscription->expires_at
            ]);
        }


        $ordersCount = $ordersQuery->count();
       


        // هنا مكان الكود
        if ($ordersCount >= $package->orders_limit) {

            $subscription->update([
                'status' => 'expired',
            ]);


            $kitchen->update([
                'open_status' => 'closed',
                'close_reason' => 'package_limit',
            ]);


            return [
                'allowed' => false,
                'message' => 'Your package order limit has been reached. Please renew your package.',
                'orders_limit' => $package->orders_limit,
                'used_orders' => $ordersCount,
            ];
        }


        return [
            'allowed' => true,
            'remaining_orders' => $package->orders_limit - $ordersCount,
        ];
    }
}
