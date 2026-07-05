<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryOrderResource;
use App\Http\Resources\OrderDetailResource;
use App\Models\DeliveryOrder;
use App\Models\Order;
use App\Models\DeliveryUser;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use App\Models\DeliveryShiftLog;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $delivery = $request->user();

        $delivery->load('level');

        $activeStatuses = [
            'accepted',
            'picked_up',
            'on_the_way',
        ];

        $activeOrdersCount = DeliveryOrder::where('delivery_user_id', $delivery->id)
            ->whereIn('status', $activeStatuses)
            ->count();

        $cashLimit = (float) optional($delivery->level)->cash_money;

        $currentCash = DeliveryOrder::where('delivery_user_id', $delivery->id)
            ->where('status', 'delivered')
            ->where('cash_settled', false)
            ->whereHas('order', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->with('order')
            ->get()
            ->sum(function ($deliveryOrder) {
                return (float) optional($deliveryOrder->order)->total;
            });

        $orders = DeliveryOrder::with([
            'order.user',
            'order.kitchen',
            'order.userAddress',
            'order.items.meal',
        ])
            ->where('delivery_user_id', $delivery->id)
            ->whereIn('status', [
                'pending',
                'accepted',
                'picked_up',
                'on_the_way',
                'delivered',
                'cancelled',
            ])
            ->latest()
            ->get();

        $request->attributes->set(
            'delivery_has_multiple_active_orders',
            $activeOrdersCount > 1
        );

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'has_multiple_active_orders' => $activeOrdersCount > 1,
                'active_orders_count' => $activeOrdersCount,

                'cash_filter' => [
                    'current_cash' => $currentCash,
                    'cash_limit' => $cashLimit,
                    'is_full_limit' => $cashLimit > 0 && $currentCash >= $cashLimit,
                    'must_deposit_cash' => $cashLimit > 0 && $currentCash >= $cashLimit,
                ],

                'orders_details' => DeliveryOrderResource::collection($orders),
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

        if ($order->status !== 'ready_to_deliver') {
            return response()->json([
                'status' => false,
                'message' => 'Order is not ready to deliver',
                'order_status' => $order->status,
            ], 400);
        }

        $delivery->orders()->attach($order->id, [
            'status' => 'accepted',
            'cash_settled' => false,
        ]);

        $order->update([
            'status' => 'accepted_by_delivery',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order accepted by delivery',
            'delivery_order_status' => 'accepted',
            'order_status' => 'accepted_by_delivery',
        ]);
    }

    if ($deliveryOrder->status === 'accepted') {

        if ($order->status !== 'accepted_by_delivery') {
            return response()->json([
                'status' => false,
                'message' => 'Invalid order status',
                'order_status' => $order->status,
            ], 400);
        }

        $delivery->orders()->updateExistingPivot($order->id, [
            'status' => 'picked_up'
        ]);

        $order->update([
            'status' => 'received_by_delivery',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order picked up',
            'delivery_order_status' => 'picked_up',
            'order_status' => 'received_by_delivery',
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
            'status' => true,
            'message' => 'Order on the way',
            'delivery_order_status' => 'on_the_way',
            'order_status' => 'on_the_way',
        ]);
    }

    if ($deliveryOrder->status === 'on_the_way') {

        $delivery->orders()->updateExistingPivot($order->id, [
            'status' => 'delivered'
        ]);

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'receive_date' => now()->toDateString(),
            'receive_time' => now()->toTimeString(),
        ]);

        OrderHistory::updateOrCreate(
            ['order_id' => $order->id],
            ['status' => 'delivered']
        );

        $cashInfo = $this->getDeliveryCashInfo($delivery);

        return response()->json([
            'status' => true,
            'message' => 'Order delivered',
            'delivery_order_status' => 'delivered',
            'order_status' => 'delivered',
            'cash_filter' => $cashInfo,
        ]);
    }

    return response()->json([
        'status' => false,
        'message' => 'Order already completed',
        'delivery_order_status' => $deliveryOrder->status
    ], 400);
}



    public function accept(Request $request, Order $order)
    {
        $delivery = $request->user();

        $delivery->load('level');

        $cashLimit = (float) optional($delivery->level)->cash_money;

        $currentCash = DeliveryOrder::where('delivery_user_id', $delivery->id)
            ->where('status', 'delivered')
            ->where('cash_settled', false)
            ->whereHas('order', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->with('order')
            ->get()
            ->sum(function ($deliveryOrder) {
                return (float) optional($deliveryOrder->order)->total;
            });

        if ($cashLimit > 0 && $currentCash >= $cashLimit) {
            return response()->json([
                'status' => false,
                'message' => 'You must settle collected cash before accepting new orders.',
                'data' => [
                    'current_cash' => $currentCash,
                    'cash_limit' => $cashLimit,
                    'must_deposit_cash' => true,
                ],
            ], 422);
        }

        $delivery_order = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if ($delivery_order && $delivery_order->status === 'accepted') {
            return response()->json([
                'status' => true,
                'message' => 'you already accepted this order.',
                'data' => [
                    'orders details' => new OrderDetailResource($order),
                    'cash' => [
                        'current_cash' => $currentCash,
                        'cash_limit' => $cashLimit,
                        'must_deposit_cash' => false,
                    ],
                ]
            ]);
        }

        if (!$delivery_order) {
            $delivery->orders()->attach($order->id, [
                'status' => 'accepted',
                'rejected_at' => null,
                'cash_settled' => false,
            ]);

            $order->update([
                'status' => 'accepted_by_delivery'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'you accepted this order.',
            'data' => [
                'orders details' => new OrderDetailResource($order),
                'cash' => [
                    'current_cash' => $currentCash,
                    'cash_limit' => $cashLimit,
                    'must_deposit_cash' => false,
                ],
            ]
        ]);
    }

    public function reject(Request $request, Order $order)
    {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $delivery_order = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if ($delivery_order && $delivery_order->status === 'rejected') {
            return response()->json([
                'status' => false,
                'message' => 'you already rejected this order.',
            ], 400);
        }

        if ($delivery_order) {
            $delivery->orders()->updateExistingPivot($order->id, [
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);
        } else {
            $delivery->orders()->attach($order->id, [
                'status' => 'rejected',
                'rejected_at' => now(),
                'cash_settled' => false,
            ]);
        }

        $newDelivery = $this->assignOrderToNearestDelivery($order, $delivery->id);

        return response()->json([
            'status' => true,
            'message' => 'you rejected this order',
            'reassigned' => $newDelivery ? true : false,
            'new_delivery_id' => $newDelivery?->id,
        ]);
    }

    public function transfer(Request $request, Order $order)
    {
        $delivery = $request->user();

        $deliveryOrder = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if (!$deliveryOrder) {
            return response()->json([
                'status' => false,
                'message' => 'This order is not assigned to you.',
            ], 403);
        }

        if (in_array($deliveryOrder->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'status' => false,
                'message' => 'This order cannot be transferred.',
            ], 422);
        }

        $delivery->orders()->updateExistingPivot($order->id, [
            'status' => 'transferred',
            'rejected_at' => now(),
        ]);

        $newDelivery = $this->assignOrderToNearestDelivery($order, $delivery->id);

        return response()->json([
            'status' => true,
            'message' => 'order transferred successfully',
            'reassigned' => $newDelivery ? true : false,
            'new_delivery_id' => $newDelivery?->id,
        ]);
    }


    public function noResponse(Request $request, Order $order)
    {
        $delivery = $request->user();

        $deliveryOrder = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if (!$deliveryOrder) {
            $delivery->orders()->attach($order->id, [
                'status' => 'rejected',
                'rejected_at' => now(),
                'cash_settled' => false,
            ]);
        } else {
            $delivery->orders()->updateExistingPivot($order->id, [
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);
        }

        $newDelivery = $this->assignOrderToNearestDelivery($order, $delivery->id);

        return response()->json([
            'status' => true,
            'message' => 'order reassigned because delivery did not respond',
            'reassigned' => $newDelivery ? true : false,
            'new_delivery_id' => $newDelivery?->id,
        ]);
    }

    private function assignOrderToNearestDelivery(Order $order, ?int $excludeDeliveryId = null)
    {
        $order->load('kitchen');

        $lat = $order->kitchen->lat ?? null;
        $lng = $order->kitchen->lng ?? null;

        if (!$lat || !$lng) {
            return null;
        }

        $rejectedDeliveryIds = DeliveryOrder::where('order_id', $order->id)
            ->whereIn('status', ['rejected', 'transferred'])
            ->pluck('delivery_user_id')
            ->toArray();

        if ($excludeDeliveryId) {
            $rejectedDeliveryIds[] = $excludeDeliveryId;
        }

        $delivery = DeliveryUser::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('status', 'active')
            ->where('is_break', 0)
            ->whereNotIn('id', array_unique($rejectedDeliveryIds))
            ->select('*')
            ->selectRaw(
                '(6371 * acos(
                cos(radians(?)) *
                cos(radians(lat)) *
                cos(radians(lng) - radians(?)) +
                sin(radians(?)) *
                sin(radians(lat))
            )) AS distance',
                [$lat, $lng, $lat]
            )
            ->orderBy('distance')
            ->first();

        if (!$delivery) {
            return null;
        }

        DeliveryOrder::updateOrCreate(
            [
                'order_id' => $order->id,
                'delivery_user_id' => $delivery->id,
            ],
            [
                'status' => 'pending',
                'rejected_at' => null,
                'cash_settled' => false,
            ]
        );

        $order->update([
            'status' => 'pending_delivery',
        ]);

        return $delivery;
    }

    private function getDeliveryCashInfo($delivery): array
    {
        $delivery->load('level');

        $cashLimit = (float) optional($delivery->level)->cash_money;

        $currentCash = DeliveryOrder::where('delivery_user_id', $delivery->id)
            ->where('status', 'delivered')
            ->where('cash_settled', false)
            ->whereHas('order', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->with('order')
            ->get()
            ->sum(function ($deliveryOrder) {
                return (float) optional($deliveryOrder->order)->total;
            });

        $isFullLimit = $cashLimit > 0 && $currentCash >= $cashLimit;

        return [
            'current_cash' => $currentCash,
            'cash_limit' => $cashLimit,
            'is_full_limit' => $isFullLimit,
            'must_deposit_cash' => $isFullLimit,
        ];
    }


  public function breakStatus(Request $request)
{
    $delivery = $request->user();

    $delivery->load('shift');

    $remainingMinutes = 0;

    if (
        $delivery->is_break &&
        $delivery->break_started_at &&
        $delivery->break_time
    ) {
        $endTime = $delivery->break_started_at
            ->copy()
            ->addMinutes($delivery->break_time);

        $remainingMinutes = max(
            0,
            now()->diffInMinutes($endTime, false)
        );

        if ($remainingMinutes <= 0) {
            $delivery->update([
                'is_break' => 0,
                'break_started_at' => null,
                'break_time' => null,
            ]);

            $remainingMinutes = 0;
        }
    }

    $activeShiftLog = DeliveryShiftLog::where('delivery_user_id', $delivery->id)
        ->where('status', 'active')
        ->latest()
        ->first();

    $isInShift = $activeShiftLog ? true : false;
    $shiftStatus = $isInShift ? 'in_shift' : 'out_of_shift';

    return response()->json([
        'status' => true,
        'data' => [
            'is_break' => (bool) $delivery->is_break,
            'break_time' => $delivery->break_time,
            'remaining_minutes' => $remainingMinutes,
            'break_started_at' => $delivery->break_started_at,

            'shift' => [
                'id' => $delivery->shift?->id,
                'name' => $delivery->shift?->name,
                'from_time' => $delivery->shift?->from_time,
                'to_time' => $delivery->shift?->to_time,

                'is_in_shift' => $isInShift,
                'status' => $shiftStatus,

                'active_shift_log' => $activeShiftLog ? [
                    'id' => $activeShiftLog->id,
                    'start_time' => $activeShiftLog->start_time,
                    'start_lat' => $activeShiftLog->start_lat,
                    'start_lng' => $activeShiftLog->start_lng,
                    'status' => $activeShiftLog->status,
                ] : null,
            ],
        ]
    ]);
}
}
