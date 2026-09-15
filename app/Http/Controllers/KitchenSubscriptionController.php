<?php

namespace App\Http\Controllers;

use App\Models\KitchenPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\KitchenPackageSubscription;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\services\FinancialTransactionService;
use App\services\Payments\KashierService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class KitchenSubscriptionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List active kitchen packages
    |--------------------------------------------------------------------------
    */

    public function packages(Request $request): JsonResponse
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if (!$user || $user->role !== 'kitchen') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Get active packages
        |--------------------------------------------------------------------------
        */

        $packages = KitchenPackage::query()
            ->where('active', true)
            ->orderBy('price')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'message' => 'Kitchen packages retrieved successfully',
            'data' => $packages->map(function (
                KitchenPackage $package
            ) {
                return [
                    'id' => $package->id,

                    'name' => $package->name,

                    'desc' => $package->desc,

                    'features' => is_array(
                        $package->features
                    )
                        ? $package->features
                        : (
                            $package->features
                            ? json_decode(
                                $package->features,
                                true
                            )
                            : []
                        ),

                    'price' => round(
                        (float) $package->price,
                        2
                    ),

                    'duration' => (int) $package->duration,

                    'duration_unit' =>
                    $package->duration_unit,

                    'active' => (bool) $package->active,
                ];
            })->values(),
        ]);
    }


    public function current(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Kitchen profile
        |--------------------------------------------------------------------------
        |
        | البروفايل قد لا يكون موجودًا حتى الآن؛
        | لأن السيناريو الحالي:
        |
        | 1. المستخدم يسجل.
        | 2. يختار الباقة.
        | 3. يدفع.
        | 4. ينشئ بروفايل المطبخ.
        |
        */

        $kitchen = $user->profile;

        /*
        |--------------------------------------------------------------------------
        | Base subscriptions query
        |--------------------------------------------------------------------------
        |
        | نبحث بالـ user_id دائمًا.
        | ولو البروفايل موجود نبحث أيضًا بالـ kitchen_id
        | لدعم الاشتراكات القديمة أو الاشتراكات المرتبطة بالبروفايل.
        |
        */

        $subscriptionsQuery = KitchenPackageSubscription::query()
            ->where(function ($query) use ($user, $kitchen) {
                $query->where('user_id', $user->id);

                if ($kitchen) {
                    $query->orWhere(
                        'kitchen_id',
                        $kitchen->id
                    );
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Link old subscriptions to the user
        |--------------------------------------------------------------------------
        |
        | في حالة وجود بروفايل ووجود اشتراكات قديمة مرتبطة فقط بالمطبخ،
        | نقوم بإضافة user_id لها.
        |
        */

        if ($kitchen) {
            KitchenPackageSubscription::query()
                ->where('kitchen_id', $kitchen->id)
                ->whereNull('user_id')
                ->update([
                    'user_id' => $user->id,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Link subscriptions to kitchen profile
        |--------------------------------------------------------------------------
        |
        | بعد إنشاء البروفايل، يتم ربط الاشتراكات التي تم إنشاؤها
        | قبل البروفايل بالـ kitchen_id.
        |
        */

        if ($kitchen) {
            KitchenPackageSubscription::query()
                ->where('user_id', $user->id)
                ->whereNull('kitchen_id')
                ->update([
                    'kitchen_id' => $kitchen->id,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Expire old active subscriptions
        |--------------------------------------------------------------------------
        */

        (clone $subscriptionsQuery)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => 'expired',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Get active subscription
        |--------------------------------------------------------------------------
        */

        $activeSubscription = (clone $subscriptionsQuery)
            ->with('package')
            ->where('status', 'active')
            ->where(function ($query) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();


        if ($activeSubscription && $activeSubscription->package) {

            $package = $activeSubscription->package;


            // Count used orders during current subscription period
            $usedOrders = Order::where(
                'kitchen_id',
                $activeSubscription->kitchen_id
            )
                ->whereBetween('created_at', [
                    $activeSubscription->starts_at,
                    $activeSubscription->expires_at
                ])
                ->count();



            $ordersFinished =
                $package->orders_limit > 0 &&
                $usedOrders >= $package->orders_limit;



            $dateFinished =
                $activeSubscription->expires_at &&
                now()->greaterThanOrEqualTo(
                    $activeSubscription->expires_at
                );



            if ($ordersFinished || $dateFinished) {


                $activeSubscription->update([
                    'status' => 'expired'
                ]);


                if ($kitchen) {

                    $kitchen->update([
                        'open_status' => 'closed'
                    ]);
                }


                $activeSubscription = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Get latest pending subscription
        |--------------------------------------------------------------------------
        */

        $pendingSubscription = (clone $subscriptionsQuery)
            ->with([
                'package',

                'paymentTransactions' => function ($query) {
                    $query
                        ->where('provider', 'kashier')
                        ->latest('id');
                },
            ])
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Pending payment
        |--------------------------------------------------------------------------
        */

        $pendingPayment = $pendingSubscription
            ? $pendingSubscription
            ->paymentTransactions
            ->first()
            : null;

        /*
        |--------------------------------------------------------------------------
        | Expire pending payment locally
        |--------------------------------------------------------------------------
        */

        if (
            $pendingPayment
            && $pendingPayment->status === 'pending'
            && $pendingPayment->expires_at
            && now()->greaterThanOrEqualTo(
                $pendingPayment->expires_at
            )
        ) {
            $pendingPayment->update([
                'status' => 'expired',

                'provider_status' =>
                $pendingPayment->provider_status
                    ?: 'EXPIRED',

                'failure_reason' =>
                'Payment session expired',
            ]);

            $pendingPayment->refresh();
        }

        /*
        |--------------------------------------------------------------------------
        | No active subscription
        |--------------------------------------------------------------------------
        */

        if (!$activeSubscription) {
            return response()->json([
                'status' => true,

                'message' =>
                'No active subscription found',

                'data' => [
                    'has_kitchen_profile' =>
                    $kitchen !== null,

                    'has_active_subscription' =>
                    false,

                    'subscription_required' =>
                    true,

                    'profile_required' =>
                    $kitchen === null,

                    'subscription' =>
                    null,

                    'pending_subscription' =>
                    $pendingSubscription
                        ? [
                            'id' =>
                            $pendingSubscription->id,

                            'user_id' =>
                            $pendingSubscription->user_id,

                            'kitchen_id' =>
                            $pendingSubscription->kitchen_id,

                            'package_id' =>
                            $pendingSubscription
                                ->kitchen_package_id,

                            'package_name' =>
                            $pendingSubscription
                                ->package_name,

                            'package_price' =>
                            round(
                                (float) $pendingSubscription
                                    ->package_price,
                                2
                            ),

                            'package_duration' =>
                            (int) $pendingSubscription
                                ->package_duration,

                            'duration_unit' =>
                            $pendingSubscription
                                ->duration_unit,

                            'status' =>
                            $pendingSubscription->status,

                            'starts_at' =>
                            $pendingSubscription
                                ->starts_at
                                ?->toISOString(),

                            'expires_at' =>
                            $pendingSubscription
                                ->expires_at
                                ?->toISOString(),

                            'paid_at' =>
                            $pendingSubscription
                                ->paid_at
                                ?->toISOString(),

                            'payment_reference' =>
                            $pendingSubscription
                                ->payment_reference,

                            'package' =>
                            $pendingSubscription->package
                                ? [
                                    'id' =>
                                    $pendingSubscription
                                        ->package
                                        ->id,

                                    'name' =>
                                    $pendingSubscription
                                        ->package
                                        ->name,

                                    'desc' =>
                                    $pendingSubscription
                                        ->package
                                        ->desc,

                                    'features' =>
                                    $pendingSubscription
                                        ->package
                                        ->features
                                        ?? [],

                                    'price' =>
                                    round(
                                        (float) $pendingSubscription
                                            ->package
                                            ->price,
                                        2
                                    ),

                                    'duration' =>
                                    (int) $pendingSubscription
                                        ->package
                                        ->duration,

                                    'duration_unit' =>
                                    $pendingSubscription
                                        ->package
                                        ->duration_unit,

                                    'active' =>
                                    (bool) $pendingSubscription
                                        ->package
                                        ->active,
                                ]
                                : null,

                            'payment' =>
                            $pendingPayment
                                ? [
                                    'payment_id' =>
                                    $pendingPayment->id,

                                    'session_id' =>
                                    $pendingPayment
                                        ->session_id,

                                    'reference' =>
                                    $pendingPayment
                                        ->merchant_reference,

                                    'payment_status' =>
                                    $pendingPayment
                                        ->status,

                                    'provider_status' =>
                                    $pendingPayment
                                        ->provider_status,

                                    'amount' =>
                                    round(
                                        (float) $pendingPayment
                                            ->amount,
                                        2
                                    ),

                                    'currency' =>
                                    $pendingPayment
                                        ->currency,

                                    'is_paid' =>
                                    $pendingPayment
                                        ->status === 'paid',

                                    'can_retry' =>
                                    in_array(
                                        $pendingPayment
                                            ->status,
                                        [
                                            'failed',
                                            'expired',
                                            'cancelled',
                                        ],
                                        true
                                    ),

                                    'failure_reason' =>
                                    $pendingPayment
                                        ->failure_reason,

                                    'paid_at' =>
                                    $pendingPayment
                                        ->paid_at
                                        ?->toISOString(),

                                    'verified_at' =>
                                    $pendingPayment
                                        ->verified_at
                                        ?->toISOString(),

                                    'expires_at' =>
                                    $pendingPayment
                                        ->expires_at
                                        ?->toISOString(),
                                ]
                                : null,
                        ]
                        : null,
                ],
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Active subscription helpers
        |--------------------------------------------------------------------------
        */

        $remainingDays = null;
        $remainingSeconds = null;

        if ($activeSubscription->expires_at) {
            if (
                now()->greaterThanOrEqualTo(
                    $activeSubscription->expires_at
                )
            ) {
                $remainingDays = 0;
                $remainingSeconds = 0;
            } else {
                $remainingSeconds = max(
                    now()->diffInSeconds(
                        $activeSubscription->expires_at,
                        false
                    ),
                    0
                );

                $remainingDays = max(
                    now()->diffInDays(
                        $activeSubscription->expires_at,
                        false
                    ),
                    0
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Active subscription response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,

            'message' =>
            'Kitchen subscription retrieved successfully',

            'data' => [
                'has_kitchen_profile' =>
                $kitchen !== null,

                'has_active_subscription' =>
                true,

                'subscription_required' =>
                false,

                'profile_required' =>
                $kitchen === null,

                'subscription' => [
                    'id' =>
                    $activeSubscription->id,

                    'user_id' =>
                    $activeSubscription->user_id,

                    'kitchen_id' =>
                    $activeSubscription->kitchen_id,

                    'package_id' =>
                    $activeSubscription
                        ->kitchen_package_id,

                    'package_name' =>
                    $activeSubscription
                        ->package_name,

                    'package_price' =>
                    round(
                        (float) $activeSubscription
                            ->package_price,
                        2
                    ),

                    'package_duration' =>
                    (int) $activeSubscription
                        ->package_duration,

                    'duration_unit' =>
                    $activeSubscription
                        ->duration_unit,

                    'status' =>
                    $activeSubscription->status,

                    'is_active' =>
                    true,

                    'starts_at' =>
                    $activeSubscription
                        ->starts_at
                        ?->toISOString(),

                    'expires_at' =>
                    $activeSubscription
                        ->expires_at
                        ?->toISOString(),

                    'paid_at' =>
                    $activeSubscription
                        ->paid_at
                        ?->toISOString(),

                    'remaining_days' =>
                    $remainingDays,

                    'remaining_seconds' =>
                    $remainingSeconds,

                    'payment_reference' =>
                    $activeSubscription
                        ->payment_reference,

                    'package' =>
                    $activeSubscription->package
                        ? [
                            'id' =>
                            $activeSubscription
                                ->package
                                ->id,

                            'name' =>
                            $activeSubscription
                                ->package
                                ->name,

                            'desc' =>
                            $activeSubscription
                                ->package
                                ->desc,

                            'features' =>
                            $activeSubscription
                                ->package
                                ->features
                                ?? [],

                            'price' =>
                            round(
                                (float) $activeSubscription
                                    ->package
                                    ->price,
                                2
                            ),

                            'duration' =>
                            (int) $activeSubscription
                                ->package
                                ->duration,

                            'duration_unit' =>
                            $activeSubscription
                                ->package
                                ->duration_unit,

                            'active' =>
                            (bool) $activeSubscription
                                ->package
                                ->active,
                        ]
                        : null,
                ],

                /*
            |--------------------------------------------------------------------------
            | Pending subscription
            |--------------------------------------------------------------------------
            |
            | قد يوجد اشتراك pending أثناء وجود اشتراك active،
            | في حالة محاولة شراء باقة جديدة ولم يكتمل دفعها.
            |
            */

                'pending_subscription' =>
                $pendingSubscription
                    ? [
                        'id' =>
                        $pendingSubscription->id,

                        'user_id' =>
                        $pendingSubscription->user_id,

                        'kitchen_id' =>
                        $pendingSubscription->kitchen_id,

                        'package_id' =>
                        $pendingSubscription
                            ->kitchen_package_id,

                        'package_name' =>
                        $pendingSubscription
                            ->package_name,

                        'package_price' =>
                        round(
                            (float) $pendingSubscription
                                ->package_price,
                            2
                        ),

                        'package_duration' =>
                        (int) $pendingSubscription
                            ->package_duration,

                        'duration_unit' =>
                        $pendingSubscription
                            ->duration_unit,

                        'status' =>
                        $pendingSubscription->status,

                        'payment' =>
                        $pendingPayment
                            ? [
                                'payment_id' =>
                                $pendingPayment->id,

                                'session_id' =>
                                $pendingPayment
                                    ->session_id,

                                'reference' =>
                                $pendingPayment
                                    ->merchant_reference,

                                'payment_status' =>
                                $pendingPayment
                                    ->status,

                                'provider_status' =>
                                $pendingPayment
                                    ->provider_status,

                                'amount' =>
                                round(
                                    (float) $pendingPayment
                                        ->amount,
                                    2
                                ),

                                'currency' =>
                                $pendingPayment
                                    ->currency,

                                'is_paid' =>
                                $pendingPayment
                                    ->status === 'paid',

                                'can_retry' =>
                                in_array(
                                    $pendingPayment
                                        ->status,
                                    [
                                        'failed',
                                        'expired',
                                        'cancelled',
                                    ],
                                    true
                                ),

                                'failure_reason' =>
                                $pendingPayment
                                    ->failure_reason,

                                'paid_at' =>
                                $pendingPayment
                                    ->paid_at
                                    ?->toISOString(),

                                'verified_at' =>
                                $pendingPayment
                                    ->verified_at
                                    ?->toISOString(),

                                'expires_at' =>
                                $pendingPayment
                                    ->expires_at
                                    ?->toISOString(),
                            ]
                            : null,
                    ]
                    : null,
            ],
        ], 200);
    }


    public function subscribe(
        Request $request,
        KitchenPackage $package,
        KashierService $kashierService
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
    | Kitchen profile
    |--------------------------------------------------------------------------
    |
    | الدفع مسموح قبل إنشاء البروفايل.
    | لذلك kitchen_id قد يكون null، ونعتمد أساسيًا على user_id.
    |
    */

        $kitchen = $user->profile;

        $kitchenId = $kitchen?->id;

        /*
    |--------------------------------------------------------------------------
    | Validate package
    |--------------------------------------------------------------------------
    */

        if (!$package->active) {
            return response()->json([
                'status' => false,
                'message' => 'This package is not active',
            ], 422);
        }

        $packagePrice = round(
            max((float) $package->price, 0),
            2
        );

        if ($packagePrice <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Package price must be greater than zero',
            ], 422);
        }

        if ((int) $package->duration <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Package duration is invalid',
            ], 422);
        }

        if (
            !in_array(
                $package->duration_unit,
                [
                    'day',
                    'month',
                    'year',
                ],
                true
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Package duration unit is invalid',
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Expire old pending payment sessions locally
    |--------------------------------------------------------------------------
    |
    | البحث أصبح باستخدام user_id بدل kitchen_id،
    | لأن المطبخ قد لا يملك Profile بعد.
    |
    */

        $expiredPendingPayments =
            PaymentTransaction::query()
            ->where('user_id', $user->id)
            ->whereNotNull('kitchen_subscription_id')
            ->where('provider', 'kashier')
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredPendingPayments as $expiredPayment) {
            DB::transaction(function () use (
                $expiredPayment
            ) {
                $expiredPayment->update([
                    'status' => 'expired',
                    'provider_status' => 'EXPIRED',
                    'failure_reason' =>
                    'Payment session expired',
                ]);

                KitchenPackageSubscription::query()
                    ->where(
                        'id',
                        $expiredPayment
                            ->kitchen_subscription_id
                    )
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'failed',
                    ]);
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Existing pending subscription
    |--------------------------------------------------------------------------
    |
    | البحث باستخدام user_id والباقة، وليس kitchen_id.
    |
    */

        $existingSubscription =
            KitchenPackageSubscription::query()
            ->with([
                'paymentTransactions' =>
                function ($query) {
                    $query
                        ->where(
                            'provider',
                            'kashier'
                        )
                        ->where(
                            'status',
                            'pending'
                        )
                        ->latest('id');
                },
            ])
            ->where('user_id', $user->id)
            ->where(
                'kitchen_package_id',
                $package->id
            )
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($existingSubscription) {
            $existingPayment =
                $existingSubscription
                ->paymentTransactions
                ->first();

            if (
                $existingPayment
                && $existingPayment->session_id
                && (
                    !$existingPayment->expires_at
                    || $existingPayment
                    ->expires_at
                    ->isFuture()
                )
            ) {
                return response()->json([
                    'status' => true,

                    'message' =>
                    'A pending subscription payment session already exists',

                    'data' => [
                        'has_kitchen_profile' =>
                        (bool) $kitchen,

                        'subscription_id' =>
                        $existingSubscription->id,

                        'payment_id' =>
                        $existingPayment->id,

                        'package_id' =>
                        $package->id,

                        'package_name' =>
                        $existingSubscription
                            ->package_name,

                        'session_id' =>
                        $existingPayment
                            ->session_id,

                        'amount' =>
                        round(
                            (float) $existingPayment
                                ->amount,
                            2
                        ),

                        'currency' =>
                        $existingPayment
                            ->currency,

                        'payment_status' =>
                        $existingPayment
                            ->status,

                        'provider_status' =>
                        $existingPayment
                            ->provider_status,

                        'expires_at' =>
                        $existingPayment
                            ->expires_at
                            ?->toISOString(),
                    ],
                ], 200);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Generate payment values
    |--------------------------------------------------------------------------
    */

        $expiresAt = now()->addMinutes(15);

        $merchantReference =
            $this->generateSubscriptionReference(
                (int) $user->id,
                (int) $package->id
            );

        try {
            /*
        |--------------------------------------------------------------------------
        | Create local subscription and payment
        |--------------------------------------------------------------------------
        */

            $localData = DB::transaction(function () use (
                $user,
                $kitchenId,
                $package,
                $packagePrice,
                $merchantReference,
                $expiresAt
            ) {
                /*
             * قفل حساب المستخدم بدل KitchenProfile؛
             * لأن البروفايل قد لا يكون موجودًا بعد.
             */
                $lockedUser =
                    \App\Models\User::query()
                    ->lockForUpdate()
                    ->findOrFail($user->id);

                /*
             * منع إنشاء Session معلقة أخرى لنفس المستخدم
             * ونفس الباقة.
             */
                $pendingPaymentExists =
                    PaymentTransaction::query()
                    ->where('user_id', $lockedUser->id)
                    ->whereNotNull(
                        'kitchen_subscription_id'
                    )
                    ->whereHas(
                        'kitchenSubscription',
                        function ($query) use (
                            $lockedUser,
                            $package
                        ) {
                            $query
                                ->where(
                                    'user_id',
                                    $lockedUser->id
                                )
                                ->where(
                                    'kitchen_package_id',
                                    $package->id
                                );
                        }
                    )
                    ->where('provider', 'kashier')
                    ->where('status', 'pending')
                    ->where(function ($query) {
                        $query
                            ->whereNull('expires_at')
                            ->orWhere(
                                'expires_at',
                                '>',
                                now()
                            );
                    })
                    ->exists();

                if ($pendingPaymentExists) {
                    throw new RuntimeException(
                        'A pending subscription payment session already exists'
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Create subscription
            |--------------------------------------------------------------------------
            */

                $subscription =
                    KitchenPackageSubscription::create([
                        /*
                     * الحساب الأساسي الذي يدفع الاشتراك.
                     */
                        'user_id' =>
                        $lockedUser->id,

                        /*
                     * قد يكون null لأن الدفع قبل إنشاء البروفايل.
                     */
                        'kitchen_id' =>
                        $kitchenId,

                        'kitchen_package_id' =>
                        $package->id,

                        /*
                     * Snapshot لبيانات الباقة.
                     */
                        'package_name' =>
                        $package->name,

                        'package_price' =>
                        $packagePrice,

                        'package_duration' =>
                        (int) $package->duration,

                        'duration_unit' =>
                        $package->duration_unit,

                        'status' =>
                        'pending',

                        'starts_at' =>
                        null,

                        'expires_at' =>
                        null,

                        'paid_at' =>
                        null,

                        'payment_reference' =>
                        $merchantReference,
                    ]);

                /*
            |--------------------------------------------------------------------------
            | Create payment transaction
            |--------------------------------------------------------------------------
            */

                $payment =
                    PaymentTransaction::create([
                        'order_id' =>
                        null,

                        'kitchen_subscription_id' =>
                        $subscription->id,

                        'user_id' =>
                        $lockedUser->id,

                        'provider' =>
                        'kashier',

                        'merchant_reference' =>
                        $merchantReference,

                        'session_id' =>
                        null,

                        'session_url' =>
                        null,

                        'provider_status' =>
                        'CREATING',

                        'provider_order_id' =>
                        null,

                        'transaction_id' =>
                        null,

                        'card_order_id' =>
                        null,

                        'amount' =>
                        $packagePrice,

                        'currency' =>
                        'EGP',

                        'status' =>
                        'pending',

                        'payment_method' =>
                        'online',

                        'refunded_amount' =>
                        0,

                        'failure_reason' =>
                        null,

                        'paid_at' =>
                        null,

                        'verified_at' =>
                        null,

                        'expires_at' =>
                        $expiresAt,

                        'create_response' =>
                        null,

                        'callback_payload' =>
                        null,

                        'verification_response' =>
                        null,
                    ]);

                return [
                    'subscription' =>
                    $subscription,

                    'payment' =>
                    $payment,
                ];
            });

            /** @var KitchenPackageSubscription $subscription */
            $subscription =
                $localData['subscription'];

            /** @var PaymentTransaction $payment */
            $payment =
                $localData['payment'];

            /*
        |--------------------------------------------------------------------------
        | Kashier merchant redirect
        |--------------------------------------------------------------------------
        */

            $merchantRedirect = trim(
                (string) config(
                    'services.kashier.merchant_redirect'
                )
            );

            if ($merchantRedirect === '') {
                throw new RuntimeException(
                    'Kashier merchant redirect URL is missing'
                );
            }

            if (
                !filter_var(
                    $merchantRedirect,
                    FILTER_VALIDATE_URL
                )
            ) {
                throw new RuntimeException(
                    'Kashier merchant redirect URL is invalid'
                );
            }

            /*
        |--------------------------------------------------------------------------
        | Kashier server webhook
        |--------------------------------------------------------------------------
        */

            $serverWebhook = trim(
                (string) config(
                    'services.kashier.webhook_url'
                )
            );

            if ($serverWebhook === '') {
                $serverWebhook =
                    route('kashier.webhook');
            }

            if (
                !filter_var(
                    $serverWebhook,
                    FILTER_VALIDATE_URL
                )
            ) {
                throw new RuntimeException(
                    'Kashier webhook URL is invalid'
                );
            }

            /*
        |--------------------------------------------------------------------------
        | Create Kashier payment session
        |--------------------------------------------------------------------------
        */

            $kashierResponse =
                $kashierService->createPaymentSession([
                    'expireAt' =>
                    $expiresAt
                        ->copy()
                        ->utc()
                        ->toISOString(),

                    'maxFailureAttempts' =>
                    3,

                    'paymentType' =>
                    'credit',

                    'amount' =>
                    number_format(
                        $packagePrice,
                        2,
                        '.',
                        ''
                    ),

                    'currency' =>
                    'EGP',

                    'order' =>
                    $merchantReference,

                    'merchantRedirect' =>
                    $merchantRedirect,

                    'display' =>
                    'ar',

                    'type' =>
                    'one-time',

                    'allowedMethods' =>
                    'card,wallet',

                    'redirectMethod' =>
                    null,

                    'failureRedirect' =>
                    false,

                    'defaultMethod' =>
                    'card',

                    'description' =>
                    'Kitchen package subscription: '
                        . $package->name,

                    'manualCapture' =>
                    false,

                    'customer' => [
                        'email' =>
                        $user->email
                            ?: 'kitchen+'
                            . $user->id
                            . '@programshouse.com',

                        'reference' =>
                        (string) $user->id,
                    ],

                    'saveCard' =>
                    'optional',

                    'retrieveSavedCard' =>
                    true,

                    'interactionSource' =>
                    'ECOMMERCE',

                    'enable3DS' =>
                    true,

                    'serverWebhook' =>
                    $serverWebhook,

                    'metaData' => [
                        'payment_type' =>
                        'kitchen_subscription',

                        'user_id' =>
                        $user->id,

                        /*
                     * قد يكون null قبل إنشاء البروفايل.
                     */
                        'kitchen_id' =>
                        $kitchenId,

                        'has_kitchen_profile' =>
                        (bool) $kitchen,

                        'subscription_id' =>
                        $subscription->id,

                        'package_id' =>
                        $package->id,

                        'payment_id' =>
                        $payment->id,
                    ],
                ]);

            /*
        |--------------------------------------------------------------------------
        | Extract session data
        |--------------------------------------------------------------------------
        */

            $sessionId =
                $kashierService->extractSessionId(
                    $kashierResponse
                );

            if (!$sessionId) {
                throw new RuntimeException(
                    'Kashier did not return session ID'
                );
            }

            $sessionUrl =
                $kashierService->extractSessionUrl(
                    $kashierResponse
                );

            $providerStatus =
                $kashierService->extractSessionStatus(
                    $kashierResponse
                );

            $session =
                $kashierService->extractSession(
                    $kashierResponse
                );

            $providerOrderId =
                data_get(
                    $session,
                    'orderId'
                );

            if (
                !$providerOrderId
                || strtoupper(
                    (string) $providerOrderId
                ) === 'NA'
            ) {
                $providerOrderId = null;
            }

            /*
        |--------------------------------------------------------------------------
        | Save Kashier session
        |--------------------------------------------------------------------------
        */

            $payment->update([
                'session_id' =>
                $sessionId,

                'session_url' =>
                $sessionUrl,

                'provider_status' =>
                $providerStatus,

                'provider_order_id' =>
                $providerOrderId,

                'create_response' =>
                $kashierResponse,
            ]);

            $payment->refresh();

            /*
        |--------------------------------------------------------------------------
        | Response to Flutter
        |--------------------------------------------------------------------------
        */

            return response()->json([
                'status' => true,

                'message' =>
                'Subscription payment session created successfully',

                'data' => [
                    'has_kitchen_profile' =>
                    (bool) $kitchen,

                    'subscription_id' =>
                    $subscription->id,

                    'payment_id' =>
                    $payment->id,

                    'package_id' =>
                    $package->id,

                    'package_name' =>
                    $subscription
                        ->package_name,

                    'package_duration' =>
                    $subscription
                        ->package_duration,

                    'duration_unit' =>
                    $subscription
                        ->duration_unit,

                    'session_id' =>
                    $payment->session_id,

                    'amount' =>
                    round(
                        (float) $payment->amount,
                        2
                    ),

                    'currency' =>
                    $payment->currency,

                    'payment_status' =>
                    $payment->status,

                    'provider_status' =>
                    $payment->provider_status,

                    'expires_at' =>
                    $payment->expires_at
                        ?->toISOString(),
                ],
            ], 201);
        } catch (Throwable $exception) {
            /*
        |--------------------------------------------------------------------------
        | Mark local records as failed
        |--------------------------------------------------------------------------
        */

            if (isset($payment)) {
                DB::transaction(function () use (
                    $payment,
                    $subscription,
                    $exception
                ) {
                    $payment->update([
                        'status' =>
                        'failed',

                        'provider_status' =>
                        'CREATE_FAILED',

                        'failure_reason' =>
                        $exception->getMessage(),
                    ]);

                    $subscription->update([
                        'status' =>
                        'failed',
                    ]);
                });
            }

            Log::error(
                'Create kitchen subscription payment session failed',
                [
                    'user_id' =>
                    $user->id,

                    'kitchen_id' =>
                    $kitchenId,

                    'package_id' =>
                    $package->id,

                    'subscription_id' =>
                    $subscription->id ?? null,

                    'payment_id' =>
                    $payment->id ?? null,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,

                'message' =>
                'Unable to create subscription payment session',

                'error' =>
                $exception->getMessage(),
            ], 502);
        }
    }








    public function verifySubscriptionPayment(
        Request $request,
        PaymentTransaction $payment,
        KashierService $kashierService,
        FinancialTransactionService $financialService
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
    | Kitchen account authorization
    |--------------------------------------------------------------------------
    |
    | الدفع قد يتم قبل إنشاء KitchenProfile.
    | لذلك لا نشترط وجود profile هنا.
    |
    */

        $role = $user->role instanceof \BackedEnum
            ? $user->role->value
            : $user->role;

        if ($role !== 'kitchen') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $kitchen = $user->profile;

        /*
    |--------------------------------------------------------------------------
    | Load subscription
    |--------------------------------------------------------------------------
    */

        $payment->load('kitchenSubscription');

        $subscription = $payment->kitchenSubscription;

        if (!$subscription) {
            return response()->json([
                'status' => false,
                'message' => 'Kitchen subscription payment not found',
            ], 404);
        }

        /*
    |--------------------------------------------------------------------------
    | Authorization by user_id
    |--------------------------------------------------------------------------
    |
    | user_id هو المرجع الأساسي قبل إنشاء البروفايل.
    | الاشتراكات القديمة يمكن التحقق منها باستخدام kitchen_id.
    |
    */

        $isSubscriptionOwner = false;

        if ($subscription->user_id !== null) {
            $isSubscriptionOwner =
                (int) $subscription->user_id ===
                (int) $user->id;
        } elseif (
            $kitchen
            && $subscription->kitchen_id !== null
        ) {
            $isSubscriptionOwner =
                (int) $subscription->kitchen_id ===
                (int) $kitchen->id;
        }

        if (!$isSubscriptionOwner) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Attach kitchen profile when available
    |--------------------------------------------------------------------------
    |
    | لو البروفايل اتعمل بعد إنشاء الاشتراك وقبل التحقق،
    | يتم ربط الاشتراك بالبروفايل تلقائيًا.
    |
    */

        if (
            $kitchen
            && $subscription->kitchen_id === null
        ) {
            $subscription->update([
                'kitchen_id' => $kitchen->id,
            ]);

            $subscription->refresh();
        }

        /*
    |--------------------------------------------------------------------------
    | Validate provider
    |--------------------------------------------------------------------------
    */

        if ($payment->provider !== 'kashier') {
            return response()->json([
                'status' => false,
                'message' => 'Payment provider is not Kashier',
            ], 422);
        }

        /*
    |--------------------------------------------------------------------------
    | Already paid
    |--------------------------------------------------------------------------
    |
    | لا نسجل الحركة المالية هنا مرة أخرى.
    | الحركة يتم تسجيلها وقت الانتقال الأول إلى paid فقط.
    |
    */

        if (
            $payment->status === 'paid'
            && $subscription->status === 'active'
        ) {
            return response()->json([
                'status' => true,
                'message' =>
                'Subscription payment is already verified',

                'data' => [
                    'has_kitchen_profile' =>
                    (bool) $kitchen,

                    'subscription_id' =>
                    $subscription->id,

                    'payment_id' =>
                    $payment->id,

                    'session_id' =>
                    $payment->session_id,

                    'user_id' =>
                    $subscription->user_id,

                    'kitchen_id' =>
                    $subscription->kitchen_id,

                    'payment_status' =>
                    $payment->status,

                    'subscription_status' =>
                    $subscription->status,

                    'is_paid' =>
                    true,

                    'starts_at' =>
                    $subscription
                        ->starts_at
                        ?->toISOString(),

                    'expires_at' =>
                    $subscription
                        ->expires_at
                        ?->toISOString(),

                    'paid_at' =>
                    $subscription
                        ->paid_at
                        ?->toISOString(),
                ],
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Validate session ID
    |--------------------------------------------------------------------------
    */

        if (!$payment->session_id) {
            return response()->json([
                'status' => false,
                'message' => 'Kashier session ID is missing',
            ], 422);
        }

        try {
            /*
        |--------------------------------------------------------------------------
        | Get Kashier session
        |--------------------------------------------------------------------------
        */

            $kashierResponse =
                $kashierService
                ->getSubscriptionPaymentSession(
                    $payment->session_id,
                    $payment->merchant_reference,
                    (float) $payment->amount
                );

            $session =
                $kashierService->extractSession(
                    $kashierResponse
                );

            if (!$session) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'Invalid Kashier session response',

                    'data' => [
                        'payment_id' =>
                        $payment->id,

                        'session_id' =>
                        $payment->session_id,

                        'kashier_response' =>
                        $kashierResponse,
                    ],
                ], 502);
            }

            /*
        |--------------------------------------------------------------------------
        | Validate returned session ID
        |--------------------------------------------------------------------------
        */

            $returnedSessionId =
                data_get($session, 'sessionId')
                ?? data_get($session, '_id');

            if (
                $returnedSessionId
                && (string) $returnedSessionId !==
                (string) $payment->session_id
            ) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'Payment session ID mismatch',
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | Validate payment reference
        |--------------------------------------------------------------------------
        */

            if (
                !$kashierService->sessionReferenceMatches(
                    $kashierResponse,
                    $payment->merchant_reference
                )
            ) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'Payment reference mismatch',
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | Validate merchant
        |--------------------------------------------------------------------------
        */

            if (
                !$kashierService->sessionMerchantMatches(
                    $kashierResponse
                )
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Merchant mismatch',
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | Validate amount
        |--------------------------------------------------------------------------
        */

            if (
                !$kashierService->sessionAmountMatches(
                    $kashierResponse,
                    (float) $payment->amount
                )
            ) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'Payment amount mismatch',
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | Validate currency
        |--------------------------------------------------------------------------
        */

            if (
                !$kashierService->sessionCurrencyMatches(
                    $kashierResponse
                )
            ) {
                return response()->json([
                    'status' => false,
                    'message' =>
                    'Payment currency mismatch',
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | Normalize status
        |--------------------------------------------------------------------------
        */

            $rawProviderStatus =
                $kashierService->extractSessionStatus(
                    $kashierResponse
                );

            $localStatus =
                $kashierService->normalizeSessionStatus(
                    $rawProviderStatus
                );

            $providerOrderId =
                data_get($session, 'orderId');

            if (
                !$providerOrderId
                || strtoupper(
                    (string) $providerOrderId
                ) === 'NA'
            ) {
                $providerOrderId =
                    $payment->provider_order_id;
            }

            $providerMethod =
                data_get($session, 'method')
                ?? data_get(
                    $session,
                    'paymentParams.defaultMethod'
                )
                ?? 'online';

            $transactionId =
                data_get($session, 'transactionId')
                ?? data_get(
                    $session,
                    'lastTransactionId'
                )
                ?? $payment->transaction_id;

            /*
        |--------------------------------------------------------------------------
        | Successful payment
        |--------------------------------------------------------------------------
        */

            if ($localStatus === 'paid') {
                DB::transaction(function () use (
                    $payment,
                    $subscription,
                    $kashierResponse,
                    $rawProviderStatus,
                    $providerOrderId,
                    $providerMethod,
                    $transactionId,
                    $financialService,
                    $kitchen,
                    $user
                ) {
                    $lockedPayment =
                        PaymentTransaction::query()
                        ->lockForUpdate()
                        ->findOrFail($payment->id);

                    $lockedSubscription =
                        KitchenPackageSubscription::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $subscription->id
                        );

                    /*
                |--------------------------------------------------------------------------
                | Attach profile inside transaction
                |--------------------------------------------------------------------------
                */

                    if (
                        $kitchen
                        && $lockedSubscription
                        ->kitchen_id === null
                    ) {
                        $lockedSubscription->update([
                            'kitchen_id' =>
                            $kitchen->id,
                        ]);

                        $lockedSubscription->refresh();
                    }

                    /*
                |--------------------------------------------------------------------------
                | Prevent duplicate success processing
                |--------------------------------------------------------------------------
                |
                | لو الدفع والاشتراك تم تفعيلهما سابقًا،
                | نحدث بيانات التحقق فقط بدون تسجيل حركة مالية جديدة.
                |
                */

                    if (
                        $lockedPayment->status === 'paid'
                        && $lockedSubscription->status === 'active'
                    ) {
                        $lockedPayment->update([
                            'provider_status' =>
                            $rawProviderStatus,

                            'verified_at' =>
                            now(),

                            'verification_response' =>
                            $kashierResponse,
                        ]);

                        return;
                    }

                    /*
                |--------------------------------------------------------------------------
                | Calculate subscription period
                |--------------------------------------------------------------------------
                */

                    $startsAt = now();

                    $expiresAt =
                        $this->calculateSubscriptionExpiresAt(
                            $startsAt,
                            (int) $lockedSubscription
                                ->package_duration,
                            $lockedSubscription
                                ->duration_unit
                        );

                    $paidAt = now();

                    /*
                |--------------------------------------------------------------------------
                | Update payment
                |--------------------------------------------------------------------------
                */

                    $lockedPayment->update([
                        'status' =>
                        'paid',

                        'provider_status' =>
                        $rawProviderStatus,

                        'payment_method' =>
                        $providerMethod,

                        'provider_order_id' =>
                        $providerOrderId,

                        'transaction_id' =>
                        $transactionId,

                        'failure_reason' =>
                        null,

                        'paid_at' =>
                        $lockedPayment->paid_at
                            ?? $paidAt,

                        'verified_at' =>
                        now(),

                        'verification_response' =>
                        $kashierResponse,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Expire old active subscription
                |--------------------------------------------------------------------------
                |
                | لو يوجد KitchenProfile نبحث بـ kitchen_id.
                | لو لا يوجد نبحث بـ user_id.
                |
                | لا نستخدم where(kitchen_id, null).
                |
                */

                    $oldSubscriptionsQuery =
                        KitchenPackageSubscription::query()
                        ->where(
                            'status',
                            'active'
                        )
                        ->where(
                            'id',
                            '!=',
                            $lockedSubscription->id
                        );

                    if (
                        $lockedSubscription
                        ->kitchen_id !== null
                    ) {
                        $oldSubscriptionsQuery->where(
                            'kitchen_id',
                            $lockedSubscription->kitchen_id
                        );
                    } else {
                        $oldSubscriptionsQuery->where(
                            'user_id',
                            $lockedSubscription->user_id
                                ?? $user->id
                        );
                    }

                    $oldSubscriptionsQuery->update([
                        'status' =>
                        'expired',

                        'expires_at' =>
                        now(),
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Activate subscription
                |--------------------------------------------------------------------------
                */

                    $lockedSubscription->update([
                        'user_id' =>
                        $lockedSubscription->user_id
                            ?? $user->id,

                        'kitchen_id' =>
                        $kitchen?->id
                            ?? $lockedSubscription
                            ->kitchen_id,

                        'status' =>
                        'active',

                        'starts_at' =>
                        $startsAt,

                        'expires_at' =>
                        $expiresAt,

                        'paid_at' =>
                        $paidAt,

                        'payment_reference' =>
                        $lockedPayment
                            ->merchant_reference,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Record financial transaction once
                |--------------------------------------------------------------------------
                |
                | هذا هو المكان الوحيد لتسجيل حركة دفع الاشتراك.
                |
                */

                    $financialService
                        ->recordKitchenSubscriptionPayment(
                            $lockedSubscription->fresh(),
                            $lockedPayment->fresh()
                        );
                });

                $payment->refresh();
                $subscription->refresh();

                return response()->json([
                    'status' => true,

                    'message' =>
                    'Subscription payment verified successfully',

                    'data' => [
                        'has_kitchen_profile' =>
                        (bool) $kitchen,

                        'subscription_id' =>
                        $subscription->id,

                        'payment_id' =>
                        $payment->id,

                        'session_id' =>
                        $payment->session_id,

                        'user_id' =>
                        $subscription->user_id,

                        'kitchen_id' =>
                        $subscription->kitchen_id,

                        'package_id' =>
                        $subscription
                            ->kitchen_package_id,

                        'package_name' =>
                        $subscription
                            ->package_name,

                        'amount' =>
                        round(
                            (float) $payment->amount,
                            2
                        ),

                        'currency' =>
                        $payment->currency,

                        'provider_status' =>
                        $payment->provider_status,

                        'payment_status' =>
                        $payment->status,

                        'subscription_status' =>
                        $subscription->status,

                        'is_paid' =>
                        true,

                        'starts_at' =>
                        $subscription
                            ->starts_at
                            ?->toISOString(),

                        'expires_at' =>
                        $subscription
                            ->expires_at
                            ?->toISOString(),

                        'paid_at' =>
                        $subscription
                            ->paid_at
                            ?->toISOString(),
                    ],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Cancelled payment
        |--------------------------------------------------------------------------
        */

            if ($localStatus === 'cancelled') {
                DB::transaction(function () use (
                    $payment,
                    $subscription,
                    $kashierResponse,
                    $rawProviderStatus
                ) {
                    $lockedPayment =
                        PaymentTransaction::query()
                        ->lockForUpdate()
                        ->findOrFail($payment->id);

                    if ($lockedPayment->status === 'paid') {
                        return;
                    }

                    $lockedPayment->update([
                        'status' =>
                        'cancelled',

                        'provider_status' =>
                        $rawProviderStatus,

                        'failure_reason' =>
                        'Kashier session was cancelled',

                        'verified_at' =>
                        now(),

                        'verification_response' =>
                        $kashierResponse,
                    ]);

                    KitchenPackageSubscription::query()
                        ->where(
                            'id',
                            $subscription->id
                        )
                        ->where(
                            'status',
                            'pending'
                        )
                        ->update([
                            'status' =>
                            'cancelled',
                        ]);
                });

                $payment->refresh();
                $subscription->refresh();

                return response()->json([
                    'status' => true,

                    'message' =>
                    'Subscription payment session is cancelled',

                    'data' => [
                        'has_kitchen_profile' =>
                        (bool) $kitchen,

                        'subscription_id' =>
                        $subscription->id,

                        'payment_id' =>
                        $payment->id,

                        'session_id' =>
                        $payment->session_id,

                        'provider_status' =>
                        $payment->provider_status,

                        'payment_status' =>
                        $payment->status,

                        'subscription_status' =>
                        $subscription->status,

                        'is_paid' =>
                        false,

                        'can_retry' =>
                        true,
                    ],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Pending, failed or expired
        |--------------------------------------------------------------------------
        */

            DB::transaction(function () use (
                $payment,
                $subscription,
                $kashierResponse,
                $rawProviderStatus,
                $localStatus
            ) {
                $lockedPayment =
                    PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                if ($lockedPayment->status === 'paid') {
                    return;
                }

                $isExpired =
                    $localStatus === 'expired'
                    || (
                        $lockedPayment->expires_at
                        && now()->greaterThanOrEqualTo(
                            $lockedPayment->expires_at
                        )
                    );

                $newPaymentStatus =
                    $isExpired
                    ? 'expired'
                    : (
                        $localStatus === 'failed'
                        ? 'failed'
                        : 'pending'
                    );

                $lockedPayment->update([
                    'status' =>
                    $newPaymentStatus,

                    'provider_status' =>
                    $rawProviderStatus,

                    'failure_reason' =>
                    match ($newPaymentStatus) {
                        'expired' =>
                        'Payment session expired',

                        'failed' =>
                        'Payment failed',

                        default =>
                        null,
                    },

                    'verified_at' =>
                    now(),

                    'verification_response' =>
                    $kashierResponse,
                ]);

                $newSubscriptionStatus =
                    match ($newPaymentStatus) {
                        'expired',
                        'failed' =>
                        'failed',

                        default =>
                        'pending',
                    };

                KitchenPackageSubscription::query()
                    ->where(
                        'id',
                        $subscription->id
                    )
                    ->where(
                        'status',
                        '!=',
                        'active'
                    )
                    ->update([
                        'status' =>
                        $newSubscriptionStatus,
                    ]);
            });

            $payment->refresh();
            $subscription->refresh();

            return response()->json([
                'status' => true,

                'message' =>
                match ($payment->status) {
                    'expired' =>
                    'Subscription payment session has expired',

                    'failed' =>
                    'Subscription payment failed',

                    default =>
                    'Subscription payment is still pending',
                },

                'data' => [
                    'has_kitchen_profile' =>
                    (bool) $kitchen,

                    'subscription_id' =>
                    $subscription->id,

                    'payment_id' =>
                    $payment->id,

                    'session_id' =>
                    $payment->session_id,

                    'provider_status' =>
                    $payment->provider_status,

                    'payment_status' =>
                    $payment->status,

                    'subscription_status' =>
                    $subscription->status,

                    'is_paid' =>
                    false,

                    'can_retry' =>
                    in_array(
                        $payment->status,
                        [
                            'failed',
                            'expired',
                            'cancelled',
                        ],
                        true
                    ),

                    'expires_at' =>
                    $payment->expires_at
                        ?->toISOString(),
                ],
            ]);
        } catch (Throwable $exception) {

            return response()->json([
                'status' => false,
                'message' => 'Unable to verify subscription payment',

                'debug' => [
                    'exception' => get_class($exception),
                    'message'   => $exception->getMessage(),
                    'file'      => $exception->getFile(),
                    'line'      => $exception->getLine(),

                    'trace' => collect($exception->getTrace())
                        ->take(10)
                        ->map(function ($trace) {
                            return [
                                'file'     => $trace['file'] ?? null,
                                'line'     => $trace['line'] ?? null,
                                'class'    => $trace['class'] ?? null,
                                'function' => $trace['function'] ?? null,
                            ];
                        })
                        ->values(),
                ],
            ], 500);
        }
    }





    public function subscriptionPaymentStatus(
        Request $request,
        PaymentTransaction $payment
    ): JsonResponse {
        $user = $request->user();

        if (!$user || $user->role !== 'kitchen') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $kitchen = $user->profile;

        if (!$kitchen) {
            return response()->json([
                'status' => false,
                'message' => 'Kitchen profile not found',
            ], 404);
        }

        $payment->load('kitchenSubscription');

        $subscription =
            $payment->kitchenSubscription;

        if (!$subscription) {
            return response()->json([
                'status' => false,
                'message' =>
                'Kitchen subscription payment not found',
            ], 404);
        }

        if (
            (int) $subscription->kitchen_id !==
            (int) $kitchen->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Local expiration
    |--------------------------------------------------------------------------
    */

        if (
            $payment->status === 'pending'
            && $payment->expires_at
            && now()->greaterThanOrEqualTo(
                $payment->expires_at
            )
        ) {
            DB::transaction(function () use (
                $payment,
                $subscription
            ) {
                $lockedPayment =
                    PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                if ($lockedPayment->status !== 'pending') {
                    return;
                }

                $lockedPayment->update([
                    'status' => 'expired',
                    'provider_status' => 'EXPIRED',
                    'failure_reason' =>
                    'Payment session expired',
                ]);

                KitchenPackageSubscription::query()
                    ->where('id', $subscription->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'failed',
                    ]);
            });

            $payment->refresh();
            $subscription->refresh();
        }

        $isPaid =
            $payment->status === 'paid'
            && $subscription->status === 'active';

        $isFinal =
            in_array(
                $payment->status,
                [
                    'paid',
                    'failed',
                    'cancelled',
                    'expired',
                    'refunded',
                ],
                true
            );

        $remainingSeconds = null;

        if ($payment->expires_at) {
            $remainingSeconds =
                now()->greaterThanOrEqualTo(
                    $payment->expires_at
                )
                ? 0
                : max(
                    now()->diffInSeconds(
                        $payment->expires_at,
                        false
                    ),
                    0
                );
        }

        return response()->json([
            'status' => true,
            'message' =>
            'Subscription payment status retrieved successfully',

            'data' => [
                'subscription_id' =>
                $subscription->id,

                'payment_id' =>
                $payment->id,

                'session_id' =>
                $payment->session_id,

                'package_id' =>
                $subscription
                    ->kitchen_package_id,

                'package_name' =>
                $subscription
                    ->package_name,

                'reference' =>
                $payment
                    ->merchant_reference,

                'amount' =>
                round(
                    (float) $payment->amount,
                    2
                ),

                'currency' =>
                $payment->currency,

                'provider' =>
                $payment->provider,

                'provider_status' =>
                $payment->provider_status,

                'payment_status' =>
                $payment->status,

                'subscription_status' =>
                $subscription->status,

                'is_paid' =>
                $isPaid,

                'is_final' =>
                $isFinal,

                'can_retry' =>
                in_array(
                    $payment->status,
                    [
                        'failed',
                        'cancelled',
                        'expired',
                    ],
                    true
                )
                    && $subscription->status !== 'active',

                'failure_reason' =>
                $payment->failure_reason,

                'paid_at' =>
                $payment->paid_at
                    ?->toISOString(),

                'verified_at' =>
                $payment->verified_at
                    ?->toISOString(),

                'payment_expires_at' =>
                $payment->expires_at
                    ?->toISOString(),

                'remaining_seconds' =>
                $remainingSeconds,

                'subscription_starts_at' =>
                $subscription->starts_at
                    ?->toISOString(),

                'subscription_expires_at' =>
                $subscription->expires_at
                    ?->toISOString(),
            ],
        ]);
    }








    private function generateSubscriptionReference(
        int $userId,
        int $packageId
    ): string {
        do {
            $reference =
                'KITCHEN-SUB-USER-'
                . $userId
                . '-'
                . $packageId
                . '-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(
                    Str::random(6)
                );
        } while (
            PaymentTransaction::query()
            ->where(
                'merchant_reference',
                $reference
            )
            ->exists()
        );

        return $reference;
    }


    private function calculateSubscriptionExpiresAt(
        Carbon $startsAt,
        int $duration,
        string $durationUnit
    ): Carbon {
        if ($duration <= 0) {
            throw new RuntimeException(
                'Subscription duration must be greater than zero'
            );
        }

        return match ($durationUnit) {
            'day' =>
            $startsAt
                ->copy()
                ->addDays($duration),

            'year' =>
            $startsAt
                ->copy()
                ->addYears($duration),

            'month' =>
            $startsAt
                ->copy()
                ->addMonths($duration),

            default =>
            throw new RuntimeException(
                'Invalid subscription duration unit'
            ),
        };
    }
}
