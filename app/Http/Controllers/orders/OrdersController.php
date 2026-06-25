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
            'user_address_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.meal_id' => 'required|exists:meals,id',
            'items.*.quantity' => 'required|integer|min:1',
            'receive_date' => 'nullable|date|before_or_equal:today + 7 days',
            'receive_time' => 'required_with:receive_date',
            'book_for_later' => 'required_with:receive_date|boolean'
        ]);

        $user = $request->user();
        $kitchen = KitchenProfile::find($validated['kitchen_id']);

        // check if kitchen is open now
        if (in_array($kitchen->open_status, ['closed', 'busy'])) {
            return response()->json([
                'message' => 'this kitchen may closed or busy right now you can order latter'
            ], 422);
        }

        // check if one meals is unavailable
        foreach ($validated['items'] as $item) {
            $meal = \App\Models\Meal::find($item['meal_id']);
            if(!$meal->availability) {
                return response()->json([
                    'message' => 'one of your items is unavailable now' ,
                    'item' => [
                        'name' => $meal->name ,
                        'id' => $meal->id,
                        'image' => $meal->image ? config('app.url') . '/storage/' . $meal->image : null
                    ]
                ] , 422);
                break;
            }
        }


        // Calculate total
        $total = 0;
        foreach ($validated['items'] as $item) {
            $meal = \App\Models\Meal::find($item['meal_id']);
            $total += $meal->price * $item['quantity'];
        }

        $lastOrder = Order::latest('id')->first();

$nextId = $lastOrder ? $lastOrder->id + 1 : 1;

$orderNumber = 'ORD-' . now()->format('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'kitchen_id' => $validated['kitchen_id'],
             'number' => $orderNumber,
            'total' => $total,
            'user_address_id' => $validated['user_address_id'] ?? null,
            'receive_date' => $validated['receive_date'] ?? null,
            'receive_time' => $validated['receive_time'] ?? null,
            'book_for_later' => $validated['book_for_later'] ?? false,
        ]);

        // calculate estimated time
        $estimated_time = 0;
        // Calculate estimated time: take the largest quantity among the items
        $quantities = array_column($validated['items'], 'quantity');
        $estimated_time = !empty($quantities) ? max($quantities) : 0;

        // Create order items
        foreach ($validated['items'] as $item) {
            $meal = \App\Models\Meal::find($item['meal_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'meal_id' => $item['meal_id'],
                'quantity' => $item['quantity'],
                'price' => $meal->price,
            ]);
        }

        $order->load(['user', 'kitchen', 'items.meal']);

        // fire event
        broadcast(new CreateOrder($order));

        return response()->json([
            'message' => 'Order created successfully',
            'order' => new OrderResource($order),
            'estimated_time' => $estimated_time
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Check if user has access to this order
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

        $order->load(['user', 'kitchen', 'items.meal', 'userAddress']);

        return response()->json([
            'order' => new OrderResource($order)
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

    public function accept_order(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        // check if user has access to accept this order
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

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected,ready_to_deliver,preparing'
        ]);

        if($order->status === $validated['status']) {
            return response()->json([
                'message' => "order is already " . $validated['status'],
            ], 400);
        }



        $order_updated = $order->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'message' => 'Order ' . $validated['status'] . ' successfully',
            'order_status' => $validated['status']
        ]);
    }

    function cancel_order(Request $request, Order $order)
    {
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

        return response()->json([
            'message' => "order canceled successfully"
        ], 201);
    }

    public function deliver_order(Request $request, Order $order, WalletService $walletService)
    {
        $validated = $request->validate([
            'status' => 'required|in:delivered,cancelled,received_by_delivery'
        ]);

        $order_updated = $order->update([
            'status' => $validated['status']
        ]);

        // if order done
        if ($validated['status'] == 'delivered') {
            // set deliver timestamp
            $order->update([
                'delivered_at' => now()
            ]);

            // create transaction
            $walletService->credit($order, $request);
        } else if ($validated['status'] == 'cancelled') {
            $walletService->credit($order, $request);
        }

        return response()->json([
            'message' => 'Order ' . $validated['status'] . ' successfully',
            'order_status' => $validated['status']
        ]);
    }
}
