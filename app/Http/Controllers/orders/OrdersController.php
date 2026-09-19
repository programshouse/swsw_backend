<?php

namespace App\Http\Controllers\orders;

use App\Events\CreateOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemsResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\services\WalletService;
use Date;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderResource;
use App\Models\KitchenProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\DeliveryOrder;
use App\Models\DeliveryUser;
use App\Models\CashCode;
use App\Models\CashCodeUsage;
use App\Models\Meal;
use App\Models\PaymentTransaction;
use Illuminate\Validation\ValidationException;
use App\Models\UserAddress;
use App\Models\OrderFeeRule;
use App\services\OrderNotificationService;
use Illuminate\Support\Facades\Log;
use App\Services\KitchenPackageService;



class OrdersController extends Controller
{

    // public function __construct(
    //     protected KitchenPackageService $kitchenPackageService
    // ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // If user is a kitchen, show their orders
        if ($user->role === 'kitchen') {
            $orders = Order::where('kitchen_id', $user->profile->id)
                ->with(['items.meal', 'userAddress'])
                ->get();
        } else {
            // If user is a client, show their orders
            $orders = Order::where('user_id', $user->id)
                ->with(['kitchen', 'items.meal', 'userAddress'])
                ->get();
        }

        return response()->json([
            'orders' => $orders
        ]);
    }

    public function kitchen_orders(Request $request)
    {
        try {

            $kitchen = $request->user()->profile;

            $orders = Order::query()
                ->where('kitchen_id', $kitchen->id)
                ->with([
                    'items.meal',
                    'userAddress',
                ])
                ->latest('created_at')
                ->get();

            return response()->json([
                'orders' => OrderResource::collection($orders),
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function client_orders(Request $request)
    {
        // ->with('items.meal' , 'address', 'kitchen')
        $orders = Order::where('user_id', $request->user()->id)->get();
        return response()->json([
            'orders' => OrderResource::collection($orders)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kitchen_id' => [
                'required',
                'exists:kitchen_profiles,id',
            ],

            'address_id' => [
                'nullable',
                'integer',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.meal_id' => [
                'required',
                'exists:meals,id',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'receive_date' => [
                'nullable',
                'date',
                'before_or_equal:today + 7 days',
            ],

            'receive_time' => [
                'required_with:receive_date',
            ],

            'book_for_later' => [
                'required_with:receive_date',
                'boolean',
            ],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $settings = Setting::first();

        /*
        |--------------------------------------------------------------------------
        | Check application working time
        |--------------------------------------------------------------------------
        */

        $start = $settings?->work_start_time;
        $end = $settings?->work_end_time;

        if ($start && $end) {
            $now = Carbon::now(
                'Africa/Cairo'
            )->format('H:i:s');

            $isOpen = $start <= $end
                ? (
                    $now >= $start
                    && $now <= $end
                )
                : (
                    $now >= $start
                    || $now <= $end
                );

            if (!$isOpen) {
                return response()->json([
                    'status' => false,
                    'message' => 'Application is closed now',
                    'work_start_time' => $start,
                    'work_end_time' => $end,
                    'current_time' => $now,
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Kitchen
        |--------------------------------------------------------------------------
        */

        $kitchen = KitchenProfile::query()
            ->find($validated['kitchen_id']);

        if (!$kitchen) {
            return response()->json([
                'status' => false,
                'message' => 'Kitchen not found',
            ], 404);
        }

        $packageCheck = [
            'allowed' => true
        ];


        if (!$packageCheck['allowed']) {

            return response()->json([
                'status' => false,
                'message' => $packageCheck['message'],
            ], 422);
        }

        if (
            in_array(
                $kitchen->open_status,
                [
                    'closed',
                    'busy',
                ],
                true
            )
        ) {
            return response()->json([
                'status' => false,
                'message' =>
                'This kitchen may be closed or busy right now. You can order later.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Check meals and calculate subtotal
        |--------------------------------------------------------------------------
        */

        $subtotal = 0;
        $meals = [];

        foreach ($validated['items'] as $item) {
            $meal = \App\Models\Meal::query()
                ->find($item['meal_id']);

            if (!$meal) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'One of your items does not exist',
                ], 422);
            }

            if (!$meal->availability) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'One of your items is unavailable now',

                    'item' => [
                        'id' => $meal->id,
                        'name' => $meal->name,

                        'image' => $meal->image
                            ? config('app.url')
                            . '/storage/'
                            . $meal->image
                            : null,
                    ],
                ], 422);
            }

            $meals[$meal->id] = $meal;

            $subtotal +=
                (float) $meal->price
                * (int) $item['quantity'];
        }

        $subtotal = round($subtotal, 2);

        /*
        |--------------------------------------------------------------------------
        | Client address
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['address_id'])) {
            $clientAddress = UserAddress::query()
                ->where(
                    'id',
                    $validated['address_id']
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->first();

            if (!$clientAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'User address not found',
                ], 422);
            }
        } else {
            $clientAddress = UserAddress::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'is_default',
                    1
                )
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Kitchen address
        |--------------------------------------------------------------------------
        */

        $kitchen->load(
            'user.defaultAddress'
        );

        $kitchenAddress =
            $kitchen->user?->defaultAddress;

        /*
        |--------------------------------------------------------------------------
        | Calculate distance and delivery price
        |--------------------------------------------------------------------------
        */

        $distanceKm = 0;

        if (
            $clientAddress
            && $kitchenAddress
            && $clientAddress->lat !== null
            && $clientAddress->lng !== null
            && $kitchenAddress->lat !== null
            && $kitchenAddress->lng !== null
        ) {
            $distanceKm = $this->calculateDistance(
                (float) $clientAddress->lat,
                (float) $clientAddress->lng,
                (float) $kitchenAddress->lat,
                (float) $kitchenAddress->lng
            );
        }

        $deliveryMeterPrice = round(
            (float) (
                $settings?->delivery_meter_price
                ?? 0
            ),
            2
        );

        $deliveryPrice = round(
            $distanceKm * $deliveryMeterPrice,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | VAT
        |--------------------------------------------------------------------------
        */

        $vatPercentage = round(
            (float) (
                $settings?->vat_percentage
                ?? 0
            ),
            2
        );

        $vatValue = round(
            ($subtotal * $vatPercentage) / 100,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Order fee rule
        |--------------------------------------------------------------------------
        */

        $feeRule = \App\Models\OrderFeeRule::query()
            ->where('is_active', true)
            ->where(
                'min_order_amount',
                '<=',
                $subtotal
            )
            ->where(function ($query) use (
                $subtotal
            ) {
                $query
                    ->whereNull(
                        'max_order_amount'
                    )
                    ->orWhere(
                        'max_order_amount',
                        '>=',
                        $subtotal
                    );
            })
            ->orderByDesc(
                'min_order_amount'
            )
            ->first();

        $clientServiceFee = round(
            (float) (
                $feeRule?->client_service_fee
                ?? 0
            ),
            2
        );

        $kitchenServiceFee = round(
            (float) (
                $feeRule?->kitchen_service_fee
                ?? 0
            ),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Kitchen net amount
        |--------------------------------------------------------------------------
        */

        $kitchenNetAmount = round(
            max(
                $subtotal - $kitchenServiceFee,
                0
            ),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Customer total
        |--------------------------------------------------------------------------
        */

        $totalBeforeDiscount = round(
            $subtotal
                + $vatValue
                + $deliveryPrice
                + $clientServiceFee,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Create order
        |--------------------------------------------------------------------------
        */

        $order = DB::transaction(function () use (
            $validated,
            $user,
            $clientAddress,
            $subtotal,
            $vatPercentage,
            $vatValue,
            $distanceKm,
            $deliveryMeterPrice,
            $deliveryPrice,
            $feeRule,
            $clientServiceFee,
            $kitchenServiceFee,
            $kitchenNetAmount,
            $totalBeforeDiscount,
            $meals
        ) {
            $lastOrderId = Order::query()
                ->max('id');

            $nextId = ((int) $lastOrderId) + 1;

            $orderNumber =
                'ORD-'
                . now()->format('Ymd')
                . '-'
                . str_pad(
                    $nextId,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $order = Order::create([
                'user_id' =>
                $user->id,

                'kitchen_id' =>
                $validated['kitchen_id'],

                'number' =>
                $orderNumber,

                'subtotal' =>
                $subtotal,

                'vat_percentage' =>
                $vatPercentage,

                'vat_value' =>
                $vatValue,

                'distance_km' =>
                $distanceKm,

                'delivery_meter_price' =>
                $deliveryMeterPrice,

                'delivery_price' =>
                $deliveryPrice,

                'order_fee_rule_id' =>
                $feeRule?->id,

                'client_service_fee' =>
                $clientServiceFee,

                'kitchen_service_fee' =>
                $kitchenServiceFee,

                'kitchen_net_amount' =>
                $kitchenNetAmount,

                /*
             * لا يوجد خصم وقت إنشاء الطلب.
             */
                'total_before_discount' =>
                $totalBeforeDiscount,

                'discount_value' =>
                0,

                'total' =>
                $totalBeforeDiscount,

                'cash_code_id' =>
                null,

                'payment_method' =>
                null,

                'payment_status' =>
                'unpaid',

                'paid_at' =>
                null,

                'payment_reference' =>
                null,

                'payment_expires_at' =>
                null,

                'user_address_id' =>
                $clientAddress?->id
                    ?? (
                        $validated['address_id']
                        ?? null
                    ),

                'receive_date' =>
                $validated['receive_date']
                    ?? null,

                'receive_time' =>
                $validated['receive_time']
                    ?? null,

                'book_for_later' =>
                $validated['book_for_later']
                    ?? false,
            ]);

            foreach (
                $validated['items']
                as $item
            ) {
                $meal =
                    $meals[$item['meal_id']];

                OrderItem::create([
                    'order_id' =>
                    $order->id,

                    'meal_id' =>
                    $meal->id,

                    'quantity' =>
                    $item['quantity'],

                    'price' =>
                    $meal->price,
                ]);
            }

            return $order;
        });

        /*
        |--------------------------------------------------------------------------
        | Estimated time
        |--------------------------------------------------------------------------
        */

        $quantities = array_column(
            $validated['items'],
            'quantity'
        );

        $estimatedTime = !empty($quantities)
            ? max($quantities)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Load relations
        |--------------------------------------------------------------------------
        */

        $order->load([
            'user',
            'userAddress',
            'kitchen.user.defaultAddress',
            'items.meal',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        broadcast(
            new CreateOrder($order)
        );

        app(
            \App\services\OrderNotificationService::class
        )->notifyKitchenNewOrder($order);

        return response()->json([
            'status' => true,
            'message' =>
            'Order created successfully',

            'order' =>
            new OrderResource($order),

            'estimated_time' =>
            $estimatedTime,

            'payment_summary' => [
                'total_before_discount' =>
                round(
                    (float) $order
                        ->total_before_discount,
                    2
                ),

                'discount_value' =>
                0,

                'remaining_amount' =>
                round(
                    (float) $order->total,
                    2
                ),

                'currency' =>
                'EGP',

                'cash_code_applied' =>
                false,
            ],
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'kitchen') {
            if ($order->kitchen_id !== $user->profile->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        } else {
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        $order->load([
            'user',
            'kitchen.user.defaultAddress',
            'items.meal',
            'userAddress',
        ]);

        return response()->json([
            'order' => new OrderResource($order),

        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Check if user has access to update this order
        if ($user->role === 'kitchen') {
            if ($order->kitchen_id !== $user->profile->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        } else {
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        // check if order is still pending
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => "order is not pending you can not update it order is " . $order->status
            ], 422);
        }

        $validated = $request->validate([
            'kitchen_id' => 'sometimes|exists:kitchen_profiles,id',
            'user_address_id' => 'nullable|exists:user_addresses,id',
            'items' => 'sometimes|array|min:1',
            'items.*.meal_id' => 'required_with:items|exists:meals,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'receive_date' => 'nullable|date|before_or_equal:today + 7 days',
            'receive_time' => 'nullable',
            'book_for_later' => 'nullable|boolean',
        ]);

        DB::transaction(function () use (&$order, $validated) {
            if (isset($validated['items'])) {
                $total = 0;
                $items = $validated['items'];

                // Recalculate total from latest meal prices.
                foreach ($items as $item) {
                    $meal = \App\Models\Meal::find($item['meal_id']);
                    $total += $meal->price * $item['quantity'];
                }

                $order->items()->delete();
                foreach ($items as $item) {
                    $meal = \App\Models\Meal::find($item['meal_id']);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'meal_id' => $item['meal_id'],
                        'quantity' => $item['quantity'],
                        'price' => $meal->price,
                    ]);
                }

                $validated['total'] = $total;
                unset($validated['items']);
            }

            $order->update($validated);
        });

        $order->load(['user', 'kitchen', 'items.meal', 'userAddress']);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => new OrderResource($order)
        ]);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Check if user has access to delete this order
        if ($user->role === 'kitchen') {
            if ($order->kitchen_id !== $user->profile->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        } else {
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }


    // public function accept_order(
    //     Request $request,
    //     Order $order,
    //     OrderNotificationService $notificationService
    // ): JsonResponse {
    //     $user = $request->user();

    //     if ($user->role === 'kitchen') {
    //         if ($order->kitchen_id !== $user->profile->id) {
    //             return response()->json([
    //                 'message' => 'Unauthorized'
    //             ], 403);
    //         }
    //     } else {
    //         if ($order->user_id !== $user->id) {
    //             return response()->json([
    //                 'message' => 'Unauthorized'
    //             ], 403);
    //         }
    //     }

    //     $validated = $request->validate([
    //         'status' => 'required|in:accepted,rejected,ready_to_deliver,preparing',

    //         // مطلوب فقط لما المطبخ يحول الأوردر ready_to_deliver
    //         'delivery_count' => 'required_if:status,ready_to_deliver|nullable|integer|min:1|max:10',
    //     ]);

    //     if ($order->status === $validated['status']) {
    //         return response()->json([
    //             'message' => 'order is already ' . $validated['status'],
    //         ], 400);
    //     }

    //     $updateData = [
    //         'status' => $validated['status'],
    //     ];

    //     if ($validated['status'] === 'ready_to_deliver') {
    //         $updateData['delivery_count'] = $validated['delivery_count'];
    //     }

    //     $order->update($updateData);

    //     $order->refresh();

    //     /*
    // |--------------------------------------------------------------------------
    // | Send notification to client
    // |--------------------------------------------------------------------------
    // */

    //     // $notificationService->notifyClientStatusChanged(
    //     //     $order,
    //     //     $validated['status']
    //     // );

    //     $assignedDeliveries = [];

    //     if ($validated['status'] === 'ready_to_deliver') {
    //         $freshOrder = $order->fresh(['kitchen']);

    //         $deliveryCount = (int) $freshOrder->delivery_count;

    //         $assignedDeliveries = $this->assignNearestDeliveries(
    //             $freshOrder,
    //             $deliveryCount
    //         );

    //         /*
    //     |--------------------------------------------------------------------------
    //     | Send notification to assigned deliveries
    //     |--------------------------------------------------------------------------
    //     */

    //         foreach ($assignedDeliveries as $assignedDelivery) {
    //             $notificationService->notifyDeliveryNewOrder(
    //                 $freshOrder,
    //                 $assignedDelivery
    //             );
    //         }

    //         if (empty($assignedDeliveries)) {
    //             return response()->json([
    //                 'message' => 'Order ready_to_deliver successfully, but no delivery matched filters',
    //                 'order_status' => $validated['status'],
    //                 'delivery_count' => $deliveryCount,
    //                 'assigned_deliveries' => [],
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Order ' . $validated['status'] . ' successfully',
    //         'order_status' => $validated['status'],
    //         'delivery_count' => $order->fresh()->delivery_count,
    //         'assigned_deliveries' => $assignedDeliveries,
    //     ]);
    // }

    public function accept_order(Request $request, Order $order, OrderNotificationService $notificationService): JsonResponse
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if ($user->role === 'kitchen') {
            if (
                !$user->profile
                || (int) $order->kitchen_id !== (int) $user->profile->id
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }
        } else {
            if ((int) $order->user_id !== (int) $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'status' => [
                'required',
                'in:accepted,rejected,ready_to_deliver,preparing',
            ],

            'delivery_count' => [
                'required_if:status,ready_to_deliver',
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Convert Flutter status to database status
        |--------------------------------------------------------------------------
        */

        $databaseStatus = match ($validated['status']) {
            'accepted' => 'accepted_by_kitchen',
            default => $validated['status'],
        };

        if ($order->status === $databaseStatus) {
            return response()->json([
                'status' => false,
                'message' => 'Order is already ' . $databaseStatus,
                'order_status' => $databaseStatus,
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | Update order
        |--------------------------------------------------------------------------
        */

        $updateData = [
            'status' => $databaseStatus,
        ];

        /*
        |--------------------------------------------------------------------------
        | Payment state after kitchen acceptance
        |--------------------------------------------------------------------------
        |
        | لو الأوردر مدفوع بالكامل بالكاش كود:
        | payment_status ستظل paid ولن نعيدها إلى awaiting_payment.
        |
        | لو يوجد مبلغ متبقٍ:
        | نفتح فترة دفع للعميل لمدة 15 دقيقة.
        |
        */

        if ($databaseStatus === 'accepted_by_kitchen') {
            if ($order->payment_status !== 'paid') {
                $updateData['payment_status'] = 'awaiting_payment';
                $updateData['payment_expires_at'] = now()->addMinutes(15);
            } else {
                $updateData['payment_expires_at'] = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Payment state after rejection
        |--------------------------------------------------------------------------
        |
        | لو الأوردر غير مدفوع يتم إلغاء حالة الدفع.
        |
        | لو الأوردر مدفوع بالكامل بالكاش كود، لا نغيره هنا إلى cancelled
        | لأن التعامل مع إعادة رصيد الكاش كود سيكون في خطوة منفصلة.
        |
        */

        if ($databaseStatus === 'rejected') {
            if ($order->payment_status !== 'paid') {
                $updateData['payment_status'] = 'cancelled';
            }

            $updateData['payment_expires_at'] = null;
        }

        if ($databaseStatus === 'ready_to_deliver') {
            $updateData['delivery_count'] = (int) $validated['delivery_count'];
        }

        $order->update($updateData);
        $order->refresh();

        /*
        |--------------------------------------------------------------------------
        | Notify client
        |--------------------------------------------------------------------------
        */

        $notificationService->notifyClientStatusChanged(
            $order,
            $databaseStatus
        );

        $assignedDeliveries = [];
        $assignedDeliveriesResponse = [];

        /*
        |--------------------------------------------------------------------------
        | Assign deliveries
        |--------------------------------------------------------------------------
        */

        if ($databaseStatus === 'ready_to_deliver') {
            $freshOrder = $order->fresh([
                'kitchen',
            ]);

            $deliveryCount = (int) $freshOrder->delivery_count;

            $assignedDeliveries = $this->assignNearestDeliveries(
                $freshOrder,
                $deliveryCount
            );

            /*
        |--------------------------------------------------------------------------
        | Notify assigned deliveries
        |--------------------------------------------------------------------------
        */

            foreach ($assignedDeliveries as $assignedDelivery) {
                /*
             * assignNearestDeliveries قد ترجع:
             *
             * 1. DeliveryUser model
             * 2. array يحتوي على id
             * 3. array يحتوي على delivery_id
             * 4. array يحتوي على delivery model
             */

                $deliveryModel = null;

                if ($assignedDelivery instanceof DeliveryUser) {
                    $deliveryModel = $assignedDelivery;
                } elseif (
                    is_array($assignedDelivery)
                    && isset($assignedDelivery['delivery'])
                    && $assignedDelivery['delivery'] instanceof DeliveryUser
                ) {
                    $deliveryModel = $assignedDelivery['delivery'];
                } elseif (is_array($assignedDelivery)) {
                    $deliveryId =
                        $assignedDelivery['id']
                        ?? $assignedDelivery['delivery_id']
                        ?? $assignedDelivery['delivery_user_id']
                        ?? null;

                    if ($deliveryId) {
                        $deliveryModel = DeliveryUser::query()
                            ->find($deliveryId);
                    }
                }

                if (!$deliveryModel) {
                    continue;
                }

                $notificationService->notifyDeliveryNewOrder(
                    $freshOrder,
                    $deliveryModel
                );

                /*
             * تجهيز بيانات آمنة للـ response بدل إرجاع Model
             * أو Array غير معروف الشكل.
             */
                $assignedDeliveriesResponse[] = [
                    'id' => $deliveryModel->id,
                    'name' => $deliveryModel->name,
                    'phone' => $deliveryModel->phone,
                    'distance' => is_array($assignedDelivery)
                        ? ($assignedDelivery['distance'] ?? null)
                        : ($deliveryModel->distance ?? null),
                ];
            }

            if (empty($assignedDeliveriesResponse)) {
                return response()->json([
                    'status' => true,
                    'message' => 'Order ready_to_deliver successfully, but no delivery matched filters',
                    'order_status' => $databaseStatus,
                    'payment_status' => $order->payment_status,
                    'payment_expires_at' => $order->payment_expires_at,
                    'delivery_count' => $deliveryCount,
                    'assigned_deliveries' => [],
                ]);
            }
        }

        $freshOrder = $order->fresh();

        return response()->json([
            'status' => true,
            'message' => 'Order ' . $databaseStatus . ' successfully',
            'order_status' => $databaseStatus,
            'payment_status' => $freshOrder->payment_status,
            'payment_expires_at' => $freshOrder->payment_expires_at,
            'delivery_count' => $freshOrder->delivery_count,
            'assigned_deliveries' => $assignedDeliveriesResponse,
        ]);
    }
    public function cancel_order(
        Request $request,
        Order $order,
        OrderNotificationService $notificationService
    ) {
        $user = $request->user();
        $given_date = Carbon::parse($order->receive_date)->startOfDay();
        $today = Carbon::today();

        // check if this user own this order
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => "this order you can not canceled "
            ], 401);
        }

        // check if order is booked for later time
        if ($order->receive_date) {

            // check if order cancel on safe time
            if ($today->diffInDays($given_date) < 1) {
                return response()->json([
                    'message' => "you can not cancel order before less than 24 hours from receive date",
                ], 422);
            } else {
                $order->update([
                    'status' => 'cancelled_by_client',
                    'cancel_date' => now()
                ]);
            }
        } else {
            // check order status
            if ($order->status === 'preparing') {
                return response()->json([
                    'message' => "you can not cancel order the order is preparing please contact with support",
                ], 422);
            } else {
                $order->update([
                    'status' => 'cancelled_by_client',
                    'cancel_date' => now()
                ]);
            }
        }

        $order->refresh();

        $notificationService->notifyKitchenClientCancelled(
            $order
        );

        return response()->json([
            'message' => "order canceled successfully"
        ], 200);
    }



    public function deliver_order(Request $request, Order $order, WalletService $walletService)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted_by_delivery,received_by_delivery,on_the_way,delivered',
        ]);

        $current = $order->status;
        $new = $validated['status'];

        $allowedTransitions = [
            'ready_to_deliver'      => ['accepted_by_delivery'],
            'accepted_by_delivery' => ['received_by_delivery'],
            'received_by_delivery' => ['on_the_way'],
            'on_the_way'           => ['delivered'],
            'delivered'            => [],
        ];

        if (!isset($allowedTransitions[$current]) || !in_array($new, $allowedTransitions[$current])) {
            return response()->json([
                'status' => false,
                'message' => "Invalid status transition from {$current} to {$new}",
            ], 422);
        }

        $orderData = [
            'status' => $new,
        ];

        if ($new === 'delivered') {
            $orderData['delivered_at'] = now();
        }

        $order->update($orderData);

        $deliveryOrderStatus = match ($new) {
            'accepted_by_delivery' => 'accepted',
            'received_by_delivery' => 'picked_up',
            'on_the_way'           => 'on_the_way',
            'delivered'            => 'delivered',
        };

        $order->deliveryOrders()->update([
            'status' => $deliveryOrderStatus,
        ]);

        if ($new === 'delivered') {
            $walletService->credit($order->fresh(), $request);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order ' . $new . ' successfully',
            'order_status' => $new,
            'delivery_order_status' => $deliveryOrderStatus,
        ]);
    }


    private function assignNearestDeliveries(Order $order, int $count): array
    {
        $count = max(1, $count);

        $kitchen = $order->kitchen;

        if (!$kitchen) {
            Log::warning('No kitchen found for order', [
                'order_id' => $order->id,
                'kitchen_id' => $order->kitchen_id,
            ]);

            return [];
        }

        /*
    |--------------------------------------------------------------------------
    | عنوان المطبخ
    |--------------------------------------------------------------------------
    |
    | order.user_id = العميل
    | kitchen.user_id = صاحب حساب المطبخ
    |
    */

        $kitchenAddress = \App\Models\UserAddress::query()
            ->where('user_id', $kitchen->user_id)
            ->where('is_default', 1)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->first();

        if (!$kitchenAddress) {
            Log::warning('Kitchen address not found', [
                'order_id' => $order->id,
                'kitchen_id' => $kitchen->id,
                'kitchen_user_id' => $kitchen->user_id,
            ]);

            return [];
        }

        $kitchenLat = (float) $kitchenAddress->lat;
        $kitchenLng = (float) $kitchenAddress->lng;

        /*
    |--------------------------------------------------------------------------
    | آخر شيفت فعال لكل دليفري
    |--------------------------------------------------------------------------
    */

        $latestActiveShiftIds = \App\Models\DeliveryShiftLog::query()
            ->selectRaw('MAX(id)')
            ->where('status', 'active')
            ->whereNull('end_time')
            ->groupBy('delivery_user_id');

        /*
    |--------------------------------------------------------------------------
    | أقرب دليفري من موقعه داخل الشيفت
    |--------------------------------------------------------------------------
    */

        $deliveries = \App\Models\DeliveryShiftLog::query()
            ->join(
                'delivery_users',
                'delivery_users.id',
                '=',
                'delivery_shift_logs.delivery_user_id'
            )
            ->select([
                'delivery_users.id',
                'delivery_users.name',
                'delivery_users.phone',
                'delivery_users.status',
                'delivery_users.is_break',
                'delivery_shift_logs.id as shift_log_id',
            ])
            ->selectRaw(
                '
            COALESCE(
                delivery_shift_logs.end_lat,
                delivery_shift_logs.start_lat
            ) AS delivery_lat
            '
            )
            ->selectRaw(
                '
            COALESCE(
                delivery_shift_logs.end_lng,
                delivery_shift_logs.start_lng
            ) AS delivery_lng
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
                    $kitchenLat,
                    $kitchenLng,
                    $kitchenLat,
                ]
            )
            ->whereIn('delivery_shift_logs.id', $latestActiveShiftIds)
            ->where('delivery_shift_logs.status', 'active')
            ->whereNull('delivery_shift_logs.end_time')
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
            )
            ->where('delivery_users.status', 'approved')
            ->where('delivery_users.is_break', 0)

            /*
        |--------------------------------------------------------------------------
        | استبعاد الدليفري المشغول
        |--------------------------------------------------------------------------
        */

            ->whereNotExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('delivery_orders')
                    ->whereColumn(
                        'delivery_orders.delivery_user_id',
                        'delivery_users.id'
                    )
                    ->whereIn('delivery_orders.status', [
                        'accepted_by_delivery',
                        'received_by_delivery',
                        'on_the_way',
                    ]);
            })
            ->orderBy('distance')
            ->limit($count)
            ->get();

        Log::info('Nearest delivery assignment result', [
            'order_id' => $order->id,
            'client_user_id' => $order->user_id,
            'kitchen_id' => $order->kitchen_id,
            'kitchen_user_id' => $kitchen->user_id,
            'kitchen_lat' => $kitchenLat,
            'kitchen_lng' => $kitchenLng,
            'requested_count' => $count,
            'matched_count' => $deliveries->count(),
            'deliveries' => $deliveries->map(function ($delivery) {
                return [
                    'delivery_user_id' => $delivery->id,
                    'delivery_lat' => $delivery->delivery_lat,
                    'delivery_lng' => $delivery->delivery_lng,
                    'distance_km' => $delivery->distance,
                    'status' => $delivery->status,
                    'is_break' => $delivery->is_break,
                ];
            })->toArray(),
        ]);

        /*
    |--------------------------------------------------------------------------
    | إنشاء الطلبات للدليفري
    |--------------------------------------------------------------------------
    */

        foreach ($deliveries as $delivery) {
            \App\Models\DeliveryOrder::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'delivery_user_id' => $delivery->id,
                ],
                [
                    'status' => 'pending',
                    'cash_settled' => false,
                ]
            );
        }

        return $deliveries
            ->map(function ($delivery) {
                return [
                    'id' => $delivery->id,
                    'name' => $delivery->name,
                    'phone' => $delivery->phone,
                    'distance_km' => round(
                        (float) $delivery->distance,
                        2
                    ),
                ];
            })
            ->values()
            ->toArray();
    }





    private function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLng / 2) *
            sin($dLng / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return round(
            $earthRadius * $c,
            2
        );
    }




    public function orderApprovalStatus(
        Request $request,
        Order $order
    ): JsonResponse {
        $user = $request->user();

        if ((int) $order->user_id !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $isApproved = in_array($order->status, [
            'accepted',
            'accepted_by_kitchen',
            'preparing',
            'ready_to_deliver',
            'accepted_by_delivery',
            'received_from_kitchen',
            'on_the_way',
            'delivered',
        ], true);

        $isRejected = in_array($order->status, [
            'rejected',
            'cancelled',
            'cancelled_by_admin',
            'cancelled_by_kitchen',
        ], true);

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->number,
                'order_status' => $order->status,
                'is_approved' => $isApproved,
                'is_rejected' => $isRejected,
                'is_pending' => !$isApproved && !$isRejected,
            ],
        ]);
    }




    public function selectPaymentMethod(
        Request $request,
        Order $order
    ): JsonResponse {
        $user = $request->user();

        /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

        if (
            (int) $order->user_id !==
            (int) $user->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([
            'payment_method' => [
                'required',
                'in:cash,online',
            ],
        ]);

        try {
            $updatedOrder = DB::transaction(
                function () use (
                    $validated,
                    $order,
                    $user
                ) {
                    /*
                |--------------------------------------------------------------------------
                | Lock order
                |--------------------------------------------------------------------------
                */

                    $lockedOrder = Order::query()
                        ->lockForUpdate()
                        ->findOrFail($order->id);

                    /*
                |--------------------------------------------------------------------------
                | Authorization
                |--------------------------------------------------------------------------
                */

                    if (
                        (int) $lockedOrder->user_id !==
                        (int) $user->id
                    ) {
                        throw new \RuntimeException(
                            'Unauthorized'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Prevent changing paid order
                |--------------------------------------------------------------------------
                */

                    if (
                        $lockedOrder->payment_status ===
                        'paid'
                    ) {
                        throw new \RuntimeException(
                            'Order is already paid'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Prevent changing completed order
                |--------------------------------------------------------------------------
                */

                    if (
                        in_array(
                            $lockedOrder->status,
                            [
                                'delivered',
                                'cancelled',
                                'cancelled_by_admin',
                                'cancelled_by_kitchen',
                                'rejected',
                            ],
                            true
                        )
                    ) {
                        throw new \RuntimeException(
                            'Payment method cannot be changed after order completion'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate allowed order statuses
                |--------------------------------------------------------------------------
                */

                    $allowedStatuses = [
                        'accepted_by_kitchen',
                        'preparing',
                        'ready_to_deliver',
                        'accepted_by_delivery',
                        'received_by_delivery',
                    ];

                    if (
                        !in_array(
                            $lockedOrder->status,
                            $allowedStatuses,
                            true
                        )
                    ) {
                        throw new \RuntimeException(
                            'Payment method cannot be selected in the current order status'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Remaining amount after cash-code discount
                |--------------------------------------------------------------------------
                */

                    $remainingAmount = round(
                        max(
                            (float) $lockedOrder->total,
                            0
                        ),
                        2
                    );

                    /*
                |--------------------------------------------------------------------------
                | Fully paid by cash code
                |--------------------------------------------------------------------------
                |
                | المفروض applyCashCode تكون بالفعل حولت الحالة إلى paid،
                | لكن الشرط موجود كحماية إضافية.
                |
                */

                    if ($remainingAmount <= 0) {
                        $lockedOrder->update([
                            'payment_method' =>
                            'cash_code',

                            'payment_status' =>
                            'paid',

                            'payment_reference' =>
                            null,

                            'payment_expires_at' =>
                            null,

                            'paid_at' =>
                            $lockedOrder->paid_at
                                ?? now(),
                        ]);

                        return $lockedOrder->fresh();
                    }

                    /*
                |--------------------------------------------------------------------------
                | Active Kashier payment session
                |--------------------------------------------------------------------------
                */

                    $activeOnlinePayment =
                        PaymentTransaction::query()
                        ->where(
                            'order_id',
                            $lockedOrder->id
                        )
                        ->where(
                            'provider',
                            'kashier'
                        )
                        ->where(
                            'status',
                            'pending'
                        )
                        ->where(function (
                            $query
                        ) {
                            $query
                                ->whereNull(
                                    'expires_at'
                                )
                                ->orWhere(
                                    'expires_at',
                                    '>',
                                    now()
                                );
                        })
                        ->latest('id')
                        ->first();

                    /*
                |--------------------------------------------------------------------------
                | Prevent changing online to cash while session is active
                |--------------------------------------------------------------------------
                */

                    // if (
                    //     $activeOnlinePayment
                    //     && $validated['payment_method'] ===
                    //     'cash'
                    // ) {
                    //     throw new \RuntimeException(
                    //         'An active online payment session already exists'
                    //     );
                    // }

                    /*
                |--------------------------------------------------------------------------
                | Select cash
                |--------------------------------------------------------------------------
                */

                    if (
                        $validated['payment_method'] === 'cash'
                    ) {

                        // Cancel existing Kashier pending session
                        PaymentTransaction::where('order_id', $lockedOrder->id)
                            ->where('provider', 'kashier')
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'cancelled',
                                'provider_status' => 'METHOD_CHANGED',
                            ]);


                        // Change order payment method to cash
                        $lockedOrder->update([
                            'payment_method' => 'cash',

                            'payment_status' =>
                            'cash_pending',

                            'payment_reference' =>
                            null,

                            'payment_expires_at' =>
                            null,

                            'paid_at' =>
                            null,
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Select online
                |--------------------------------------------------------------------------
                */

                    if (
                        $validated['payment_method'] ===
                        'online'
                    ) {
                        $lockedOrder->update([
                            'payment_method' =>
                            'online',

                            'payment_status' =>
                            'awaiting_payment',

                            /*
                         * المدة الفعلية يتم تثبيتها أيضًا
                         * عند إنشاء جلسة Kashier.
                         */
                            'payment_expires_at' =>
                            now()->addMinutes(15),

                            'paid_at' =>
                            null,
                        ]);
                    }

                    return $lockedOrder->fresh();
                }
            );

            $remainingAmount = round(
                max(
                    (float) $updatedOrder->total,
                    0
                ),
                2
            );

            return response()->json([
                'status' => true,
                'message' =>
                'Payment method selected successfully',

                'data' => [
                    'order_id' =>
                    $updatedOrder->id,

                    'order_number' =>
                    $updatedOrder->number,

                    'total_before_discount' =>
                    round(
                        (float) $updatedOrder
                            ->total_before_discount,
                        2
                    ),

                    'discount_value' =>
                    round(
                        (float) $updatedOrder
                            ->discount_value,
                        2
                    ),

                    'remaining_amount' =>
                    $remainingAmount,

                    'currency' =>
                    'EGP',

                    'cash_code_id' =>
                    $updatedOrder->cash_code_id,

                    'cash_code_applied' =>
                    $updatedOrder->cash_code_id !==
                        null,

                    'payment_method' =>
                    $updatedOrder->payment_method,

                    'payment_status' =>
                    $updatedOrder->payment_status,

                    'is_fully_paid' =>
                    $updatedOrder->payment_status ===
                        'paid',

                    'is_payment_required' =>
                    $updatedOrder->payment_status !==
                        'paid'
                        && $remainingAmount > 0,

                    'payment_expires_at' =>
                    $updatedOrder
                        ->payment_expires_at
                        ?->toISOString(),

                    'can_create_kashier_session' =>
                    $updatedOrder->payment_method ===
                        'online'
                        && $remainingAmount > 0
                        && in_array(
                            $updatedOrder->payment_status,
                            [
                                'awaiting_payment',
                                'failed',
                                'expired',
                            ],
                            true
                        ),
                ],
            ]);
        } catch (\RuntimeException $exception) {
            $statusCode =
                $exception->getMessage() ===
                'Unauthorized'
                ? 403
                : 422;

            return response()->json([
                'status' => false,
                'message' =>
                $exception->getMessage(),
            ], $statusCode);
        } catch (\Throwable $exception) {
            Log::error(
                'Select payment method failed',
                [
                    'order_id' =>
                    $order->id,

                    'user_id' =>
                    $user->id,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,
                'message' =>
                'Unable to select payment method',

                'error' =>
                $exception->getMessage(),
            ], 500);
        }
    }




    public function applyCashCode(
        Request $request,
        Order $order
    ): JsonResponse {
        $user = $request->user();

        /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

        if (
            (int) $order->user_id !==
            (int) $user->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([
            'cash_code' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        try {
            $updatedOrder = DB::transaction(
                function () use (
                    $order,
                    $user,
                    $validated
                ) {
                    /*
                |--------------------------------------------------------------------------
                | Lock order
                |--------------------------------------------------------------------------
                */

                    $lockedOrder = Order::query()
                        ->lockForUpdate()
                        ->findOrFail($order->id);

                    if (
                        (int) $lockedOrder->user_id !==
                        (int) $user->id
                    ) {
                        throw new \RuntimeException(
                            'Unauthorized'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate order payment
                |--------------------------------------------------------------------------
                */

                    if (
                        $lockedOrder->payment_status ===
                        'paid'
                    ) {
                        throw new \RuntimeException(
                            'Order is already paid'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate order status
                |--------------------------------------------------------------------------
                */

                    if (
                        in_array(
                            $lockedOrder->status,
                            [
                                'delivered',
                                'cancelled',
                                'cancelled_by_admin',
                                'cancelled_by_kitchen',
                                'rejected',
                            ],
                            true
                        )
                    ) {
                        throw new \RuntimeException(
                            'Cash code cannot be applied to this order'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Prevent applying code after payment method selection
                |--------------------------------------------------------------------------
                */

                    if (
                        in_array(
                            $lockedOrder->payment_status,
                            [
                                'pending',
                                'cash_pending',
                            ],
                            true
                        )
                    ) {
                        throw new \RuntimeException(
                            'Cash code cannot be applied after payment has started'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Prevent applying more than one cash code
                |--------------------------------------------------------------------------
                */

                    if (
                        $lockedOrder->cash_code_id
                        || (float) $lockedOrder
                            ->discount_value > 0
                    ) {
                        throw new \RuntimeException(
                            'A cash code has already been applied to this order'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Prevent applying code while Kashier session is active
                |--------------------------------------------------------------------------
                */

                    $hasActiveOnlinePayment =
                        PaymentTransaction::query()
                        ->where(
                            'order_id',
                            $lockedOrder->id
                        )
                        ->where(
                            'provider',
                            'kashier'
                        )
                        ->where(
                            'status',
                            'pending'
                        )
                        ->where(function (
                            $query
                        ) {
                            $query
                                ->whereNull(
                                    'expires_at'
                                )
                                ->orWhere(
                                    'expires_at',
                                    '>',
                                    now()
                                );
                        })
                        ->exists();

                    if ($hasActiveOnlinePayment) {
                        throw new \RuntimeException(
                            'Cash code cannot be applied while an online payment session is active'
                        );
                    }

                    /*
                |--------------------------------------------------------------------------
                | Find and lock cash code
                |--------------------------------------------------------------------------
                */

                    $normalizedCode = strtoupper(
                        trim(
                            $validated['cash_code']
                        )
                    );

                    $cashCode = CashCode::query()
                        ->where(
                            'code',
                            $normalizedCode
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$cashCode) {
                        throw ValidationException::withMessages([
                            'cash_code' => [
                                'الكود النقدي غير صحيح',
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate cash code owner
                |--------------------------------------------------------------------------
                */

                    if (
                        (int) $cashCode->user_id !==
                        (int) $user->id
                    ) {
                        throw ValidationException::withMessages([
                            'cash_code' => [
                                'هذا الكود غير مخصص لحسابك',
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate cash code status
                |--------------------------------------------------------------------------
                */

                    if (!$cashCode->is_active) {
                        throw ValidationException::withMessages([
                            'cash_code' => [
                                'هذا الكود غير مفعل',
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate expiration
                |--------------------------------------------------------------------------
                */

                    if (
                        !$cashCode->expires_at
                        || now()->greaterThan(
                            $cashCode->expires_at
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'cash_code' => [
                                'انتهت صلاحية هذا الكود',
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate maximum uses
                |--------------------------------------------------------------------------
                */

                    if (
                        $cashCode->max_uses !== null
                        && (int) $cashCode->used_count >=
                        (int) $cashCode->max_uses
                    ) {
                        throw ValidationException::withMessages([
                            'cash_code' => [
                                'تم استهلاك الحد الأقصى لاستخدام هذا الكود',
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Validate remaining balance
                |--------------------------------------------------------------------------
                */

                    $balanceBefore = round(
                        (float) $cashCode
                            ->remaining_balance,
                        2
                    );

                    if ($balanceBefore <= 0) {
                        throw ValidationException::withMessages([
                            'cash_code' => [
                                'رصيد الكود غير كافٍ',
                            ],
                        ]);
                    }

                    /*
                |--------------------------------------------------------------------------
                | Calculate discount
                |--------------------------------------------------------------------------
                */

                    $totalBeforeDiscount = round(
                        (float) $lockedOrder
                            ->total_before_discount,
                        2
                    );

                    $discountValue = round(
                        min(
                            $balanceBefore,
                            $totalBeforeDiscount
                        ),
                        2
                    );

                    $balanceAfter = round(
                        max(
                            $balanceBefore
                                - $discountValue,
                            0
                        ),
                        2
                    );

                    $finalTotal = round(
                        max(
                            $totalBeforeDiscount
                                - $discountValue,
                            0
                        ),
                        2
                    );

                    /*
                |--------------------------------------------------------------------------
                | Check if fully paid by cash code
                |--------------------------------------------------------------------------
                */

                    $isFullyPaid =
                        $finalTotal <= 0;

                    /*
                |--------------------------------------------------------------------------
                | Update order
                |--------------------------------------------------------------------------
                */

                    $lockedOrder->update([
                        'cash_code_id' =>
                        $cashCode->id,

                        'discount_value' =>
                        $discountValue,

                        'total' =>
                        $finalTotal,

                        'payment_method' =>
                        $isFullyPaid
                            ? 'cash_code'
                            : null,

                        'payment_status' =>
                        $isFullyPaid
                            ? 'paid'
                            : 'unpaid',

                        'paid_at' =>
                        $isFullyPaid
                            ? (
                                $lockedOrder->paid_at
                                ?? now()
                            )
                            : null,

                        'payment_reference' =>
                        null,

                        'payment_expires_at' =>
                        null,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Deduct cash code balance
                |--------------------------------------------------------------------------
                */

                    $cashCode->update([
                        'remaining_balance' =>
                        $balanceAfter,

                        'used_count' =>
                        (int) $cashCode
                            ->used_count + 1,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Record cash code usage
                |--------------------------------------------------------------------------
                */

                    CashCodeUsage::create([
                        'cash_code_id' =>
                        $cashCode->id,

                        'user_id' =>
                        $user->id,

                        'order_id' =>
                        $lockedOrder->id,

                        'amount' =>
                        $discountValue,

                        'balance_before' =>
                        $balanceBefore,

                        'balance_after' =>
                        $balanceAfter,
                    ]);

                    return $lockedOrder->fresh();
                }
            );

            $remainingAmount = round(
                max(
                    (float) $updatedOrder->total,
                    0
                ),
                2
            );

            return response()->json([
                'status' => true,

                'message' =>
                $remainingAmount <= 0
                    ? 'Cash code applied and order fully paid'
                    : 'Cash code applied successfully',

                'data' => [
                    'order_id' =>
                    $updatedOrder->id,

                    'order_number' =>
                    $updatedOrder->number,

                    'total_before_discount' =>
                    round(
                        (float) $updatedOrder
                            ->total_before_discount,
                        2
                    ),

                    'discount_value' =>
                    round(
                        (float) $updatedOrder
                            ->discount_value,
                        2
                    ),

                    'remaining_amount' =>
                    $remainingAmount,

                    'currency' =>
                    'EGP',

                    'cash_code_id' =>
                    $updatedOrder->cash_code_id,

                    'cash_code_applied' =>
                    true,

                    'is_fully_paid' =>
                    $updatedOrder
                        ->payment_status ===
                        'paid',

                    'is_payment_required' =>
                    $updatedOrder
                        ->payment_status !==
                        'paid'
                        && $remainingAmount > 0,

                    'payment_method' =>
                    $updatedOrder
                        ->payment_method,

                    'payment_status' =>
                    $updatedOrder
                        ->payment_status,

                    'paid_at' =>
                    $updatedOrder
                        ->paid_at
                        ?->toISOString(),
                ],
            ]);
        } catch (\RuntimeException $exception) {
            $statusCode =
                $exception->getMessage() ===
                'Unauthorized'
                ? 403
                : 422;

            return response()->json([
                'status' => false,
                'message' =>
                $exception->getMessage(),
            ], $statusCode);
        } catch (\Throwable $exception) {
            Log::error(
                'Apply cash code failed',
                [
                    'order_id' =>
                    $order->id,

                    'user_id' =>
                    $user->id,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,
                'message' =>
                'Unable to apply cash code',

                'error' =>
                $exception->getMessage(),
            ], 500);
        }
    }










    public function paymentStatus(Order $order)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $order->id,
                'number' => $order->number,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
            ],
        ]);
    }
}
