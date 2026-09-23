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
use Carbon\Carbon;
use App\services\OrderNotificationService;
use App\services\FinancialTransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $delivery->load('level');

        /*
        |--------------------------------------------------------------------------
        | الحالات النشطة
        |--------------------------------------------------------------------------
        */

        $activeStatuses = [
            'accepted',
            'picked_up',
            'on_the_way',
        ];

        $activeOrdersCount = DeliveryOrder::query()
            ->where('delivery_user_id', $delivery->id)
            ->whereIn('status', $activeStatuses)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | حساب الكاش الحالي
        |--------------------------------------------------------------------------
        */

        $cashLimit = (float) optional($delivery->level)->cash_money;

        $currentCash = DeliveryOrder::query()
            ->where('delivery_user_id', $delivery->id)
            ->where('status', 'delivered')
            ->where('cash_settled', false)
            ->whereHas('order', function ($query) {
                $query->where('payment_method', 'cash');
            })
            ->with('order')
            ->get()
            ->sum(function (DeliveryOrder $deliveryOrder) {
                return (float) optional($deliveryOrder->order)->total;
            });

        /*
        |--------------------------------------------------------------------------
        | جلب طلبات الدليفري
        |--------------------------------------------------------------------------
        */

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
                'rejected',
                'transferred',
                'picked_up',
                'on_the_way',
                'delivered',
                'cancelled',
            ])
            ->latest('updated_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | تعديل status الظاهر في الـ response فقط
        |--------------------------------------------------------------------------
        |
        | الـ Resource يعرض order.status من جدول orders.
        | في حالة رفض أو تحويل أو إلغاء الدليفري، نعرض حالة delivery_orders
        | بدل حالة الطلب العامة.
        |
        | setAttribute لا ينفذ UPDATE في قاعدة البيانات.
        |--------------------------------------------------------------------------
        */

        $orders->each(function (DeliveryOrder $deliveryOrder) {
            if (!$deliveryOrder->order) {
                return;
            }

            if (
                in_array(
                    $deliveryOrder->status,
                    [
                        'rejected',
                        'transferred',
                        'cancelled',
                    ],
                    true
                )
            ) {
                $deliveryOrder->order->setAttribute(
                    'status',
                    $deliveryOrder->status
                );
            }
        });

        $hasMultipleActiveOrders = $activeOrdersCount > 1;

        $request->attributes->set(
            'delivery_has_multiple_active_orders',
            $hasMultipleActiveOrders
        );

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'has_multiple_active_orders' => $hasMultipleActiveOrders,
                'active_orders_count' => $activeOrdersCount,

                'cash_filter' => [
                    'current_cash' => $currentCash,
                    'cash_limit' => $cashLimit,
                    'is_full_limit' => $cashLimit > 0
                        && $currentCash >= $cashLimit,
                    'must_deposit_cash' => $cashLimit > 0
                        && $currentCash >= $cashLimit,
                ],

                'orders_details' => DeliveryOrderResource::collection($orders),
            ],
        ]);
    }


    public function updateStatus(
        Request $request,
        Order $order,
        FinancialTransactionService $financialService
      ) {
        $delivery = $request->user();

        $deliveryOrder = DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', $delivery->id)
            ->first();

        if (!$deliveryOrder) {
            return response()->json([
                'status' => false,
                'message' => 'You must accept this order first',
                'order_status' => $order->status,
            ], 400);
        }

            /*
        |--------------------------------------------------------------------------
        | 1) Received from kitchen
        |--------------------------------------------------------------------------
        */

        if ($deliveryOrder->status === 'accepted') {
            if ($order->status !== 'accepted_by_delivery') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid order status',
                    'order_status' => $order->status,
                ], 400);
            }

            DB::transaction(function () use (
                $deliveryOrder,
                $order
            ) {
                $lockedDeliveryOrder = DeliveryOrder::query()
                    ->where('id', $deliveryOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedOrder = Order::query()
                    ->where('id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedDeliveryOrder->status !== 'accepted') {
                    throw new RuntimeException(
                        'Invalid delivery order status'
                    );
                }

                if ($lockedOrder->status !== 'accepted_by_delivery') {
                    throw new RuntimeException(
                        'Invalid order status'
                    );
                }

                $lockedDeliveryOrder->update([
                    'status' => 'picked_up',
                ]);

                $lockedOrder->update([
                    'status' => 'received_by_delivery',
                ]);
            });

            return response()->json([
                'status' => true,
                'message' => 'Order received by delivery',
                'delivery_order_status' => 'picked_up',
                'order_status' => 'received_by_delivery',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2) Delivered
        |--------------------------------------------------------------------------
        */

        if ($deliveryOrder->status === 'picked_up') {
            if ($order->status !== 'received_by_delivery') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid order status',
                    'order_status' => $order->status,
                ], 400);
            }

            try {
                DB::transaction(function () use (
                    $deliveryOrder,
                    $order,
                    $delivery,
                    $financialService
                ) {
                    $lockedDeliveryOrder = DeliveryOrder::query()
                        ->where('id', $deliveryOrder->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $lockedOrder = Order::query()
                        ->where('id', $order->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedDeliveryOrder->status !== 'picked_up') {
                        throw new RuntimeException(
                            'Invalid delivery order status'
                        );
                    }

                    if ($lockedOrder->status !== 'received_by_delivery') {
                        throw new RuntimeException(
                            'Invalid order status'
                        );
                    }

                    $lockedDeliveryOrder->update([
                        'status' => 'delivered',
                    ]);

                    $orderUpdateData = [
                        'status' => 'delivered',
                        'delivered_at' => now(),
                        'receive_date' => now()->toDateString(),
                        'receive_time' => now()->toTimeString(),
                    ];

                    /*
                |--------------------------------------------------------------------------
                | Confirm cash payment collection
                |--------------------------------------------------------------------------
                |
                | في حالة أن العميل اختار الدفع كاش:
                | يتم اعتبار المبلغ محصلًا عند تسليم الأوردر.
                |
                */

                    if (
                        $lockedOrder->payment_method === 'cash'
                        && $lockedOrder->payment_status === 'cash_pending'
                    ) {
                        $orderUpdateData['payment_status'] = 'paid';
                        $orderUpdateData['paid_at'] = now();
                    }

                    $lockedOrder->update($orderUpdateData);

                    /*
                |--------------------------------------------------------------------------
                | Record financial transactions
                |--------------------------------------------------------------------------
                |
                | عند التسليم يتم:
                |
                | 1. تسجيل تحصيل الأونر لو الدفع Cash.
                | 2. تسجيل خصم الكاش كود إن وجد.
                | 3. تسجيل مستحق المطبخ.
                | 4. تسجيل مستحق الدليفري.
                |
                | لو أي خطوة مالية فشلت، يتم Rollback لكل التحديثات،
                | وبالتالي الأوردر لن يتحول إلى delivered بدون تسوية مالية.
                |
                */

                    $financialService->settleDeliveredOrder(
                        $lockedOrder->fresh(),
                        (int) $delivery->id
                    );

                    OrderHistory::updateOrCreate(
                        [
                            'order_id' => $lockedOrder->id,
                        ],
                        [
                            'status' => 'delivered',
                        ]
                    );
                });

                $order->refresh();
                $deliveryOrder->refresh();

                $cashInfo = $this->getDeliveryCashInfo(
                    $delivery
                );

                return response()->json([
                    'status' => true,
                    'message' => 'Order delivered',
                    'delivery_order_status' => 'delivered',
                    'order_status' => 'delivered',
                    'cash_filter' => $cashInfo,
                ]);
            } catch (Throwable $exception) {
                Log::error(
                    'Deliver order settlement failed',
                    [
                        'order_id' => $order->id,
                        'delivery_id' => $delivery->id,
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'exception_class' => get_class($exception),
                    ]
                );

                return response()->json([
                    'status' => false,
                    'message' => 'Unable to deliver order',
                    'error' => $exception->getMessage(),
                    'debug' => [
                        'exception' => get_class($exception),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                    ],
                ], 500);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Order already completed',
            'delivery_order_status' => $deliveryOrder->status,
            'order_status' => $order->status,
        ], 400);
    }




    public function accept(
        Request $request,
        Order $order,
        OrderNotificationService $notificationService
    ) {
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

        if ($delivery_order) {
            $delivery_order->update([
                'status' => 'accepted',
                'rejected_at' => null,
                'cash_settled' => false,
            ]);
        } else {
            $delivery->orders()->attach($order->id, [
                'status' => 'accepted',
                'rejected_at' => null,
                'cash_settled' => false,
            ]);
        }

        $order->update([
            'status' => 'accepted_by_delivery'
        ]);

        $order->refresh();


        DeliveryOrder::where('order_id', $order->id)
            ->where('delivery_user_id', '!=', $delivery->id)
            ->where('status', 'pending')
            ->get()
            ->each(function ($otherDeliveryOrder) {
                $otherDeliveryOrder->update([
                    'status' => 'transferred',
                    'transferred_at' => now(),
                ]);

                $otherDeliveryOrder->deliveryUser?->registerFail();
            });

        $delivery->registerSuccess();

        $notificationService->notifyKitchenDeliveryAccepted(
            $order,
            $delivery
        );

        $notificationService->notifyClientStatusChanged(
            $order,
            'accepted_by_delivery'
        );

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




    public function reject(
        Request $request,
        Order $order,
        OrderNotificationService $notificationService
    ) {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $result = DB::transaction(function () use (
                $order,
                $delivery
            ) {
                /*
            |--------------------------------------------------------------------------
            | Get delivery order and lock it
            |--------------------------------------------------------------------------
            */

                $deliveryOrder = DeliveryOrder::query()
                    ->where('order_id', $order->id)
                    ->where(
                        'delivery_user_id',
                        $delivery->id
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$deliveryOrder) {
                    throw new \RuntimeException(
                        'This order is not assigned to you.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Validate current assignment status
            |--------------------------------------------------------------------------
            */

                if ($deliveryOrder->status === 'rejected') {
                    throw new \RuntimeException(
                        'You already rejected this order.'
                    );
                }

                if (
                    !in_array(
                        $deliveryOrder->status,
                        [
                            'pending',
                            'accepted',
                        ],
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'This order cannot be rejected in its current status.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Mark current delivery as rejected
            |--------------------------------------------------------------------------
            */

                $deliveryOrder->update([
                    'status' => 'rejected',
                ]);


                $delivery->registerFail();
                /*
            |--------------------------------------------------------------------------
            | Assign order to next nearest delivery
            |--------------------------------------------------------------------------
            */

                $freshOrder = $order->fresh();

                $assignmentResult =
                    $this->assignOrderToNearestDelivery(
                        $freshOrder,
                        $delivery->id
                    );

                return [
                    'order' => $freshOrder->fresh(),

                    'assignment_result' =>
                    $assignmentResult,
                ];
            });

            /*
        |--------------------------------------------------------------------------
        | Extract assignment result
        |--------------------------------------------------------------------------
        */

            $freshOrder = $result['order'];

            $assignmentResult =
                $result['assignment_result'];

            $newDelivery =
                $assignmentResult['delivery'];

            $failureReason =
                $assignmentResult['failure_reason'];

            $failureDetails =
                $assignmentResult['failure_details'];

            /*
        |--------------------------------------------------------------------------
        | Notify kitchen
        |--------------------------------------------------------------------------
        */

            $notificationService
                ->notifyKitchenDeliveryRejected(
                    $freshOrder,
                    $delivery,
                    $newDelivery
                );

            /*
        |--------------------------------------------------------------------------
        | Notify new delivery
        |--------------------------------------------------------------------------
        */

            if ($newDelivery) {
                $notificationService
                    ->notifyDeliveryNewOrder(
                        $freshOrder,
                        $newDelivery
                    );
            }

            /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

            return response()->json([
                'status' => true,

                'message' => $newDelivery
                    ? 'You rejected the order and it was assigned to another delivery.'
                    : 'You rejected the order, but no available delivery was found.',

                'reassigned' => (bool) $newDelivery,

                'new_delivery_id' =>
                $newDelivery?->id,

                'failure_reason' =>
                $newDelivery
                    ? null
                    : $failureReason,

                'failure_details' =>
                $newDelivery
                    ? null
                    : $failureDetails,
            ]);
        } catch (\RuntimeException $exception) {
            $message = $exception->getMessage();

            $statusCode = match ($message) {
                'This order is not assigned to you.' => 404,
                'You already rejected this order.' => 400,
                default => 422,
            };

            return response()->json([
                'status' => false,
                'message' => $message,
            ], $statusCode);
        } catch (\Throwable $exception) {
            Log::error(
                'Reject delivery order failed',
                [
                    'order_id' => $order->id,
                    'delivery_id' => $delivery->id,
                    'message' => $exception->getMessage(),
                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,
                'message' => 'Unable to reject order.',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }


    public function transfer(
        Request $request,
        Order $order,
        OrderNotificationService $notificationService
    ) {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $result = DB::transaction(function () use (
                $order,
                $delivery
            ) {
                /*
            |--------------------------------------------------------------------------
            | Get current delivery assignment
            |--------------------------------------------------------------------------
            */

                $deliveryOrder = DeliveryOrder::query()
                    ->where('order_id', $order->id)
                    ->where(
                        'delivery_user_id',
                        $delivery->id
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$deliveryOrder) {
                    throw new \RuntimeException(
                        'This order is not assigned to you.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Validate assignment status
            |--------------------------------------------------------------------------
            */

                if (
                    !in_array(
                        $deliveryOrder->status,
                        [
                            'pending',


                        ],
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'This order cannot be transferred.'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Mark current delivery as transferred
            |--------------------------------------------------------------------------
            */

                $deliveryOrder->update([
                    'status' => 'transferred',
                ]);

                /*
            |--------------------------------------------------------------------------
            | Assign order to next nearest delivery
            |--------------------------------------------------------------------------
            */

                $freshOrder = $order->fresh();

                $assignmentResult =
                    $this->assignOrderToNearestDelivery(
                        $freshOrder,
                        $delivery->id
                    );

                return [
                    'order' => $freshOrder->fresh(),
                    'assignment_result' =>
                    $assignmentResult,
                ];
            });

            /*
        |--------------------------------------------------------------------------
        | Extract assignment result
        |--------------------------------------------------------------------------
        */

            $freshOrder = $result['order'];

            $assignmentResult =
                $result['assignment_result'];

            $newDelivery =
                $assignmentResult['delivery'];

            $failureReason =
                $assignmentResult['failure_reason'];

            $failureDetails =
                $assignmentResult['failure_details'];

            /*
        |--------------------------------------------------------------------------
        | Notify new delivery
        |--------------------------------------------------------------------------
        */

            if ($newDelivery) {
                $notificationService
                    ->notifyDeliveryNewOrder(
                        $freshOrder,
                        $newDelivery
                    );
            }

            /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

            return response()->json([
                'status' => true,

                'message' => $newDelivery
                    ? 'Order transferred successfully.'
                    : 'Order transferred, but no available delivery was found.',

                'reassigned' => (bool) $newDelivery,

                'new_delivery_id' =>
                $newDelivery?->id,

                'failure_reason' =>
                $newDelivery
                    ? null
                    : $failureReason,

                'failure_details' =>
                $newDelivery
                    ? null
                    : $failureDetails,
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error(
                'Transfer delivery order failed',
                [
                    'order_id' => $order->id,
                    'delivery_id' => $delivery->id,
                    'message' => $exception->getMessage(),
                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,
                'message' => 'Unable to transfer order.',
                'error' => $exception->getMessage(),
            ], 500);
        }
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

        $assignmentResult =
            $this->assignOrderToNearestDelivery(
                $order,
                $delivery->id
            );

        $newDelivery =
            $assignmentResult['delivery'];

        return response()->json([
            'status' => true,

            'message' => $newDelivery
                ? 'Order reassigned because delivery did not respond.'
                : 'No available delivery was found.',

            'reassigned' => (bool) $newDelivery,

            'new_delivery_id' =>
            $newDelivery?->id,

            'failure_reason' =>
            $assignmentResult['failure_reason'],

            'failure_details' =>
            $assignmentResult['failure_details'],
        ]);
    }

    // private function assignOrderToNearestDelivery(Order $order, ?int $excludeDeliveryId = null)
    // {
    //     $order->load('kitchen');

    //     $lat = $order->kitchen->lat ?? null;
    //     $lng = $order->kitchen->lng ?? null;

    //     if (!$lat || !$lng) {
    //         return null;
    //     }

    //     $rejectedDeliveryIds = DeliveryOrder::where('order_id', $order->id)
    //         ->whereIn('status', ['rejected', 'transferred'])
    //         ->pluck('delivery_user_id')
    //         ->toArray();

    //     if ($excludeDeliveryId) {
    //         $rejectedDeliveryIds[] = $excludeDeliveryId;
    //     }

    //     $delivery = DeliveryUser::query()
    //         ->whereNotNull('lat')
    //         ->whereNotNull('lng')
    //         ->where('status', 'active')
    //         ->where('is_break', 0)
    //         ->whereNotIn('id', array_unique($rejectedDeliveryIds))
    //         ->select('*')
    //         ->selectRaw(
    //             '(6371 * acos(
    //             cos(radians(?)) *
    //             cos(radians(lat)) *
    //             cos(radians(lng) - radians(?)) +
    //             sin(radians(?)) *
    //             sin(radians(lat))
    //         )) AS distance',
    //             [$lat, $lng, $lat]
    //         )
    //         ->orderBy('distance')
    //         ->first();

    //     if (!$delivery) {
    //         return null;
    //     }

    //     DeliveryOrder::updateOrCreate(
    //         [
    //             'order_id' => $order->id,
    //             'delivery_user_id' => $delivery->id,
    //         ],
    //         [
    //             'status' => 'pending',
    //             'rejected_at' => null,
    //             'cash_settled' => false,
    //         ]
    //     );

    //     $order->update([
    //         'status' => 'pending_delivery',
    //     ]);

    //     return $delivery;
    // }

    private function assignOrderToNearestDelivery(
        Order $order,
        ?int $excludeDeliveryId = null
    ): array {
        $locationFreshnessMinutes = 3;
        /*
            |--------------------------------------------------------------------------
            | عنوان المطبخ
            |--------------------------------------------------------------------------
            */

        $order->load('kitchen.user.defaultAddress');

        $kitchenAddress =
            $order->kitchen?->user?->defaultAddress;

        if (!$kitchenAddress) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'Kitchen address was not found.',

                'failure_details' => [
                    'order_id' => $order->id,
                    'kitchen_id' => $order->kitchen_id,
                ],
            ];
        }

        if (
            $kitchenAddress->lat === null ||
            $kitchenAddress->lng === null
        ) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'Kitchen coordinates were not found.',

                'failure_details' => [
                    'order_id' => $order->id,
                    'kitchen_id' => $order->kitchen_id,
                    'kitchen_lat' =>
                    $kitchenAddress->lat,
                    'kitchen_lng' =>
                    $kitchenAddress->lng,
                ],
            ];
        }

        $lat = (float) $kitchenAddress->lat;
        $lng = (float) $kitchenAddress->lng;

        /*
        |--------------------------------------------------------------------------
        | الدليفريين المستبعدين
        |--------------------------------------------------------------------------
        */

        $excludedDeliveryIds = DeliveryOrder::query()
            ->where('order_id', $order->id)
            ->whereIn('status', [
                'rejected',
                'transferred',
            ])
            ->pluck('delivery_user_id')
            ->map(
                fn($id) => (int) $id
            )
            ->toArray();

        if ($excludeDeliveryId !== null) {
            $excludedDeliveryIds[] =
                (int) $excludeDeliveryId;
        }

        $excludedDeliveryIds = array_values(
            array_unique($excludedDeliveryIds)
        );

        /*
        |--------------------------------------------------------------------------
        | أحدث شيفت نشط
        |--------------------------------------------------------------------------
        */

        $latestActiveShiftIds = DB::table(
            'delivery_shift_logs'
        )
            ->selectRaw('MAX(id)')
            ->where('status', 'active')
            ->whereNull('end_time')
            ->groupBy('delivery_user_id');

        /*
        |--------------------------------------------------------------------------
        | فحص مراحل استبعاد الدليفريين
        |--------------------------------------------------------------------------
        */

        $activeShiftDeliveries =
            DeliveryUser::query()
            ->join(
                'delivery_shift_logs',
                'delivery_shift_logs.delivery_user_id',
                '=',
                'delivery_users.id'
            )
            ->whereIn(
                'delivery_shift_logs.id',
                $latestActiveShiftIds
            )
            ->select([
                'delivery_users.id',
                'delivery_users.status',
                'delivery_users.is_break',

                'delivery_shift_logs.id as shift_log_id',
                'delivery_shift_logs.status as shift_status',
                'delivery_shift_logs.start_lat',
                'delivery_shift_logs.start_lng',
                'delivery_shift_logs.end_lat',
                'delivery_shift_logs.end_lng',
                'delivery_shift_logs.updated_at as shift_log_updated_at',
            ])
            ->get();

        if ($activeShiftDeliveries->isEmpty()) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'No delivery has an active shift.',

                'failure_details' => [
                    'excluded_delivery_ids' =>
                    $excludedDeliveryIds,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | فحص حالة حساب الدليفري
        |--------------------------------------------------------------------------
        */

        $statusAvailableDeliveries =
            $activeShiftDeliveries
            ->filter(function ($delivery) {
                return in_array(
                    $delivery->status,
                    [
                        'active',
                        'approved',
                    ],
                    true
                );
            })
            ->values();

        if ($statusAvailableDeliveries->isEmpty()) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'Deliveries have active shifts, but their account status is not available.',

                'failure_details' => [
                    'required_statuses' => [
                        'active',
                        'approved',
                    ],

                    'deliveries' =>
                    $activeShiftDeliveries
                        ->map(function ($delivery) {
                            return [
                                'id' => $delivery->id,
                                'status' =>
                                $delivery->status,
                            ];
                        })
                        ->values(),
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | فحص الراحة
        |--------------------------------------------------------------------------
        */

        $notOnBreakDeliveries =
            $statusAvailableDeliveries
            ->filter(function ($delivery) {
                if ((int) $delivery->is_break === 0) {
                    return true;
                }

                return $delivery->break_started_at
                    && now()->greaterThanOrEqualTo(
                        \Carbon\Carbon::parse($delivery->break_started_at)
                            ->addMinutes($delivery->break_time ?: 15)
                    );
            })
            ->values();

        if ($notOnBreakDeliveries->isEmpty()) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'All available deliveries are currently on break.',

                'failure_details' => [
                    'deliveries' =>
                    $statusAvailableDeliveries
                        ->map(function ($delivery) {
                            return [
                                'id' => $delivery->id,
                                'is_break' =>
                                $delivery->is_break,
                            ];
                        })
                        ->values(),
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | فحص الإحداثيات
        |--------------------------------------------------------------------------
        */

        $locationFreshnessMinutes = 3;
        $deliveriesWithCoordinates =
            $notOnBreakDeliveries
            ->filter(function ($delivery) use ($locationFreshnessMinutes) {
                $currentLat =
                    $delivery->end_lat ??
                    $delivery->start_lat;

                $currentLng =
                    $delivery->end_lng ??
                    $delivery->start_lng;

                if ($currentLat === null || $currentLng === null) {
                    return false;
                }

                if (!$delivery->shift_log_updated_at) {
                    return false;
                }

                return \Carbon\Carbon::parse($delivery->shift_log_updated_at)
                    ->greaterThanOrEqualTo(now()->subMinutes($locationFreshnessMinutes));
            })
            ->values();

        if ($deliveriesWithCoordinates->isEmpty()) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'Available deliveries do not have current coordinates.',

                'failure_details' => [
                    'deliveries' =>
                    $notOnBreakDeliveries
                        ->map(function ($delivery) {
                            return [
                                'id' => $delivery->id,

                                'start_lat' =>
                                $delivery->start_lat,

                                'start_lng' =>
                                $delivery->start_lng,

                                'end_lat' =>
                                $delivery->end_lat,

                                'end_lng' =>
                                $delivery->end_lng,
                            ];
                        })
                        ->values(),
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | فحص المستبعدين
        |--------------------------------------------------------------------------
        */

        $deliveriesAfterExclusion =
            $deliveriesWithCoordinates
            ->reject(function ($delivery) use (
                $excludedDeliveryIds
            ) {
                return in_array(
                    (int) $delivery->id,
                    $excludedDeliveryIds,
                    true
                );
            })
            ->values();

        if ($deliveriesAfterExclusion->isEmpty()) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'All available deliveries were excluded because they rejected or transferred this order before.',

                'failure_details' => [
                    'excluded_delivery_ids' =>
                    $excludedDeliveryIds,

                    'available_delivery_ids' =>
                    $deliveriesWithCoordinates
                        ->pluck('id')
                        ->map(
                            fn($id) => (int) $id
                        )
                        ->values(),
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Query اختيار الأقرب
        |--------------------------------------------------------------------------
        */

        $deliveryQuery = DeliveryUser::query()
            ->join(
                'delivery_shift_logs',
                'delivery_shift_logs.delivery_user_id',
                '=',
                'delivery_users.id'
            )
            ->whereIn(
                'delivery_shift_logs.id',
                $latestActiveShiftIds
            )
            ->whereIn(
                'delivery_users.status',
                [
                    'active',
                    'approved',
                ]
            )
            ->where(function ($q) {
                $q->where('delivery_users.is_break', 0)
                    ->orWhereRaw(
                        'delivery_users.break_started_at IS NOT NULL AND TIMESTAMPDIFF(MINUTE, delivery_users.break_started_at, NOW()) >= delivery_users.break_time'
                    );
            })

            ->where(
                'delivery_shift_logs.updated_at',
                '>=',
                now()->subMinutes($locationFreshnessMinutes)   // ⬅️ جديد، نفس المتغير من فوق
            )
            ->whereNotNull(
                DB::raw(
                    'COALESCE(
                    delivery_shift_logs.end_lat,
                    delivery_shift_logs.start_lat
                )'
                )
            )
            ->whereNotNull(
                DB::raw(
                    'COALESCE(
                    delivery_shift_logs.end_lng,
                    delivery_shift_logs.start_lng
                )'
                )
            );

        if (!empty($excludedDeliveryIds)) {
            $deliveryQuery->whereNotIn(
                'delivery_users.id',
                $excludedDeliveryIds
            );
        }

        $delivery = $deliveryQuery
            ->select('delivery_users.*')
            ->selectRaw(
                '
            COALESCE(
                delivery_shift_logs.end_lat,
                delivery_shift_logs.start_lat
            ) AS current_lat
            '
            )
            ->selectRaw(
                '
            COALESCE(
                delivery_shift_logs.end_lng,
                delivery_shift_logs.start_lng
            ) AS current_lng
            '
            )
            ->selectRaw(
                '
            (
                6371 * ACOS(
                    LEAST(
                        1,
                        GREATEST(
                            -1,
                            COS(RADIANS(?))
                            * COS(
                                RADIANS(
                                    COALESCE(
                                        delivery_shift_logs.end_lat,
                                        delivery_shift_logs.start_lat
                                    )
                                )
                            )
                            * COS(
                                RADIANS(
                                    COALESCE(
                                        delivery_shift_logs.end_lng,
                                        delivery_shift_logs.start_lng
                                    )
                                ) - RADIANS(?)
                            )
                            + SIN(RADIANS(?))
                            * SIN(
                                RADIANS(
                                    COALESCE(
                                        delivery_shift_logs.end_lat,
                                        delivery_shift_logs.start_lat
                                    )
                                )
                            )
                        )
                    )
                )
            ) AS distance
            ',
                [
                    $lat,
                    $lng,
                    $lat,
                ]
            )
            ->orderBy('distance', 'asc')
            ->orderBy(
                'delivery_users.id',
                'asc'
            )
            ->first();

        if (!$delivery) {
            return [
                'delivery' => null,

                'failure_reason' =>
                'Deliveries passed the availability checks, but the nearest delivery query returned no result.',

                'failure_details' => [
                    'excluded_delivery_ids' =>
                    $excludedDeliveryIds,

                    'candidate_delivery_ids' =>
                    $deliveriesAfterExclusion
                        ->pluck('id')
                        ->map(
                            fn($id) => (int) $id
                        )
                        ->values(),

                    'kitchen_lat' => $lat,
                    'kitchen_lng' => $lng,
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | إنشاء الإسناد
        |--------------------------------------------------------------------------
        */

        DeliveryOrder::query()->updateOrCreate(
            [
                'order_id' => $order->id,

                'delivery_user_id' =>
                $delivery->id,
            ],
            [
                'status' => 'pending',
                'cash_settled' => false,
            ]
        );

        if ($order->status !== 'ready_to_deliver') {
            $order->update([
                'status' => 'ready_to_deliver',
            ]);
        }

        return [
            'delivery' => $delivery,
            'failure_reason' => null,
            'failure_details' => null,
        ];
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







    public function weeklyReports(Request $request)
    {
        $delivery = $request->user();

        $date = $request->date ? Carbon::parse($request->date) : now();

        $startOfWeek = $date->copy()->startOfWeek(Carbon::SATURDAY);
        $endOfWeek = $date->copy()->endOfWeek(Carbon::FRIDAY);

        $deliveryOrders = DeliveryOrder::with(['order.userAddress'])
            ->where('delivery_user_id', $delivery->id)
            ->whereBetween('updated_at', [
                $startOfWeek->copy()->startOfDay(),
                $endOfWeek->copy()->endOfDay(),
            ])
            ->get();

        $deliveredOrders = $deliveryOrders->where('status', 'delivered');

        $deliveredCount = $deliveredOrders->count();

        $earnings = $deliveredOrders->sum(function ($deliveryOrder) {
            return (float) optional($deliveryOrder->order)->total;
        });

        $cancelledOrders = $deliveryOrders->where('status', 'cancelled');

        $lateOrders = $deliveredOrders->filter(function ($deliveryOrder) {
            $order = $deliveryOrder->order;

            if (!$order || !$order->receive_date || !$order->receive_time || !$order->delivered_at) {
                return false;
            }

            $expectedTime = Carbon::parse($order->receive_date . ' ' . $order->receive_time);
            $deliveredAt = Carbon::parse($order->delivered_at);

            return $deliveredAt->gt($expectedTime);
        });

        $onTimeOrders = $deliveredOrders->filter(function ($deliveryOrder) use ($lateOrders) {
            return !$lateOrders->contains('id', $deliveryOrder->id);
        });

        $onTimeCount = $onTimeOrders->count();
        $cancelledCount = $cancelledOrders->count();
        $lateCount = $lateOrders->count();

        $totalForChart = $onTimeCount + $cancelledCount + $lateCount;

        $formatOrders = function ($orders) {
            return $orders->map(function ($deliveryOrder) {
                $order = $deliveryOrder->order;

                return [
                    'order_id' => $order?->id,
                    'order_number' => $order?->number,
                    'date' => optional($order?->created_at)->toDateString(),
                    'time' => optional($order?->created_at)->format('H:i'),
                    'address' => $order?->userAddress?->full_address,
                    'phone' => $order?->userAddress?->phone,
                    'location_link' => $order?->userAddress?->location_link,
                    'lat' => $order?->userAddress?->lat,
                    'lng' => $order?->userAddress?->lng,
                    'status' => $deliveryOrder->status,
                    'order_status' => $order?->status,
                    'total' => (float) ($order?->total ?? 0),
                ];
            })->values();
        };

        return response()->json([
            'status' => true,
            'data' => [
                'week' => [
                    'from' => $startOfWeek->toDateString(),
                    'to' => $endOfWeek->toDateString(),
                ],

                'delivered_orders' => $deliveredCount,
                'earnings_so_far' => round($earnings, 2),

                'chart' => [
                    'on_time_orders' => [
                        'count' => $onTimeCount,
                        'percentage' => $totalForChart > 0 ? round(($onTimeCount / $totalForChart) * 100) : 0,
                    ],
                    'cancelled_orders' => [
                        'count' => $cancelledCount,
                        'percentage' => $totalForChart > 0 ? round(($cancelledCount / $totalForChart) * 100) : 0,
                    ],
                    'late_orders' => [
                        'count' => $lateCount,
                        'percentage' => $totalForChart > 0 ? round(($lateCount / $totalForChart) * 100) : 0,
                    ],
                ],

                'details' => [
                    'on_time_orders' => $formatOrders($onTimeOrders),
                    'cancelled_orders' => $formatOrders($cancelledOrders),
                    'late_orders' => $formatOrders($lateOrders),
                ],
            ],
        ]);
    }















    public function debugAssignment(Request $request, Order $order)
{
    $locationFreshnessMinutes = 3;
    $debugNow = now();

    $order->load('kitchen.user.defaultAddress');
    $kitchenAddress = $order->kitchen?->user?->defaultAddress;

    if (!$kitchenAddress || $kitchenAddress->lat === null || $kitchenAddress->lng === null) {
        return response()->json([
            'status' => false,
            'message' => 'Kitchen address/coordinates not found.',
        ], 422);
    }

    $lat = (float) $kitchenAddress->lat;
    $lng = (float) $kitchenAddress->lng;

    $excludedDeliveryIds = DeliveryOrder::query()
        ->where('order_id', $order->id)
        ->whereIn('status', ['rejected', 'transferred'])
        ->pluck('delivery_user_id')
        ->map(fn($id) => (int) $id)
        ->toArray();

    $latestActiveShiftIds = DB::table('delivery_shift_logs')
        ->selectRaw('MAX(id)')
        ->where('status', 'active')
        ->whereNull('end_time')
        ->groupBy('delivery_user_id');

    $candidates = DeliveryUser::query()
        ->join(
            'delivery_shift_logs',
            'delivery_shift_logs.delivery_user_id',
            '=',
            'delivery_users.id'
        )
        ->whereIn('delivery_shift_logs.id', $latestActiveShiftIds)
        ->select([
            'delivery_users.id',
            'delivery_users.status',
            'delivery_users.is_break',
            'delivery_users.break_started_at',
            'delivery_users.break_time',
            'delivery_shift_logs.id as shift_log_id',
            'delivery_shift_logs.start_lat',
            'delivery_shift_logs.start_lng',
            'delivery_shift_logs.end_lat',
            'delivery_shift_logs.end_lng',
            'delivery_shift_logs.updated_at as shift_log_updated_at',
        ])
        ->get();

    $report = $candidates->map(function ($delivery) use (
        $lat,
        $lng,
        $excludedDeliveryIds,
        $locationFreshnessMinutes,
        $debugNow
    ) {
        $currentLat = $delivery->end_lat ?? $delivery->start_lat;
        $currentLng = $delivery->end_lng ?? $delivery->start_lng;

        $reasons = [];

        // فحص الحالة
        $statusOk = in_array($delivery->status, ['active', 'approved'], true);
        if (!$statusOk) {
            $reasons[] = "status='{$delivery->status}' غير متاح";
        }

        // فحص البريك
        $breakOk = true;
        if ((int) $delivery->is_break === 1) {
            $breakEndsAt = $delivery->break_started_at
                ? \Carbon\Carbon::parse($delivery->break_started_at)->addMinutes($delivery->break_time ?: 15)
                : null;

            $breakOk = $breakEndsAt && $debugNow->greaterThanOrEqualTo($breakEndsAt);

            if (!$breakOk) {
                $reasons[] = "is_break=1، البريك بينتهي الساعة "
                    . ($breakEndsAt?->toDateTimeString() ?? 'غير معروف');
            }
        }

        // فحص الإحداثيات
        $hasCoordinates = $currentLat !== null && $currentLng !== null;
        if (!$hasCoordinates) {
            $reasons[] = 'مفيش إحداثيات (lat/lng) خالص';
        }

        // فحص حداثة الموقع
        $isFresh = false;
        $minutesSinceUpdate = null;
        if ($delivery->shift_log_updated_at) {
            $updatedAt = \Carbon\Carbon::parse($delivery->shift_log_updated_at);
            $minutesSinceUpdate = round($updatedAt->diffInSeconds($debugNow) / 60, 1);
            $isFresh = $updatedAt->greaterThanOrEqualTo($debugNow->copy()->subMinutes($locationFreshnessMinutes));
        }
        if (!$isFresh) {
            $reasons[] = "آخر تحديث موقع من {$minutesSinceUpdate} دقيقة (المسموح: {$locationFreshnessMinutes} دقيقة)";
        }

        // فحص الاستبعاد
        $isExcluded = in_array((int) $delivery->id, $excludedDeliveryIds, true);
        if ($isExcluded) {
            $reasons[] = 'مستبعد لأنه رفض/حوّل الأوردر ده قبل كده';
        }

        // حساب المسافة (لو فيه إحداثيات)
        $distance = null;
        if ($hasCoordinates) {
            $distance = 6371 * acos(max(-1, min(1,
                cos(deg2rad($lat)) * cos(deg2rad((float) $currentLat))
                * cos(deg2rad((float) $currentLng) - deg2rad($lng))
                + sin(deg2rad($lat)) * sin(deg2rad((float) $currentLat))
            )));
        }

        $eligible = $statusOk && $breakOk && $hasCoordinates && $isFresh && !$isExcluded;

        return [
            'delivery_id' => $delivery->id,
            'eligible' => $eligible,
            'reasons_if_not_eligible' => $reasons,
            'status' => $delivery->status,
            'is_break' => (bool) $delivery->is_break,
            'shift_log_updated_at' => $delivery->shift_log_updated_at,
            'minutes_since_location_update' => $minutesSinceUpdate,
            'current_lat' => $currentLat,
            'current_lng' => $currentLng,
            'distance_km' => $distance !== null ? round($distance, 3) : null,
        ];
    })
    ->sortBy(fn($d) => $d['distance_km'] ?? PHP_INT_MAX)
    ->values();

    $eligibleSorted = $report->filter(fn($d) => $d['eligible'])->values();

    return response()->json([
        'status' => true,
        'order_id' => $order->id,
        'kitchen_lat' => $lat,
        'kitchen_lng' => $lng,
        'server_now' => $debugNow->toDateTimeString(),
        'location_freshness_minutes' => $locationFreshnessMinutes,
        'excluded_delivery_ids' => $excludedDeliveryIds,
        'would_be_assigned_to' => $eligibleSorted->first()['delivery_id'] ?? null,
        'all_candidates_sorted_by_distance' => $report,
    ]);
}
}
