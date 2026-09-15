<?php

namespace App\Http\Controllers;

use App\Models\KitchenProfile;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use App\Models\DeliveryUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardInsightsController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $last7Days = Carbon::today()->subDays(6);

        $ordersByStatus = Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $ordersTrend = Order::query()
            ->whereDate('created_at', '>=', $last7Days)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_revenue')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trend = collect(range(0, 6))->map(function ($dayOffset) use ($last7Days, $ordersTrend) {
            $date = $last7Days->copy()->addDays($dayOffset)->toDateString();
            $daily = $ordersTrend->get($date);

            return [
                'date' => $date,
                'orders' => (int) ($daily->total_orders ?? 0),
                'revenue' => (float) ($daily->total_revenue ?? 0),
            ];
        });

        $data = [
            'kitchens' => [
                'total' => KitchenProfile::count(),
                'approved' => KitchenProfile::where('statue', 'approved')->count(),
                'pending' => KitchenProfile::where('statue', 'pending')->count(),
                'rejected' => KitchenProfile::where('statue', 'rejected')->count(),
                'open_now' => KitchenProfile::where('open_status', 'open')->count(),
            ],
            'users' => [
                'total' => User::count(),
                'clients' => User::where('role', 'client')->count(),
                'kitchens' => User::where('role', 'kitchen')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'active' => User::where('status', 'active')->count(),
            ],
            'meals' => [
                'total' => Meal::count(),
                'approved' => Meal::where('approved', true)->count(),
                'pending' => Meal::where('approved', false)->count(),
                'available_now' => Meal::where('availability', true)->count(),
                'rejected' => Meal::where('approved', 'rejected')->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'today' => Order::whereDate('created_at', $today)->count(),
                'delivered_today' => Order::where('status', 'delivered')->whereDate('delivered_at', $today)->count(),
                'statuses' => $ordersByStatus,
                'total_revenue' => (float) Order::where('status', 'delivered')->sum('total'),
                'today_revenue' => (float) Order::where('status', 'delivered')->whereDate('delivered_at', $today)->sum('total'),
                'last_7_days_trend' => $trend,
            ],
            'delivery' => [
                'total' => DeliveryUser::count(),
                 'approved' => DeliveryUser::where('status', 'approved')->count(),
                'pending' => DeliveryUser::where('status', 'pending')->count(),
                'rejected' => DeliveryUser::where('status', 'rejected')->count(),
                // 'inactive' => DeliveryUser::where('status', 'inactive')->count(),
            ],
        ];

        return view('admin.dashboard.index', compact('data'));
    }
}
