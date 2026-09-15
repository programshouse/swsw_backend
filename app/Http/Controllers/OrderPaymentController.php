<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\services\FinancialTransactionService;
use App\services\Payments\KashierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Illuminate\Validation\Rule;


class OrderPaymentController extends Controller
{
    public function options(
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

        $order->refresh();

        $totalBeforeDiscount = round(
            (float) $order->total_before_discount,
            2
        );

        $discountValue = round(
            (float) $order->discount_value,
            2
        );

        $remainingAmount = round(
            max((float) $order->total, 0),
            2
        );

        $isFullyPaid =
            $order->payment_status === 'paid'
            || $remainingAmount <= 0;

        $paymentExpiresAt = $order->payment_expires_at;
        $isExpired = false;
        $remainingSeconds = null;

        if ($paymentExpiresAt) {
            $isExpired = now()->greaterThanOrEqualTo(
                $paymentExpiresAt
            );

            $remainingSeconds = $isExpired
                ? 0
                : max(
                    now()->diffInSeconds(
                        $paymentExpiresAt,
                        false
                    ),
                    0
                );
        }

        $availableMethods = [];

        if (
            $order->status === 'accepted_by_kitchen'
            && !$isFullyPaid
            && !$isExpired
            && in_array(
                $order->payment_status,
                [
                    'awaiting_payment',
                    'failed',
                ],
                true
            )
        ) {
            $availableMethods = [
                'cash',
                'online',
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Order payment options retrieved successfully',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->number,
                'order_status' => $order->status,
                'total_before_discount' => $totalBeforeDiscount,
                'discount_value' => $discountValue,
                'remaining_amount' => $remainingAmount,
                'currency' => 'EGP',
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'is_fully_paid' => $isFullyPaid,
                'is_payment_required' =>
                !$isFullyPaid && $remainingAmount > 0,
                'payment_expires_at' =>
                $paymentExpiresAt?->toISOString(),
                'is_expired' => $isExpired,
                'remaining_seconds' => $remainingSeconds,
                'available_methods' => $availableMethods,
            ],
        ]);
    }

    public function createKashierPayment(Request $request, Order $order, KashierService $kashierService): JsonResponse
    {
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
        | Validate payment method
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'allowed_methods' => [
                'nullable',
                'string',
                Rule::in([
                    'CARD',
                    'WALLET',
                    'APPLEPAY',
                ]),
            ],
        ]);

        $requestedMethod = strtoupper(
            trim($validated['allowed_methods'] ?? 'CARD')
        );

        $kashierAllowedMethod = match ($requestedMethod) {
            'WALLET' => 'wallet',
            'APPLEPAY' => 'applepay',
            default => 'card',
        };

        /*
        |--------------------------------------------------------------------------
        | Authorization
        |--------------------------------------------------------------------------
        */

        if ((int) $order->user_id !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Load order relations
        |--------------------------------------------------------------------------
        */

        $order->load('user');
        $order->refresh();

        /*
        |--------------------------------------------------------------------------
        | Validate order status
        |--------------------------------------------------------------------------
        */



        $allowedPaymentStatuses = [
            'accepted_by_kitchen',
            'preparing',
        ];

        if (
            !in_array(
                $order->status,
                $allowedPaymentStatuses,
                true
            )
        ) {
            return response()->json([
                'status' => false,
                'message' =>
                'Order must be accepted by kitchen before payment',
                'order_status' => $order->status,
            ], 422);
        }

        if ($order->payment_method === 'cash') {
    return response()->json([
        'status' => false,
        'message' =>
        'This order is set to cash payment and cannot be paid online.',
        'data' => [
            'order_id' => $order->id,
            'payment_method' => $order->payment_method,
        ],
    ], 422);
}

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate successful payment
        |--------------------------------------------------------------------------
        */

        if ($order->payment_status === 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Order is already paid',
                'data' => [
                    'order_id' => $order->id,

                    'payment_status' =>
                    $order->payment_status,

                    'paid_at' =>
                    $order->paid_at?->toISOString(),
                ],
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Remaining amount
        |--------------------------------------------------------------------------
        */

        $amount = round(
            max((float) $order->total, 0),
            2
        );

        /*
        |--------------------------------------------------------------------------
        | Fully paid by cash code
        |--------------------------------------------------------------------------
        */

        if ($amount <= 0) {
            $order->update([
                'payment_method' => 'cash_code',
                'payment_status' => 'paid',
                'paid_at' => $order->paid_at ?? now(),
                'payment_expires_at' => null,
            ]);

            return response()->json([
                'status' => true,

                'message' =>
                'Order is fully paid using cash code',

                'data' => [
                    'order_id' => $order->id,
                    'amount' => 0,
                    'currency' => 'EGP',
                    'payment_method' => 'cash_code',
                    'payment_status' => 'paid',
                    'is_paid' => true,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve session expiration
        |--------------------------------------------------------------------------
        */

        $canCreateNewExpiration = in_array(
            $order->payment_status,
            [
                'expired',
                'failed',
                'cancelled',
            ],
            true
        );

        if (
            $canCreateNewExpiration
            || !$order->payment_expires_at
            || now()->greaterThanOrEqualTo(
                $order->payment_expires_at
            )
        ) {
            $expiresAt = now()->addMinutes(15);
        } else {
            $expiresAt = $order->payment_expires_at;
        }

        /*
        |--------------------------------------------------------------------------
        | Existing pending payment
        |--------------------------------------------------------------------------
        */

        $existingPayment =
            PaymentTransaction::query()
            ->where('order_id', $order->id)
            ->where('user_id', $user->id)
            ->where('provider', 'kashier')
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $existingSessionMethod = null;

        if ($existingPayment && $existingPayment->create_response) {
            $createResponse = $existingPayment->create_response;

            if (is_string($createResponse)) {
                $decodedResponse = json_decode(
                    $createResponse,
                    true
                );

                if (json_last_error() === JSON_ERROR_NONE) {
                    $createResponse = $decodedResponse;
                }
            }

            $existingSessionMethod = strtolower(
                trim(
                    (string) (
                        data_get(
                            $createResponse,
                            'response.paymentSession.paymentParams.allowedMethods'
                        )
                        ?? data_get(
                            $createResponse,
                            'paymentSession.paymentParams.allowedMethods'
                        )
                        ?? data_get(
                            $createResponse,
                            'paymentParams.allowedMethods'
                        )
                        ?? data_get(
                            $createResponse,
                            'response.allowedMethods'
                        )
                        ?? data_get(
                            $createResponse,
                            'allowedMethods'
                        )
                        ?? ''
                    )
                )
            );
        }

        /*
        * نرجع الجلسة القديمة فقط لو ما زالت صالحة
        * وطريقة الدفع مطابقة للطريقة المطلوبة.
        */

        if (
            $existingPayment
            && $existingPayment->session_id
            && (
                !$existingPayment->expires_at
                || $existingPayment->expires_at->isFuture()
            )
            && $existingSessionMethod === $kashierAllowedMethod
        ) {
            return response()->json([
                'status' => true,

                'message' =>
                'A pending payment session already exists',

                'data' => [
                    'payment_id' =>
                    $existingPayment->id,

                    'order_id' =>
                    $order->id,

                    'order_number' =>
                    $order->number,

                    'reference' =>
                    $existingPayment
                        ->merchant_reference,

                    'session_id' =>
                    $existingPayment->session_id,

                    'amount' =>
                    (float) $existingPayment->amount,

                    'currency' =>
                    $existingPayment->currency,

                    'payment_status' =>
                    $existingPayment->status,

                    'provider_status' =>
                    $existingPayment
                        ->provider_status,

                    'expires_at' =>
                    $existingPayment->expires_at
                        ?->toISOString(),
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Cancel old pending session when payment method changes
        |--------------------------------------------------------------------------
        */

        if (
            $existingPayment
            && $existingPayment->status === 'pending'
            && $existingPayment->session_id
            && $existingSessionMethod !== $kashierAllowedMethod
        ) {
            $existingPayment->update([
                'status' => 'cancelled',
                'provider_status' => 'METHOD_CHANGED',
                'failure_reason' =>
                'Payment method changed from '
                    . ($existingSessionMethod ?: 'unknown')
                    . ' to '
                    . $kashierAllowedMethod,
            ]);

            $existingPayment = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Expire old local pending payment
        |--------------------------------------------------------------------------
        */

        if (
            $existingPayment
            && $existingPayment->status === 'pending'
            && $existingPayment->expires_at
            && now()->greaterThanOrEqualTo(
                $existingPayment->expires_at
            )
        ) {
            $existingPayment->update([
                'status' => 'expired',
                'provider_status' => 'EXPIRED',
                'failure_reason' =>
                'Payment session expired',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate reference
        |--------------------------------------------------------------------------
        */

        $merchantReference =
            $this->generateMerchantReference(
                $order
            );

        try {
            /*
        |--------------------------------------------------------------------------
        | Create local transaction
        |--------------------------------------------------------------------------
        */

            $payment = DB::transaction(function () use (
                $order,
                $user,
                $amount,
                $merchantReference,
                $expiresAt
            ) {
                $lockedOrder =
                    Order::query()
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if (
                    $lockedOrder->payment_status === 'paid'
                ) {
                    throw new RuntimeException(
                        'Order is already paid'
                    );
                }

                $pendingPaymentExists =
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
                        'A pending payment session already exists'
                    );
                }

                $payment =
                    PaymentTransaction::create([
                        'order_id' =>
                        $lockedOrder->id,

                        'kitchen_subscription_id' =>
                        null,

                        'user_id' =>
                        $user->id,

                        'provider' =>
                        'kashier',

                        'merchant_reference' =>
                        $merchantReference,

                        'session_id' => null,
                        'session_url' => null,

                        'provider_status' =>
                        'CREATING',

                        'provider_order_id' => null,
                        'transaction_id' => null,
                        'card_order_id' => null,

                        'amount' =>
                        $amount,

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
//////////////////////////هنا
                $lockedOrder->update([
                    'payment_method' =>
                    'cash',

                    'payment_status' =>
                    'pending',

                    'payment_reference' =>
                    $merchantReference,

                    'payment_expires_at' =>
                    $expiresAt,
                ]);

                return $payment;
            });

            /*
        |--------------------------------------------------------------------------
        | Kashier callback URLs
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

            $sessionPayload = [
                'expireAt' =>
                $expiresAt
                    ->copy()
                    ->utc()
                    ->toISOString(),

                'maxFailureAttempts' =>
                3,

                'amount' =>
                number_format(
                    $amount,
                    2,
                    '.',
                    ''
                ),

                'currency' =>
                'EGP',

                'order' =>
                $merchantReference,

                /*
             * يرجع إلى صفحة Blade العامة.
             */
                'merchantRedirect' =>
                $merchantRedirect,

                'display' =>
                'ar',

                'type' =>
                'one-time',

                'allowedMethods' =>
                $kashierAllowedMethod,

                'redirectMethod' =>
                null,

                'failureRedirect' =>
                false,

                'defaultMethod' =>
                $kashierAllowedMethod,

                'description' =>
                'Payment for order '
                    . $order->number,

                'manualCapture' =>
                false,

                'customer' => [
                    'email' =>
                    $order->user?->email
                        ?: 'customer+'
                        . $user->id
                        . '@programshouse.com',

                    'reference' =>
                    (string) $user->id,
                ],

                /*
             * تعطيل حفظ واسترجاع البطاقة مؤقتًا
             * لعزل مشكلة Card و3DS.
             */
                'saveCard' =>
                'disabled',

                'retrieveSavedCard' =>
                false,

                'interactionSource' =>
                'ECOMMERCE',

                'enable3DS' =>
                true,

                /*
             * إشعار Server-to-Server.
             */
                'serverWebhook' =>
                $serverWebhook,

                'metaData' => [
                    'payment_type' =>
                    'order_payment',

                    'order_id' =>
                    $order->id,

                    'order_number' =>
                    $order->number,

                    'payment_id' =>
                    $payment->id,

                    'discount_value' =>
                    round(
                        (float) $order
                            ->discount_value,
                        2
                    ),
                ],
            ];

            Log::info(
                'Creating Kashier payment session',
                [
                    'order_id' =>
                    $order->id,

                    'payment_id' =>
                    $payment->id,

                    'requested_method' =>
                    $requestedMethod,

                    'kashier_allowed_method' =>
                    $kashierAllowedMethod,

                    'allowedMethods' =>
                    $sessionPayload['allowedMethods']
                        ?? null,

                    'defaultMethod' =>
                    $sessionPayload['defaultMethod']
                        ?? null,
                ]
            );

            $kashierResponse =
                $kashierService->createPaymentSession(
                    $sessionPayload
                );

            /*
        |--------------------------------------------------------------------------
        | Extract session
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
        | Save session
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
        | Response
        |--------------------------------------------------------------------------
        */

            return response()->json([
                'status' => true,

                'message' =>
                'Payment session created successfully',

                'data' => [
                    'payment_id' =>
                    $payment->id,

                    'order_id' =>
                    $order->id,

                    'order_number' =>
                    $order->number,

                    'reference' =>
                    $payment
                        ->merchant_reference,

                    'session_id' =>
                    $payment->session_id,

                    'amount' =>
                    (float) $payment->amount,

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
        | Mark payment as failed
        |--------------------------------------------------------------------------
        */

            if (isset($payment)) {
                DB::transaction(function () use (
                    $payment,
                    $order,
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

                    $freshOrder =
                        Order::query()
                        ->lockForUpdate()
                        ->find($order->id);

                    if (
                        $freshOrder
                        && $freshOrder->payment_status !==
                        'paid'
                    ) {
                        $freshOrder->update([
                            'payment_status' =>
                            'awaiting_payment',

                            'payment_method' =>
                            null,
                        ]);
                    }
                });
            }

            Log::error(
                'Create Kashier payment session failed',
                [
                    'order_id' =>
                    $order->id,

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
                'Unable to create payment session',

                'error' =>
                $exception->getMessage(),
            ], 502);
        }
    }

    public function status(
        Request $request,
        PaymentTransaction $payment
    ): JsonResponse {
        $user = $request->user();

        if ((int) $payment->user_id !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $payment->load('order');

        if (!$payment->order) {
            return response()->json([
                'status' => false,
                'message' => 'Payment order not found',
            ], 404);
        }

        $order = $payment->order;

        if (
            $payment->status === 'pending'
            && $payment->expires_at
            && now()->greaterThanOrEqualTo($payment->expires_at)
        ) {
            DB::transaction(function () use ($payment, $order) {
                $lockedPayment = PaymentTransaction::query()
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

                $lockedOrder = Order::query()
                    ->lockForUpdate()
                    ->find($order->id);

                if (
                    $lockedOrder
                    && $lockedOrder->payment_status !== 'paid'
                ) {
                    $lockedOrder->update([
                        'payment_status' => 'expired',
                    ]);
                }
            });

            $payment->refresh();
            $payment->load('order');
            $order = $payment->order;
        }

        $isPaid = $payment->status === 'paid';

        $isFinal = in_array(
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
            $remainingSeconds = now()->greaterThanOrEqualTo(
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
            'message' => 'Payment status retrieved successfully',
            'data' => [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'order_number' => $order->number,
                'order_status' => $order->status,
                'reference' =>
                $payment->merchant_reference,
                'session_id' =>
                $payment->session_id,
                'provider_order_id' =>
                $payment->provider_order_id,
                'transaction_id' =>
                $payment->transaction_id,
                'provider' =>
                $payment->provider,
                'provider_status' =>
                $payment->provider_status,
                'amount' =>
                round((float) $payment->amount, 2),
                'currency' =>
                $payment->currency,
                'payment_method' =>
                $payment->payment_method,
                'payment_status' =>
                $payment->status,
                'order_payment_status' =>
                $order->payment_status,
                'is_paid' => $isPaid,
                'is_final' => $isFinal,
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
                    && $order->payment_status !== 'paid',
                'failure_reason' =>
                $payment->failure_reason,
                'paid_at' =>
                $payment->paid_at?->toISOString(),
                'verified_at' =>
                $payment->verified_at?->toISOString(),
                'expires_at' =>
                $payment->expires_at?->toISOString(),
                'remaining_seconds' =>
                $remainingSeconds,
            ],
        ]);
    }

    public function verify(
        Request $request,
        PaymentTransaction $payment,
        KashierService $kashierService,
        FinancialTransactionService $financialService
    ): JsonResponse {
        $user = $request->user();

        /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

        if ((int) $payment->user_id !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Load order
    |--------------------------------------------------------------------------
    */

        $payment->load('order');

        if (!$payment->order) {
            return response()->json([
                'status' => false,
                'message' => 'Payment order not found',
            ], 404);
        }

        $order = $payment->order;

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
    */

        if (
            $payment->status === 'paid'
            && $order->payment_status === 'paid'
        ) {
            /*
         * ضمان تسجيل القيود المالية لو لم تكن مسجلة.
         * idempotency_key يمنع التكرار.
         */
            $financialService->recordOwnerOrderPayment(
                $order->fresh(),
                'online',
                $payment->merchant_reference
            );

            $financialService->recordCashCodeDiscount(
                $order->fresh()
            );

            return response()->json([
                'status' => true,
                'message' => 'Payment is already verified',
                'data' => [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'session_id' => $payment->session_id,
                    'provider_status' => $payment->provider_status,
                    'payment_status' => 'paid',
                    'order_payment_status' =>
                    $order->payment_status,
                    'is_paid' => true,
                    'paid_at' =>
                    $payment->paid_at?->toISOString(),
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
        | Retrieve session from Kashier
        |--------------------------------------------------------------------------
        */

            $kashierResponse =
                $kashierService->getPaymentSession(
                    $payment->session_id
                );

            Log::info('Kashier get session response', [
                'payment_id' => $payment->id,
                'session_id' => $payment->session_id,
                'response' => $kashierResponse,
            ]);

            /*
        |--------------------------------------------------------------------------
        | Extract session
        |--------------------------------------------------------------------------
        */

            $session = $kashierService->extractSession(
                $kashierResponse
            );

            if (!$session) {
                Log::error(
                    'Invalid Kashier session response',
                    [
                        'payment_id' => $payment->id,
                        'session_id' => $payment->session_id,
                        'response' => $kashierResponse,
                    ]
                );

                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Kashier session response',
                    'data' => [
                        'payment_id' => $payment->id,
                        'session_id' => $payment->session_id,
                        'kashier_response' => $kashierResponse,
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
                    'message' => 'Payment session ID mismatch',
                    'data' => [
                        'expected_session_id' =>
                        $payment->session_id,
                        'received_session_id' =>
                        $returnedSessionId,
                        'kashier_response' =>
                        $kashierResponse,
                    ],
                ], 422);
            }

            /*
        |--------------------------------------------------------------------------
        | Validate merchant reference
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
                    'message' => 'Payment reference mismatch',
                    'data' => [
                        'expected_reference' =>
                        $payment->merchant_reference,
                        'received_reference' =>
                        data_get(
                            $session,
                            'paymentParams.order'
                        ),
                        'kashier_response' =>
                        $kashierResponse,
                    ],
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
                    'data' => [
                        'expected_merchant_id' =>
                        config(
                            'services.kashier.merchant_id'
                        ),
                        'received_merchant_id' =>
                        data_get($session, 'merchantId'),
                        'kashier_response' =>
                        $kashierResponse,
                    ],
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
                    'message' => 'Payment amount mismatch',
                    'data' => [
                        'expected_amount' =>
                        (float) $payment->amount,

                        'received_amount' =>
                        data_get(
                            $session,
                            'paymentParams.amount'
                        ),

                        'kashier_response' =>
                        $kashierResponse,
                    ],
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
                    'message' => 'Payment currency mismatch',
                    'data' => [
                        'expected_currency' =>
                        $payment->currency,

                        'received_currency' =>
                        data_get(
                            $session,
                            'paymentParams.currency'
                        ),

                        'kashier_response' =>
                        $kashierResponse,
                    ],
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

            /*
        |--------------------------------------------------------------------------
        | Provider identifiers
        |--------------------------------------------------------------------------
        */

            $providerOrderId =
                data_get($session, 'orderId');

            $providerOrderId =
                $providerOrderId
                && strtoupper((string) $providerOrderId) !== 'NA'
                ? (string) $providerOrderId
                : $payment->provider_order_id;

            $providerMethod =
                data_get($session, 'method')
                ?? data_get(
                    $session,
                    'paymentParams.defaultMethod'
                )
                ?? $payment->payment_method
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
        | Paid session
        |--------------------------------------------------------------------------
        */

            if ($localStatus === 'paid') {
                DB::transaction(function () use (
                    $payment,
                    $order,
                    $kashierResponse,
                    $rawProviderStatus,
                    $providerOrderId,
                    $providerMethod,
                    $transactionId,
                    $financialService
                ) {
                    $lockedPayment =
                        PaymentTransaction::query()
                        ->lockForUpdate()
                        ->findOrFail($payment->id);

                    $lockedOrder =
                        Order::query()
                        ->lockForUpdate()
                        ->findOrFail($order->id);

                    /*
                 * Already processed.
                 */
                    if (
                        $lockedPayment->status === 'paid'
                        && $lockedOrder->payment_status === 'paid'
                    ) {
                        $lockedPayment->update([
                            'provider_status' =>
                            $rawProviderStatus,

                            'verification_response' =>
                            $kashierResponse,

                            'verified_at' => now(),
                        ]);

                        $financialService
                            ->recordOwnerOrderPayment(
                                $lockedOrder->fresh(),
                                'online',
                                $lockedPayment
                                    ->merchant_reference
                            );

                        $financialService
                            ->recordCashCodeDiscount(
                                $lockedOrder->fresh()
                            );

                        return;
                    }

                    /*
                 * Order paid using another payment transaction.
                 */
                    if (
                        $lockedOrder->payment_status === 'paid'
                        && $lockedPayment->status !== 'paid'
                    ) {
                        $lockedPayment->update([
                            'status' => 'failed',

                            'provider_status' =>
                            $rawProviderStatus,

                            'failure_reason' =>
                            'Order already paid by another transaction',

                            'verification_response' =>
                            $kashierResponse,

                            'verified_at' => now(),

                            'transaction_id' =>
                            $transactionId,

                            'provider_order_id' =>
                            $providerOrderId,
                        ]);

                        return;
                    }

                    $paidAt = now();

                    $lockedPayment->update([
                        'status' => 'paid',

                        'provider_status' =>
                        $rawProviderStatus,

                        'payment_method' =>
                        $providerMethod,

                        'transaction_id' =>
                        $transactionId,

                        'provider_order_id' =>
                        $providerOrderId,

                        'failure_reason' => null,

                        'paid_at' =>
                        $lockedPayment->paid_at
                            ?? $paidAt,

                        'verified_at' => now(),

                        'verification_response' =>
                        $kashierResponse,
                    ]);

                    $lockedOrder->update([
                        'payment_method' => 'online',
                        'payment_status' => 'paid',

                        'paid_at' =>
                        $lockedOrder->paid_at
                            ?? $paidAt,

                        'payment_expires_at' => null,

                        'payment_reference' =>
                        $lockedPayment
                            ->merchant_reference,
                    ]);

                    $freshOrder = $lockedOrder->fresh();

                    $financialService
                        ->recordOwnerOrderPayment(
                            $freshOrder,
                            'online',
                            $lockedPayment
                                ->merchant_reference
                        );

                    $financialService
                        ->recordCashCodeDiscount(
                            $freshOrder
                        );
                });

                $payment->refresh();
                $order->refresh();

                return response()->json([
                    'status' => true,
                    'message' => 'Payment verified successfully',
                    'data' => [
                        'payment_id' => $payment->id,
                        'order_id' => $order->id,
                        'session_id' => $payment->session_id,

                        'provider_status' =>
                        $payment->provider_status,

                        'payment_status' =>
                        $payment->status,

                        'order_payment_status' =>
                        $order->payment_status,

                        'is_paid' => true,

                        'amount' =>
                        round(
                            (float) $payment->amount,
                            2
                        ),

                        'currency' =>
                        $payment->currency,

                        'transaction_id' =>
                        $payment->transaction_id,

                        'provider_order_id' =>
                        $payment->provider_order_id,

                        'paid_at' =>
                        $payment->paid_at?->toISOString(),

                        'verified_at' =>
                        $payment->verified_at?->toISOString(),

                        'kashier_response' =>
                        $kashierResponse,
                    ],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Cancelled session
        |--------------------------------------------------------------------------
        */

            if ($localStatus === 'cancelled') {
                DB::transaction(function () use (
                    $payment,
                    $order,
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
                        'status' => 'cancelled',

                        'provider_status' =>
                        $rawProviderStatus,

                        'failure_reason' =>
                        'Kashier session was cancelled',

                        'verified_at' => now(),

                        'verification_response' =>
                        $kashierResponse,
                    ]);

                    $lockedOrder =
                        Order::query()
                        ->lockForUpdate()
                        ->findOrFail($order->id);

                    if (
                        $lockedOrder->payment_status !== 'paid'
                    ) {
                        $lockedOrder->update([
                            'payment_method' => null,

                            'payment_status' =>
                            'awaiting_payment',
                        ]);
                    }
                });

                $payment->refresh();
                $order->refresh();

                return response()->json([
                    'status' => true,
                    'message' => 'Payment session is cancelled',
                    'data' => [
                        'payment_id' => $payment->id,
                        'order_id' => $order->id,
                        'session_id' => $payment->session_id,

                        'provider_status' =>
                        $payment->provider_status,

                        'payment_status' =>
                        $payment->status,

                        'order_payment_status' =>
                        $order->payment_status,

                        'is_paid' => false,
                        'can_retry' => true,

                        'kashier_response' =>
                        $kashierResponse,
                    ],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Pending, failed or expired session
        |--------------------------------------------------------------------------
        */

            DB::transaction(function () use (
                $payment,
                $order,
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

                /*
             * تعتبر الجلسة منتهية إذا:
             *
             * 1. Kashier أعادت EXPIRED.
             * 2. أو انتهى expires_at المحلي.
             */
                $isExpired =
                    $localStatus === 'expired'
                    || (
                        $lockedPayment->expires_at
                        && now()->greaterThanOrEqualTo(
                            $lockedPayment->expires_at
                        )
                    );

                $newStatus = $isExpired
                    ? 'expired'
                    : (
                        $localStatus === 'failed'
                        ? 'failed'
                        : 'pending'
                    );

                $lockedPayment->update([
                    'status' => $newStatus,

                    'provider_status' =>
                    $rawProviderStatus,

                    'failure_reason' =>
                    match ($newStatus) {
                        'expired' =>
                        'Payment session expired',

                        'failed' =>
                        'Payment failed',

                        default => null,
                    },

                    'verified_at' => now(),

                    'verification_response' =>
                    $kashierResponse,
                ]);

                $lockedOrder =
                    Order::query()
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if (
                    $lockedOrder->payment_status === 'paid'
                ) {
                    return;
                }

                $lockedOrder->update([
                    'payment_method' =>
                    $newStatus === 'pending'
                        ? 'online'
                        : null,

                    'payment_status' =>
                    match ($newStatus) {
                        'expired' => 'expired',
                        'failed' => 'failed',
                        default => 'pending',
                    },
                ]);
            });

            $payment->refresh();
            $order->refresh();

            return response()->json([
                'status' => true,

                'message' =>
                match ($payment->status) {
                    'expired' =>
                    'Payment session has expired',

                    'failed' =>
                    'Payment failed',

                    default =>
                    'Payment is still pending',
                },

                'data' => [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'session_id' => $payment->session_id,

                    'provider_status' =>
                    $payment->provider_status,

                    'payment_status' =>
                    $payment->status,

                    'order_payment_status' =>
                    $order->payment_status,

                    'is_paid' => false,

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
                    $payment->expires_at?->toISOString(),

                    'kashier_response' =>
                    $kashierResponse,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error(
                'Verify Kashier payment session failed',
                [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,

                    'session_id' =>
                    $payment->session_id,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,
                'message' => 'Unable to verify payment',
                'error' => $exception->getMessage(),
            ], 502);
        }
    }

    public function selectCash(
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

        try {
            $updatedOrder = DB::transaction(function () use (
                $order,
                $user
            ) {
                $lockedOrder = Order::query()
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if (
                    (int) $lockedOrder->user_id !==
                    (int) $user->id
                ) {
                    throw new RuntimeException(
                        'Unauthorized'
                    );
                }

                if (
                    $lockedOrder->status !==
                    'accepted_by_kitchen'
                ) {
                    throw new RuntimeException(
                        'Order must be accepted by kitchen before selecting payment method'
                    );
                }

                if (
                    $lockedOrder->payment_status === 'paid'
                ) {
                    throw new RuntimeException(
                        'Order is already paid'
                    );
                }

                if (
                    $lockedOrder->payment_expires_at
                    && now()->greaterThanOrEqualTo(
                        $lockedOrder->payment_expires_at
                    )
                ) {
                    $lockedOrder->update([
                        'payment_status' => 'expired',
                    ]);

                    throw new RuntimeException(
                        'Payment time has expired'
                    );
                }

                $remainingAmount = round(
                    max(
                        (float) $lockedOrder->total,
                        0
                    ),
                    2
                );

                if ($remainingAmount <= 0) {
                    $lockedOrder->update([
                        'payment_method' => 'cash_code',
                        'payment_status' => 'paid',
                        'paid_at' =>
                        $lockedOrder->paid_at ?? now(),
                        'payment_expires_at' => null,
                        'status' => 'preparing',
                    ]);

                    return $lockedOrder->fresh();
                }

                $pendingOnlinePayment =
                    PaymentTransaction::query()
                    ->where(
                        'order_id',
                        $lockedOrder->id
                    )
                    ->where('provider', 'kashier')
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first();

                if ($pendingOnlinePayment) {
                    throw new RuntimeException(
                        'There is an active online payment session for this order'
                    );
                }

                if (
                    $lockedOrder->payment_method === 'cash'
                    && $lockedOrder->payment_status ===
                    'cash_pending'
                ) {
                    if (
                        $lockedOrder->status !== 'preparing'
                    ) {
                        $lockedOrder->update([
                            'status' => 'preparing',
                            'payment_expires_at' => null,
                        ]);
                    }

                    return $lockedOrder->fresh();
                }

                $lockedOrder->update([
                    'payment_method' => 'cash',
                    'payment_status' => 'cash_pending',
                    'payment_expires_at' => null,
                    'status' => 'preparing',
                    'paid_at' => null,
                ]);

                return $lockedOrder->fresh();
            });

            return response()->json([
                'status' => true,
                'message' =>
                $updatedOrder->payment_status === 'paid'
                    ? 'Order is fully paid using cash code'
                    : 'Cash payment selected successfully',
                'data' => [
                    'order_id' => $updatedOrder->id,
                    'order_number' => $updatedOrder->number,
                    'order_status' => $updatedOrder->status,
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
                    round(
                        max(
                            (float) $updatedOrder->total,
                            0
                        ),
                        2
                    ),
                    'currency' => 'EGP',
                    'payment_method' =>
                    $updatedOrder->payment_method,
                    'payment_status' =>
                    $updatedOrder->payment_status,
                    'is_paid' =>
                    $updatedOrder->payment_status ===
                        'paid',
                    'is_cash_pending' =>
                    $updatedOrder->payment_status ===
                        'cash_pending',
                    'paid_at' =>
                    $updatedOrder->paid_at
                        ? $updatedOrder
                        ->paid_at
                        ->toISOString()
                        : null,
                    'payment_expires_at' =>
                    $updatedOrder->payment_expires_at
                        ? $updatedOrder
                        ->payment_expires_at
                        ->toISOString()
                        : null,
                ],
            ]);
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();

            $statusCode = match ($message) {
                'Unauthorized' => 403,
                default => 422,
            };

            return response()->json([
                'status' => false,
                'message' => $message,
            ], $statusCode);
        } catch (Throwable $exception) {
            Log::error(
                'Select cash payment failed',
                [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'message' =>
                    $exception->getMessage(),
                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,
                'message' =>
                'Unable to select cash payment',
                'error' =>
                $exception->getMessage(),
            ], 500);
        }
    }

    private function generateMerchantReference(
        Order $order
    ): string {
        do {
            $reference =
                'ORDER-'
                . $order->id
                . '-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(6));
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





    public function kashierMethods(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ((int) $order->user_id !== (int) $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->refresh();

        if ($order->status !== 'preparing') {
            return response()->json([
                'status' => false,
                'message' => 'Order must be accepted by kitchen before payment',
                'order_status' => $order->status,
            ], 422);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Order is already paid',
                'payment_status' => $order->payment_status,
            ], 422);
        }

        if (
            $order->payment_expires_at
            && now()->greaterThanOrEqualTo(
                $order->payment_expires_at
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Payment time has expired',
                'payment_status' => 'expired',
            ], 422);
        }

        $remainingAmount = round(
            max((float) $order->total, 0),
            2
        );

        if ($remainingAmount <= 0) {
            return response()->json([
                'status' => true,
                'message' => 'No online payment is required',
                'data' => [
                    'order_id' => $order->id,
                    'remaining_amount' => 0,
                    'currency' => 'EGP',
                    'methods' => [],
                ],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kashier payment methods retrieved successfully',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->number,
                'remaining_amount' => $remainingAmount,
                'currency' => 'EGP',

                /*
             * دي الطرق المرسلة إلى Payment Session.
             */
                'allowed_methods' => [
                    'card',
                    'wallet',
                ],

                'methods' => [
                    [
                        'code' => 'card',
                        'name_ar' => 'بطاقة بنكية',
                        'name_en' => 'Card',                                                  
                        'enabled' => true,
                    ],
                    [
                        'code' => 'wallet',
                        'name_ar' => 'محفظة إلكترونية',
                        'name_en' => 'Mobile Wallet',
                        'enabled' => true,
                    ],
                    [
                        'code' => 'apple_pay',
                        'name_ar' => 'Apple Pay',
                        'name_en' => 'Apple Pay',

                        /*
                     * التوفر الحقيقي يحدده Kashier SDK حسب:
                     * iOS + الجهاز + إعداد Apple Merchant ID.
                     */
                        'enabled' => true,
                        'sdk_managed' => true,
                        'ios_only' => true,
                    ],
                ],

                /*
             * Flutter لا يرسل الطريقة للباك إند.
             * ينشئ Session ثم يترك SDK يعرض الطرق المتاحة.
             */
                'selection_handled_by_sdk' => true,
            ],
        ]);
    }
}
