<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderFinancialReportController extends Controller
{
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Daily filter
        |--------------------------------------------------------------------------
        */

        $date = $request->filled('date')
            ? Carbon::parse($request->date)->toDateString()
            : Carbon::now('Africa/Cairo')->toDateString();

        $status = $request->input('status');

        $keyword = trim(
            (string) $request->input('keyword', '')
        );

        /*
        |--------------------------------------------------------------------------
        | Orders query
        |--------------------------------------------------------------------------
        */

        $ordersQuery = Order::query()
            ->with([
                'user:id,name,phone',
                'kitchen:id,name,user_id',
                'items:id,order_id,meal_id,quantity,price',
                'items.meal:id,name',
                'deliveryOrders' => function ($query) {
                    $query
                        ->whereNotNull('delivery_user_id')
                        ->whereNotIn('status', [
                            'rejected',
                        ])
                        ->with([
                            'deliveryUser:id,name,phone',
                        ])
                        ->latest('id');
                },
            ])
            ->whereDate('created_at', $date);

        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        */

        if ($status !== null && $status !== '') {
            $ordersQuery->where('status', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($keyword !== '') {
            $ordersQuery->where(function ($query) use ($keyword) {
                $query
                    ->where('number', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere(
                                'phone',
                                'like',
                                "%{$keyword}%"
                            );
                    })
                    ->orWhereHas('kitchen', function ($kitchenQuery) use ($keyword) {
                        $kitchenQuery->where(
                            'name',
                            'like',
                            "%{$keyword}%"
                        );
                    });
            });
        }

        $orders = $ordersQuery
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Daily totals
        |--------------------------------------------------------------------------
        */

        $dailyOrdersQuery = Order::query()
            ->whereDate('created_at', $date);

        if ($status !== null && $status !== '') {
            $dailyOrdersQuery->where('status', $status);
        }

        if ($keyword !== '') {
            $dailyOrdersQuery->where(function ($query) use ($keyword) {
                $query
                    ->where('number', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere(
                                'phone',
                                'like',
                                "%{$keyword}%"
                            );
                    })
                    ->orWhereHas('kitchen', function ($kitchenQuery) use ($keyword) {
                        $kitchenQuery->where(
                            'name',
                            'like',
                            "%{$keyword}%"
                        );
                    });
            });
        }

        $summary = [
            'orders_count' => (clone $dailyOrdersQuery)->count(),

            /*
             * إجمالي ما دفعه العملاء فعليًا بعد الخصم.
             */
            'users_paid' => round(
                (float) (clone $dailyOrdersQuery)->sum('total'),
                2
            ),

            /*
             * إجمالي مستحقات المطابخ.
             */
            'kitchens_net' => round(
                (float) (clone $dailyOrdersQuery)
                    ->sum('kitchen_net_amount'),
                2
            ),

            /*
             * إجمالي ما يحصل عليه الدليفري.
             */
            'deliveries_amount' => round(
                (float) (clone $dailyOrdersQuery)
                    ->sum('delivery_price'),
                2
            ),

            /*
             * رسوم التطبيق:
             * رسوم العميل + رسوم المطبخ.
             */
            'application_service_revenue' => round(
                (float) (
                    (clone $dailyOrdersQuery)
                        ->sum('client_service_fee')
                    +
                    (clone $dailyOrdersQuery)
                        ->sum('kitchen_service_fee')
                ),
                2
            ),

            'vat_total' => round(
                (float) (clone $dailyOrdersQuery)
                    ->sum('vat_value'),
                2
            ),

            'discounts_total' => round(
                (float) (clone $dailyOrdersQuery)
                    ->sum('discount_value'),
                2
            ),
        ];

        $statuses = [
            'pending' => 'قيد الانتظار',
            'accepted_by_kitchen' => 'تم قبول الطلب',
            'preparing' => 'جاري التحضير',
            'ready_to_deliver' => 'جاهز للتوصيل',
            'accepted_by_delivery' => 'تم قبول الدليفري',
            'received_from_kitchen' => 'تم الاستلام من المطبخ',
            'on_the_way' => 'في الطريق',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
            'cancelled_by_admin' => 'ملغي بواسطة الإدارة',
        ];

        return view(
            'admin.order-financial-reports.index',
            compact(
                'orders',
                'summary',
                'date',
                'status',
                'keyword',
                'statuses'
            )
        );
    }

    public function show(Order $order): View
    {
        $order->load([
            'user:id,name,phone,email',
            'userAddress',
            'kitchen:id,name,user_id,logo',
            'kitchen.user:id,name,phone,email',
            'items.meal',
            'deliveryOrders' => function ($query) {
                $query
                    ->whereNotNull('delivery_user_id')
                    ->whereNotIn('status', [
                        'rejected',
                    ])
                    ->with([
                        'deliveryUser:id,name,phone,email',
                    ])
                    ->latest('id');
            },
        ]);

        $deliveryOrder = $order->deliveryOrders->first();

        $financials = $this->buildFinancialDetails($order);

        return view(
            'admin.order-financial-reports.show',
            compact(
                'order',
                'deliveryOrder',
                'financials'
            )
        );
    }

    private function buildFinancialDetails(Order $order): array
    {
        $subtotal = (float) ($order->subtotal ?? 0);

        $vatValue = (float) ($order->vat_value ?? 0);

        $deliveryPrice = (float) (
            $order->delivery_price ?? 0
        );

        $clientServiceFee = (float) (
            $order->client_service_fee ?? 0
        );

        $kitchenServiceFee = (float) (
            $order->kitchen_service_fee ?? 0
        );

        $kitchenNetAmount = (float) (
            $order->kitchen_net_amount
            ?? max(
                $subtotal - $kitchenServiceFee,
                0
            )
        );

        $totalBeforeDiscount = (float) (
            $order->total_before_discount
            ?? (
                $subtotal
                + $vatValue
                + $deliveryPrice
                + $clientServiceFee
            )
        );

        $discountValue = (float) (
            $order->discount_value ?? 0
        );

        $userPaid = (float) (
            $order->total
            ?? max(
                $totalBeforeDiscount - $discountValue,
                0
            )
        );

        /*
         * حاليًا سعر التوصيل كاملًا للدليفري.
         */
        $deliveryAmount = $deliveryPrice;

        /*
         * دخل التطبيق من الخدمات فقط.
         */
        $applicationServiceRevenue =
            $clientServiceFee +
            $kitchenServiceFee;

        return [
            'subtotal' => round($subtotal, 2),
            'vat_value' => round($vatValue, 2),
            'delivery_price' => round($deliveryPrice, 2),
            'client_service_fee' => round(
                $clientServiceFee,
                2
            ),
            'kitchen_service_fee' => round(
                $kitchenServiceFee,
                2
            ),
            'kitchen_net_amount' => round(
                $kitchenNetAmount,
                2
            ),
            'total_before_discount' => round(
                $totalBeforeDiscount,
                2
            ),
            'discount_value' => round(
                $discountValue,
                2
            ),
            'user_paid' => round($userPaid, 2),
            'delivery_amount' => round(
                $deliveryAmount,
                2
            ),
            'application_service_revenue' => round(
                $applicationServiceRevenue,
                2
            ),
        ];
    }
}