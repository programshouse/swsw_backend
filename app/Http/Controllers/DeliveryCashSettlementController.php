<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCashSettlement;
use App\Models\DeliveryOrder;
use App\Models\PaymentTransaction;
use App\services\Payments\KashierService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class DeliveryCashSettlementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Cash settlement summary
    |--------------------------------------------------------------------------
    |
    | ترجع إجمالي أوردرات الكاش التي استلم الدليفري قيمتها
    | ولم يقم بتسويتها مع الأونر حتى الآن.
    |
    */

    public function summary(
        Request $request
    ): JsonResponse {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $orders = $this->eligibleCashOrders(
            (int) $delivery->id
        )->get();

        $amount = round(
            $orders->sum(
                function (
                    DeliveryOrder $deliveryOrder
                ) {
                    return (float) (
                        $deliveryOrder->order?->total
                        ?? 0
                    );
                }
            ),
            2
        );

        return response()->json([
            'status' => true,

            'message' =>
                'Delivery cash summary retrieved successfully',

            'data' => [
                'orders_count' =>
                    $orders->count(),

                'amount' =>
                    $amount,

                'currency' =>
                    'EGP',

                'can_pay' =>
                    $amount > 0,

                'order_ids' =>
                    $orders
                        ->pluck('order_id')
                        ->values(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Kashier payment session
    |--------------------------------------------------------------------------
    |
    | لا توجد موافقة أدمن.
    |
    | بمجرد استدعاء الـ API:
    |
    | 1. يتم حساب أوردرات الكاش غير المسواة.
    | 2. يتم إنشاء DeliveryCashSettlement بحالة processing.
    | 3. يتم ربط الأوردرات بالتسوية.
    | 4. يتم إنشاء Kashier Payment Session.
    |
    */

    public function createSession(
        Request $request,
        KashierService $kashierService
    ): JsonResponse {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

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
            trim(
                (string) (
                    $validated['allowed_methods']
                    ?? 'CARD'
                )
            )
        );

        $kashierAllowedMethod = match (
            $requestedMethod
        ) {
            'WALLET' =>
                'wallet',

            'APPLEPAY' =>
                'applepay',

            default =>
                'card',
        };

        /*
        |--------------------------------------------------------------------------
        | Return active session if exists
        |--------------------------------------------------------------------------
        |
        | منع إنشاء أكثر من Session نشطة لنفس الدليفري.
        |
        */

        $existingSettlement =
            DeliveryCashSettlement::query()
                ->where(
                    'delivery_user_id',
                    $delivery->id
                )
                ->where(
                    'status',
                    'processing'
                )
                ->whereNotNull(
                    'session_id'
                )
                ->where(function ($query) {
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

        if ($existingSettlement) {
            return response()->json([
                'status' => true,

                'message' =>
                    'An active cash settlement session already exists',

                'data' =>
                    $this->settlementResponse(
                        $existingSettlement
                    ),
            ], 200);
        }

        $expiresAt = now()->addMinutes(15);

        $settlement = null;
        $payment = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | Create local settlement
            |--------------------------------------------------------------------------
            */

            $result = DB::transaction(
                function () use (
                    $delivery,
                    $expiresAt
                ) {
                    /*
                     * نقفل الأوردرات أثناء إنشاء التسوية
                     * لمنع إدخال نفس الأوردر في أكثر من تسوية.
                     */
                    $deliveryOrders =
                        $this->eligibleCashOrders(
                            (int) $delivery->id
                        )
                            ->lockForUpdate()
                            ->get();

                    if ($deliveryOrders->isEmpty()) {
                        throw new RuntimeException(
                            'No unsettled cash orders were found'
                        );
                    }

                    $amount = round(
                        $deliveryOrders->sum(
                            function (
                                DeliveryOrder $deliveryOrder
                            ) {
                                return (float) (
                                    $deliveryOrder
                                        ->order
                                        ?->total
                                    ?? 0
                                );
                            }
                        ),
                        2
                    );

                    if ($amount <= 0) {
                        throw new RuntimeException(
                            'Cash settlement amount must be greater than zero'
                        );
                    }

                    $merchantReference =
                        $this
                            ->generateMerchantReference(
                                (int) $delivery->id
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Create settlement
                    |--------------------------------------------------------------------------
                    |
                    | تبدأ مباشرة processing لأن مفيش موافقة أدمن.
                    |
                    */

                    $settlement =
                        DeliveryCashSettlement::create([
                            'delivery_user_id' =>
                                $delivery->id,

                            'amount' =>
                                $amount,

                            'status' =>
                                'processing',

                            'merchant_reference' =>
                                $merchantReference,

                            'provider' =>
                                'kashier',

                            'provider_status' =>
                                'CREATING',

                            'expires_at' =>
                                $expiresAt,

                            'paid_at' =>
                                null,

                            'failed_at' =>
                                null,

                            'failure_reason' =>
                                null,
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Attach orders to settlement
                    |--------------------------------------------------------------------------
                    */

                    DeliveryOrder::query()
                        ->whereIn(
                            'id',
                            $deliveryOrders
                                ->pluck('id')
                        )
                        ->update([
                            'cash_settlement_id' =>
                                $settlement->id,
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
                                null,

                            'delivery_cash_settlement_id' =>
                                $settlement->id,

                            /*
                             * الدليفري موجود في delivery_users،
                             * وليس users.
                             */
                            'user_id' =>
                                null,

                            'provider' =>
                                'kashier',

                            'merchant_reference' =>
                                $merchantReference,

                            'payment_request_id' =>
                                null,

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

                    return [
                        'settlement' =>
                            $settlement,

                        'payment' =>
                            $payment,
                    ];
                }
            );

            /** @var DeliveryCashSettlement $settlement */
            $settlement =
                $result['settlement'];

            /** @var PaymentTransaction $payment */
            $payment =
                $result['payment'];

            /*
            |--------------------------------------------------------------------------
            | Kashier URLs
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

            $serverWebhook = trim(
                (string) config(
                    'services.kashier.webhook_url'
                )
            );

            if ($serverWebhook === '') {
                $serverWebhook =
                    route('kashier.webhook');
            }

            /*
            |--------------------------------------------------------------------------
            | Create session payload
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
                        (float) $settlement->amount,
                        2,
                        '.',
                        ''
                    ),

                'currency' =>
                    'EGP',

                'order' =>
                    $settlement
                        ->merchant_reference,

                'merchantRedirect' =>
                    $merchantRedirect,

                'display' =>
                    'ar',

                'type' =>
                    'one-time',

                'allowedMethods' =>
                    $kashierAllowedMethod,

                'defaultMethod' =>
                    $kashierAllowedMethod,

                'redirectMethod' =>
                    null,

                'failureRedirect' =>
                    false,

                'description' =>
                    'Delivery cash settlement #'
                    . $settlement->id,

                'manualCapture' =>
                    false,

                'customer' => [
                    'email' =>
                        $delivery->email
                        ?: 'delivery+'
                        . $delivery->id
                        . '@programshouse.com',

                    'reference' =>
                        'delivery-'
                        . $delivery->id,
                ],

                'saveCard' =>
                    'disabled',

                'retrieveSavedCard' =>
                    false,

                'interactionSource' =>
                    'ECOMMERCE',

                'enable3DS' =>
                    true,

                'serverWebhook' =>
                    $serverWebhook,

                'metaData' => [
                    'payment_type' =>
                        'delivery_cash_settlement',

                    'delivery_cash_settlement_id' =>
                        $settlement->id,

                    'delivery_user_id' =>
                        $delivery->id,

                    'payment_id' =>
                        $payment->id,
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | Create Kashier session
            |--------------------------------------------------------------------------
            */

            $kashierResponse =
                $kashierService
                    ->createPaymentSession(
                        $sessionPayload
                    );

            $sessionId =
                $kashierService
                    ->extractSessionId(
                        $kashierResponse
                    );

            if (!$sessionId) {
                throw new RuntimeException(
                    'Kashier did not return session ID'
                );
            }

            $sessionUrl =
                $kashierService
                    ->extractSessionUrl(
                        $kashierResponse
                    );

            $providerStatus =
                $kashierService
                    ->extractSessionStatus(
                        $kashierResponse
                    );

            /*
            |--------------------------------------------------------------------------
            | Save session data
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $settlement,
                $payment,
                $sessionId,
                $sessionUrl,
                $providerStatus,
                $kashierResponse
            ) {
                $lockedSettlement =
                    DeliveryCashSettlement::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $settlement->id
                        );

                $lockedPayment =
                    PaymentTransaction::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $payment->id
                        );

                $lockedPayment->update([
                    'session_id' =>
                        $sessionId,

                    'session_url' =>
                        $sessionUrl,

                    'provider_status' =>
                        $providerStatus,

                    'create_response' =>
                        $kashierResponse,
                ]);

                $lockedSettlement->update([
                    'status' =>
                        'processing',

                    'session_id' =>
                        $sessionId,

                    'session_url' =>
                        $sessionUrl,

                    'provider_status' =>
                        $providerStatus,

                    'provider_response' =>
                        $kashierResponse,

                    'failure_reason' =>
                        null,
                ]);
            });

            $settlement->refresh();

            return response()->json([
                'status' => true,

                'message' =>
                    'Delivery cash settlement session created successfully',

                'data' =>
                    $this->settlementResponse(
                        $settlement
                    ),
            ], 201);
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Handle session creation failure
            |--------------------------------------------------------------------------
            */

            if ($settlement) {
                DB::transaction(function () use (
                    $settlement,
                    $payment,
                    $exception
                ) {
                    $lockedSettlement =
                        DeliveryCashSettlement::query()
                            ->lockForUpdate()
                            ->find(
                                $settlement->id
                            );

                    if ($lockedSettlement) {
                        $lockedSettlement->update([
                            'status' =>
                                'failed',

                            'provider_status' =>
                                'CREATE_FAILED',

                            'failed_at' =>
                                now(),

                            'paid_at' =>
                                null,

                            'failure_reason' =>
                                $exception->getMessage(),
                        ]);
                    }

                    if ($payment) {
                        $lockedPayment =
                            PaymentTransaction::query()
                                ->lockForUpdate()
                                ->find(
                                    $payment->id
                                );

                        if ($lockedPayment) {
                            $lockedPayment->update([
                                'status' =>
                                    'failed',

                                'provider_status' =>
                                    'CREATE_FAILED',

                                'failure_reason' =>
                                    $exception->getMessage(),

                                'verified_at' =>
                                    now(),
                            ]);
                        }
                    }

                    /*
                     * نفك ارتباط الأوردرات حتى يستطيع الدليفري
                     * إنشاء محاولة دفع جديدة.
                     */
                    DeliveryOrder::query()
                        ->where(
                            'cash_settlement_id',
                            $settlement->id
                        )
                        ->where(
                            'cash_settled',
                            0
                        )
                        ->update([
                            'cash_settlement_id' =>
                                null,
                        ]);
                });
            }

            Log::error(
                'Create delivery cash settlement failed',
                [
                    'delivery_user_id' =>
                        $delivery->id,

                    'settlement_id' =>
                        $settlement?->id,

                    'payment_id' =>
                        $payment?->id,

                    'message' =>
                        $exception->getMessage(),

                    'file' =>
                        $exception->getFile(),

                    'line' =>
                        $exception->getLine(),

                    'exception_class' =>
                        get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,

                'message' =>
                    'Unable to create delivery cash settlement session',

                'error' =>
                    $exception->getMessage(),
            ], 502);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Settlement status
    |--------------------------------------------------------------------------
    */

    public function status(
        Request $request,
        DeliveryCashSettlement $settlement
    ): JsonResponse {
        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (
            (int) $settlement->delivery_user_id
            !== (int) $delivery->id
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $settlement->refresh();

        /*
        |--------------------------------------------------------------------------
        | Handle locally expired session
        |--------------------------------------------------------------------------
        |
        | الـ Webhook أو Verify يظل هو المصدر الأساسي،
        | لكن لو انتهى الوقت محليًا وما زالت processing
        | نرجع أنها منتهية في الـ response.
        |
        */

        $isLocallyExpired =
            $settlement->status === 'processing'
            && $settlement->expires_at
            && now()->greaterThan(
                $settlement->expires_at
            );

        return response()->json([
            'status' => true,

            'message' =>
                'Cash settlement status retrieved successfully',

            'data' =>
                array_merge(
                    $this->settlementResponse(
                        $settlement
                    ),
                    [
                        'is_locally_expired' =>
                            $isLocallyExpired,
                    ]
                ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Eligible cash orders
    |--------------------------------------------------------------------------
    */

    private function eligibleCashOrders(
        int $deliveryId
    ): Builder {
        return DeliveryOrder::query()
            ->with([
                'order',
            ])
            ->where(
                'delivery_user_id',
                $deliveryId
            )
            ->where(
                'status',
                'delivered'
            )
            ->where(
                'cash_settled',
                0
            )
            ->whereNull(
                'cash_settlement_id'
            )
            ->whereHas(
                'order',
                function ($query) {
                    $query
                        ->where(
                            'status',
                            'delivered'
                        )
                        ->where(
                            'payment_method',
                            'cash'
                        )
                        ->where(
                            'payment_status',
                            'paid'
                        );
                }
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate merchant reference
    |--------------------------------------------------------------------------
    */

    private function generateMerchantReference(
        int $deliveryId
    ): string {
        return sprintf(
            'DELIVERY-CASH-%d-%s-%s',
            $deliveryId,
            now()->format('YmdHis'),
            strtoupper(
                str()->random(6)
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Settlement response
    |--------------------------------------------------------------------------
    */

    private function settlementResponse(
        DeliveryCashSettlement $settlement
    ): array {
        return [
            'settlement_id' =>
                $settlement->id,

            'amount' =>
                round(
                    (float) $settlement->amount,
                    2
                ),

            'currency' =>
                'EGP',

            'status' =>
                $settlement->status,

            'provider_status' =>
                $settlement->provider_status,

            'reference' =>
                $settlement
                    ->merchant_reference,

            'session_id' =>
                $settlement->session_id,

            'session_url' =>
                $settlement->session_url,

            'transaction_id' =>
                $settlement->transaction_id,

            'expires_at' =>
                $settlement->expires_at
                    ?->toISOString(),

            'paid_at' =>
                $settlement->paid_at
                    ?->toISOString(),

            'failed_at' =>
                $settlement->failed_at
                    ?->toISOString(),

            'failure_reason' =>
                $settlement->failure_reason,

            'is_paid' =>
                $settlement->status ===
                'paid',

            'is_processing' =>
                $settlement->status ===
                'processing',

            'is_failed' =>
                $settlement->status ===
                'failed',

            'is_expired' =>
                $settlement->status ===
                'expired',

            'is_cancelled' =>
                $settlement->status ===
                'cancelled',
        ];
    }



    
}