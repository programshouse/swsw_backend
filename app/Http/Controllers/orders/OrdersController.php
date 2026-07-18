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
use Illuminate\Validation\ValidationException;
use App\Models\UserAddress;
use App\Models\OrderFeeRule;
use App\services\OrderNotificationService;
use Illuminate\Support\Facades\Log;



class OrdersController extends Controller
{
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
        $user = $request->user()->profile;
        $orders = Order::where('kitchen_id', $user->id)->with('items.meal', 'userAddress')->get();
        return response()->json([
            'orders' => OrderResource::collection($orders)
        ]);
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
            'kitchen_id' => 'required|exists:kitchen_profiles,id',
            'address_id' => 'nullable|integer',

            'items' => 'required|array|min:1',
            'items.*.meal_id' => 'required|exists:meals,id',
            'items.*.quantity' => 'required|integer|min:1',

            'receive_date' => 'nullable|date|before_or_equal:today + 7 days',
            'receive_time' => 'required_with:receive_date',
            'book_for_later' => 'required_with:receive_date|boolean',

            'cash_code' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        $settings = Setting::first();

        /*
    |--------------------------------------------------------------------------
    | Check application working time
    |--------------------------------------------------------------------------
    */

        $start = $settings?->work_start_time;
        $end = $settings?->work_end_time;

        if ($start && $end) {
            $now = Carbon::now('Africa/Cairo')->format('H:i:s');

            /*
         * يدعم كذلك ساعات العمل التي تتخطى منتصف الليل.
         */
            $isOpen = $start <= $end
                ? ($now >= $start && $now <= $end)
                : ($now >= $start || $now <= $end);

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

        $kitchen = KitchenProfile::find($validated['kitchen_id']);

        if (!$kitchen) {
            return response()->json([
                'message' => 'Kitchen not found',
            ], 404);
        }

        // check if kitchen is open now
        if (in_array($kitchen->open_status, ['closed', 'busy'])) {
            return response()->json([
                'message' =>
                'this kitchen may closed or busy right now you can order latter',
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Check meals availability
    |--------------------------------------------------------------------------
    */

        foreach ($validated['items'] as $item) {
            $meal = \App\Models\Meal::find($item['meal_id']);

            if (!$meal) {
                return response()->json([
                    'message' => 'one of your items does not exist',
                ], 422);
            }

            /*
         * لا يوجد فحص meal->kitchen_id هنا؛
         * لأن الفانكشن الأصلية في البرودكشن لم تكن تعتمد عليه.
         */

            if (!$meal->availability) {
                return response()->json([
                    'message' => 'one of your items is unavailable now',
                    'item' => [
                        'name' => $meal->name,
                        'id' => $meal->id,
                        'image' => $meal->image
                            ? config('app.url') .
                            '/storage/' .
                            $meal->image
                            : null,
                    ],
                ], 422);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Calculate items subtotal
    |--------------------------------------------------------------------------
    */

        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $meal = \App\Models\Meal::find($item['meal_id']);

            $subtotal +=
                (float) $meal->price *
                (int) $item['quantity'];
        }

        $subtotal = round($subtotal, 2);

        /*
    |--------------------------------------------------------------------------
    | Client address
    |--------------------------------------------------------------------------
    */

        $clientAddress = null;

        if (!empty($validated['address_id'])) {
            /*
         * التأكد أن العنوان يخص نفس العميل بدون تغيير Validation البرودكشن.
         */
            $clientAddress = UserAddress::query()
                ->where('id', $validated['address_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$clientAddress) {
                return response()->json([
                    'status' => false,
                    'message' => 'User address not found',
                ], 422);
            }
        } else {
            $clientAddress = UserAddress::query()
                ->where('user_id', $user->id)
                ->where('is_default', 1)
                ->first();
        }

        /*
    |--------------------------------------------------------------------------
    | Kitchen address
    |--------------------------------------------------------------------------
    */

        $kitchen->load('user.defaultAddress');

        $kitchenAddress = $kitchen->user?->defaultAddress;

        /*
    |--------------------------------------------------------------------------
    | Calculate distance and delivery
    |--------------------------------------------------------------------------
    */

        $distanceKm = 0;

        if (
            $clientAddress &&
            $kitchenAddress &&
            $clientAddress->lat !== null &&
            $clientAddress->lng !== null &&
            $kitchenAddress->lat !== null &&
            $kitchenAddress->lng !== null
        ) {
            $distanceKm = $this->calculateDistance(
                (float) $clientAddress->lat,
                (float) $clientAddress->lng,
                (float) $kitchenAddress->lat,
                (float) $kitchenAddress->lng
            );
        }

        /*
     * الاسم في الجدول delivery_meter_price،
     * ولكنه مستخدم حاليًا كسعر لكل كيلومتر.
     */
        $deliveryMeterPrice = round(
            (float) ($settings?->delivery_meter_price ?? 0),
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
            (float) ($settings?->vat_percentage ?? 0),
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
            ->where('min_order_amount', '<=', $subtotal)
            ->where(function ($query) use ($subtotal) {
                $query
                    ->whereNull('max_order_amount')
                    ->orWhere(
                        'max_order_amount',
                        '>=',
                        $subtotal
                    );
            })
            ->orderByDesc('min_order_amount')
            ->first();

        $clientServiceFee = round(
            (float) ($feeRule?->client_service_fee ?? 0),
            2
        );

        $kitchenServiceFee = round(
            (float) ($feeRule?->kitchen_service_fee ?? 0),
            2
        );

        /*
    |--------------------------------------------------------------------------
    | Kitchen amount
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
    |
    | رسوم المطبخ لا تضاف على العميل.
    | رسوم المطبخ يتم خصمها فقط من مستحق المطبخ.
    |
    */

        $totalBeforeDiscount = round(
            $subtotal +
                $vatValue +
                $deliveryPrice +
                $clientServiceFee,
            2
        );

        /*
    |--------------------------------------------------------------------------
    | Cash code
    |--------------------------------------------------------------------------
    */

        $cashCode = null;
        $discountValue = 0;
        $finalTotal = $totalBeforeDiscount;
        $balanceBefore = null;
        $balanceAfter = null;

        /*
    |--------------------------------------------------------------------------
    | Create order and deduct cash-code balance
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
            &$cashCode,
            &$discountValue,
            &$finalTotal,
            &$balanceBefore,
            &$balanceAfter
        ) {
            /*
        |--------------------------------------------------------------------------
        | Cash code validation
        |--------------------------------------------------------------------------
        */

            if (!empty($validated['cash_code'])) {
                $normalizedCode = strtoupper(
                    trim($validated['cash_code'])
                );

                $cashCode = CashCode::query()
                    ->where('code', $normalizedCode)
                    ->lockForUpdate()
                    ->first();

                if (!$cashCode) {
                    throw ValidationException::withMessages([
                        'cash_code' => [
                            'الكود النقدي غير صحيح',
                        ],
                    ]);
                }

                if ((int) $cashCode->user_id !== (int) $user->id) {
                    throw ValidationException::withMessages([
                        'cash_code' => [
                            'هذا الكود غير مخصص لحسابك',
                        ],
                    ]);
                }

                if (!$cashCode->is_active) {
                    throw ValidationException::withMessages([
                        'cash_code' => [
                            'هذا الكود غير مفعل',
                        ],
                    ]);
                }

                if (
                    !$cashCode->expires_at ||
                    now()->greaterThan($cashCode->expires_at)
                ) {
                    throw ValidationException::withMessages([
                        'cash_code' => [
                            'انتهت صلاحية هذا الكود',
                        ],
                    ]);
                }

                if (
                    $cashCode->max_uses !== null &&
                    (int) $cashCode->used_count >=
                    (int) $cashCode->max_uses
                ) {
                    throw ValidationException::withMessages([
                        'cash_code' => [
                            'تم استهلاك الحد الأقصى لاستخدام هذا الكود',
                        ],
                    ]);
                }

                /*
             * الكاش كود يغطي كامل ما يجب على العميل دفعه:
             *
             * subtotal
             * + VAT
             * + delivery
             * + client service fee
             */
                if (
                    (float) $cashCode->remaining_balance <
                    (float) $totalBeforeDiscount
                ) {
                    throw ValidationException::withMessages([
                        'cash_code' => [
                            'رصيد الكود غير كافٍ لتغطية قيمة الطلب بالكامل',
                        ],
                    ]);
                }

                $balanceBefore =
                    (float) $cashCode->remaining_balance;

                $discountValue =
                    (float) $totalBeforeDiscount;

                $balanceAfter = round(
                    $balanceBefore - $discountValue,
                    2
                );

                // The order becomes free for the client
                $finalTotal = 0;
            }

            /*
        |--------------------------------------------------------------------------
        | Order number
        |--------------------------------------------------------------------------
        */

            $lastOrder = Order::latest('id')->first();

            $nextId = $lastOrder
                ? $lastOrder->id + 1
                : 1;

            $orderNumber = 'ORD-'
                . now()->format('Ymd')
                . '-'
                . str_pad(
                    $nextId,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            /*
        |--------------------------------------------------------------------------
        | Create order
        |--------------------------------------------------------------------------
        */

            $order = Order::create([
                'user_id' => $user->id,
                'kitchen_id' => $validated['kitchen_id'],
                'number' => $orderNumber,

                /*
             * Financial snapshot
             */
                'subtotal' => $subtotal,

                'vat_percentage' => $vatPercentage,
                'vat_value' => $vatValue,

                'distance_km' => $distanceKm,
                'delivery_meter_price' => $deliveryMeterPrice,
                'delivery_price' => $deliveryPrice,

                'order_fee_rule_id' => $feeRule?->id,

                'client_service_fee' => $clientServiceFee,
                'kitchen_service_fee' => $kitchenServiceFee,

                'kitchen_net_amount' => $kitchenNetAmount,

                'total_before_discount' =>
                $totalBeforeDiscount,

                'discount_value' => $discountValue,
                'total' => $finalTotal,

                'cash_code_id' => $cashCode?->id,
                'payment_method' => null,
                'payment_status' => 'unpaid',
                'paid_at' => null,
                'payment_expires_at' => null,

                /*
             * الاحتفاظ بنفس السلوك القديم:
             * لو لم يرسل العنوان، نستخدم العنوان الافتراضي إن وجد.
             */
                'user_address_id' =>
                $clientAddress?->id
                    ?? ($validated['address_id'] ?? null),

                'receive_date' =>
                $validated['receive_date'] ?? null,

                'receive_time' =>
                $validated['receive_time'] ?? null,

                'book_for_later' =>
                $validated['book_for_later'] ?? false,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Create order items
        |--------------------------------------------------------------------------
        */

            foreach ($validated['items'] as $item) {
                $meal = \App\Models\Meal::find(
                    $item['meal_id']
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'meal_id' => $item['meal_id'],
                    'quantity' => $item['quantity'],

                    /*
                 * تخزين سعر الوجبة وقت الطلب.
                 */
                    'price' => $meal->price,
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Deduct balance and save usage
        |--------------------------------------------------------------------------
        */

            if ($cashCode) {
                $cashCode->update([
                    'remaining_balance' => $balanceAfter,

                    'used_count' =>
                    (int) $cashCode->used_count + 1,
                ]);

                CashCodeUsage::create([
                    'cash_code_id' => $cashCode->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount' => $discountValue,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                ]);
            }

            return $order;
        });

        /*
    |--------------------------------------------------------------------------
    | Calculate estimated time
    |--------------------------------------------------------------------------
    */

        $quantities = array_column(
            $validated['items'],
            'quantity'
        );

        $estimated_time = !empty($quantities)
            ? max($quantities)
            : 0;

        /*
    |--------------------------------------------------------------------------
    | Load response relations
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
    | Fire event
    |--------------------------------------------------------------------------
    */

        broadcast(new CreateOrder($order));

        app(\App\services\OrderNotificationService::class)
            ->notifyKitchenNewOrder($order);

        /*
    |--------------------------------------------------------------------------
    | Keep the production response unchanged
    |--------------------------------------------------------------------------
    */

        return response()->json([
            'message' => 'Order created successfully',
            'order' => new OrderResource($order),
            'estimated_time' => $estimated_time,
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

    public function accept_order(
        Request $request,
        Order $order,
        OrderNotificationService $notificationService
    ): JsonResponse {
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
                    'status' => 'cancelled',
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
                    'status' => 'cancelled',
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
        ], 201);
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
}
