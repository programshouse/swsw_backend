<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\services\FinancialTransactionService;
use App\Models\DeliveryCashSettlement;
use App\services\Payments\DeliveryCashSettlementService;

class KashierWebhookController extends Controller
{
    public function handle(
        Request $request,
        FinancialTransactionService $financialService,
        DeliveryCashSettlementService $cashSettlementService
    ): JsonResponse {
        $payload = $request->all();

        /*
    |--------------------------------------------------------------------------
    | Log received webhook
    |--------------------------------------------------------------------------
    */

        Log::info('Kashier webhook received', [
            'payload' => $payload,
            'ip' => $request->ip(),
        ]);

        /*
    |--------------------------------------------------------------------------
    | Verify webhook signature
    |--------------------------------------------------------------------------
    */

        if (!$this->verifySignature($request)) {
            Log::warning('Invalid Kashier webhook signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Invalid webhook signature',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /*
    |--------------------------------------------------------------------------
    | Normalize webhook data
    |--------------------------------------------------------------------------
    */

        $normalized = $this->normalizePayload($payload);

        Log::info('Kashier webhook normalized', [
            'normalized' => $normalized,
        ]);

        /*
    |--------------------------------------------------------------------------
    | Validate required identifiers
    |--------------------------------------------------------------------------
    */

        if (
            !$normalized['merchant_reference']
            && !$normalized['payment_request_id']
            && !$normalized['transaction_id']
            && !$normalized['card_order_id']
            && !$normalized['provider_order_id']
        ) {
            Log::warning(
                'Kashier webhook missing payment identifiers',
                [
                    'payload' => $payload,
                ]
            );

            return response()->json([
                'status' => false,
                'message' => 'Payment identifier is missing',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /*
    |--------------------------------------------------------------------------
    | Find payment transaction
    |--------------------------------------------------------------------------
    */

        $payment = $this->findPaymentTransaction(
            $normalized
        );

        if (!$payment) {
            Log::warning(
                'Kashier payment transaction not found',
                [
                    'merchant_reference' =>
                    $normalized['merchant_reference'],

                    'payment_request_id' =>
                    $normalized['payment_request_id'],

                    'transaction_id' =>
                    $normalized['transaction_id'],

                    'card_order_id' =>
                    $normalized['card_order_id'],

                    'provider_order_id' =>
                    $normalized['provider_order_id'],
                ]
            );

            /*
         * نرجع 200 حتى لا تستمر Kashier
         * في إعادة إرسال Webhook غير قابل للمطابقة.
         */
            return response()->json([
                'status' => true,
                'message' => 'Webhook received, payment not found',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Validate merchant
    |--------------------------------------------------------------------------
    */

        $configuredMerchantId = trim(
            (string) config(
                'services.kashier.merchant_id'
            )
        );

        if (
            $normalized['merchant_id']
            && $configuredMerchantId !== ''
            && $normalized['merchant_id'] !== $configuredMerchantId
        ) {
            Log::warning('Kashier merchant mismatch', [
                'payment_id' => $payment->id,

                'received_merchant_id' =>
                $normalized['merchant_id'],

                'expected_merchant_id' =>
                $configuredMerchantId,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Merchant mismatch',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /*
    |--------------------------------------------------------------------------
    | Validate amount
    |--------------------------------------------------------------------------
    */

        if ($normalized['amount'] !== null) {
            $expectedAmount = round(
                (float) $payment->amount,
                2
            );

            $receivedAmount = round(
                (float) $normalized['amount'],
                2
            );

            if (
                abs(
                    $expectedAmount - $receivedAmount
                ) >= 0.01
            ) {
                Log::warning(
                    'Kashier webhook amount mismatch',
                    [
                        'payment_id' =>
                        $payment->id,

                        'expected_amount' =>
                        $expectedAmount,

                        'received_amount' =>
                        $receivedAmount,
                    ]
                );

                return response()->json([
                    'status' => false,
                    'message' => 'Payment amount mismatch',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Validate currency
    |--------------------------------------------------------------------------
    */

        if (
            $normalized['currency']
            && strtoupper($normalized['currency']) !==
            strtoupper((string) $payment->currency)
        ) {
            Log::warning(
                'Kashier webhook currency mismatch',
                [
                    'payment_id' =>
                    $payment->id,

                    'expected_currency' =>
                    $payment->currency,

                    'received_currency' =>
                    $normalized['currency'],
                ]
            );

            return response()->json([
                'status' => false,
                'message' => 'Payment currency mismatch',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /*
    |--------------------------------------------------------------------------
    | Raw provider status
    |--------------------------------------------------------------------------
    */

        $rawProviderStatus = strtoupper(
            trim(
                (string) data_get(
                    $payload,
                    'data.status',
                    ''
                )
            )
        );

        try {
            DB::transaction(function () use (
                $payment,
                $normalized,
                $payload,
                $rawProviderStatus,
                $financialService,
                $cashSettlementService
            ) {
                $lockedPayment =
                    PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                /*
|--------------------------------------------------------------------------
| Delivery cash settlement
|--------------------------------------------------------------------------
*/

                if ($lockedPayment->delivery_cash_settlement_id) {

                    $settlement =
                        DeliveryCashSettlement::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $lockedPayment->delivery_cash_settlement_id
                        );

                    if ($normalized['status'] === 'paid') {

                        $cashSettlementService->markAsPaid(
                            $settlement,
                            $lockedPayment,
                            $payload
                        );

                        return;
                    }

                    if ($normalized['status'] === 'failed') {

                        $cashSettlementService->markAsFailed(
                            $settlement,
                            $lockedPayment,
                            $payload
                        );

                        return;
                    }

                    if ($normalized['status'] === 'cancelled') {

                        $cashSettlementService->markAsCancelled(
                            $settlement,
                            $lockedPayment,
                            $payload
                        );

                        return;
                    }

                    if ($normalized['status'] === 'expired') {

                        $cashSettlementService->markAsExpired(
                            $settlement,
                            $lockedPayment,
                            $payload
                        );

                        return;
                    }

                    $lockedPayment->update([
                        'provider_status' =>
                        $rawProviderStatus,
                        'callback_payload' =>
                        $payload,
                    ]);

                    $settlement->update([
                        'provider_status' =>
                        $rawProviderStatus,
                        'callback_payload' =>
                        $payload,
                    ]);

                    return;
                }

                /*
|--------------------------------------------------------------------------
| Order payment
|--------------------------------------------------------------------------
*/

                $order = Order::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedPayment->order_id
                    );

                /*
            |--------------------------------------------------------------------------
            | Idempotency
            |--------------------------------------------------------------------------
            */

                if (
                    $lockedPayment->status === 'paid'
                    && $normalized['status'] === 'paid'
                ) {
                    $lockedPayment->update([
                        'provider_status' =>
                        $rawProviderStatus
                            ?: $lockedPayment
                            ->provider_status,

                        'callback_payload' =>
                        $payload,

                        'transaction_id' =>
                        $normalized['transaction_id']
                            ?? $lockedPayment
                            ->transaction_id,

                        'card_order_id' =>
                        $normalized['card_order_id']
                            ?? $lockedPayment
                            ->card_order_id,

                        'provider_order_id' =>
                        $normalized['provider_order_id']
                            ?? $lockedPayment
                            ->provider_order_id,

                        'payment_request_id' =>
                        $normalized['payment_request_id']
                            ?? $lockedPayment
                            ->payment_request_id,

                        'verified_at' => now(),
                    ]);

                    /*
                 * يضمن تسجيل القيود المالية
                 * حتى لو تم استقبال نفس Webhook مرة أخرى.
                 */
                    $freshOrder = $order->fresh();

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

                    return;
                }

                /*
            |--------------------------------------------------------------------------
            | Successful payment
            |--------------------------------------------------------------------------
            */

                if ($normalized['status'] === 'paid') {
                    /*
                 * الأوردر مدفوع بعملية أخرى.
                 */
                    if (
                        $order->payment_status === 'paid'
                        && $lockedPayment->status !== 'paid'
                    ) {
                        Log::warning(
                            'Order already paid by another transaction',
                            [
                                'order_id' =>
                                $order->id,

                                'payment_id' =>
                                $lockedPayment->id,
                            ]
                        );

                        $lockedPayment->update([
                            'status' =>
                            'failed',

                            'provider_status' =>
                            $rawProviderStatus
                                ?: $lockedPayment
                                ->provider_status,

                            'failure_reason' =>
                            'Order already paid by another transaction',

                            'callback_payload' =>
                            $payload,

                            'transaction_id' =>
                            $normalized['transaction_id']
                                ?? $lockedPayment
                                ->transaction_id,

                            'card_order_id' =>
                            $normalized['card_order_id']
                                ?? $lockedPayment
                                ->card_order_id,

                            'provider_order_id' =>
                            $normalized['provider_order_id']
                                ?? $lockedPayment
                                ->provider_order_id,

                            'payment_request_id' =>
                            $normalized['payment_request_id']
                                ?? $lockedPayment
                                ->payment_request_id,

                            'verified_at' =>
                            now(),
                        ]);

                        return;
                    }

                    $paidAt =
                        $normalized['paid_at']
                        ?? now();

                    /*
                |--------------------------------------------------------------------------
                | Update payment transaction
                |--------------------------------------------------------------------------
                */

                    $lockedPayment->update([
                        'status' =>
                        'paid',

                        /*
                     * لا نخزن card أو wallet في العمود
                     * إذا كان العمود المحلي يقبل online فقط.
                     */
                        'payment_method' =>
                        'online',

                        'provider_status' =>
                        $rawProviderStatus
                            ?: 'SUCCESS',

                        'transaction_id' =>
                        $normalized['transaction_id']
                            ?? $lockedPayment
                            ->transaction_id,

                        'card_order_id' =>
                        $normalized['card_order_id']
                            ?? $lockedPayment
                            ->card_order_id,

                        'provider_order_id' =>
                        $normalized['provider_order_id']
                            ?? $lockedPayment
                            ->provider_order_id,

                        'payment_request_id' =>
                        $normalized['payment_request_id']
                            ?? $lockedPayment
                            ->payment_request_id,

                        'paid_at' =>
                        $lockedPayment->paid_at
                            ?? $paidAt,

                        'verified_at' =>
                        now(),

                        'failure_reason' =>
                        null,

                        'callback_payload' =>
                        $payload,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Update order payment
                |--------------------------------------------------------------------------
                */

                    $order->update([
                        'payment_method' =>
                        'online',

                        'payment_status' =>
                        'paid',

                        'paid_at' =>
                        $order->paid_at
                            ?? $paidAt,

                        'payment_expires_at' =>
                        null,

                        'payment_reference' =>
                        $lockedPayment
                            ->merchant_reference,
                    ]);

                    /*
                |--------------------------------------------------------------------------
                | Financial transactions
                |--------------------------------------------------------------------------
                */

                    $freshOrder = $order->fresh();

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

                    return;
                }

                /*
            |--------------------------------------------------------------------------
            | Failed payment
            |--------------------------------------------------------------------------
            */

                if ($normalized['status'] === 'failed') {
                    if ($lockedPayment->status === 'paid') {
                        return;
                    }

                    $lockedPayment->update([
                        'status' =>
                        'failed',

                        'provider_status' =>
                        $rawProviderStatus
                            ?: 'FAILED',

                        'transaction_id' =>
                        $normalized['transaction_id']
                            ?? $lockedPayment
                            ->transaction_id,

                        'card_order_id' =>
                        $normalized['card_order_id']
                            ?? $lockedPayment
                            ->card_order_id,

                        'provider_order_id' =>
                        $normalized['provider_order_id']
                            ?? $lockedPayment
                            ->provider_order_id,

                        'payment_request_id' =>
                        $normalized['payment_request_id']
                            ?? $lockedPayment
                            ->payment_request_id,

                        'failure_reason' =>
                        $normalized['failure_reason']
                            ?? 'Payment failed',

                        'verified_at' =>
                        now(),

                        'callback_payload' =>
                        $payload,
                    ]);

                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'payment_method' =>
                            null,

                            'payment_status' =>
                            'awaiting_payment',
                        ]);
                    }

                    return;
                }

                /*
            |--------------------------------------------------------------------------
            | Cancelled payment
            |--------------------------------------------------------------------------
            */

                if ($normalized['status'] === 'cancelled') {
                    if ($lockedPayment->status === 'paid') {
                        return;
                    }

                    $lockedPayment->update([
                        'status' =>
                        'cancelled',

                        'provider_status' =>
                        $rawProviderStatus
                            ?: 'CANCELLED',

                        'transaction_id' =>
                        $normalized['transaction_id']
                            ?? $lockedPayment
                            ->transaction_id,

                        'card_order_id' =>
                        $normalized['card_order_id']
                            ?? $lockedPayment
                            ->card_order_id,

                        'provider_order_id' =>
                        $normalized['provider_order_id']
                            ?? $lockedPayment
                            ->provider_order_id,

                        'payment_request_id' =>
                        $normalized['payment_request_id']
                            ?? $lockedPayment
                            ->payment_request_id,

                        'failure_reason' =>
                        $normalized['failure_reason']
                            ?? 'Payment cancelled',

                        'verified_at' =>
                        now(),

                        'callback_payload' =>
                        $payload,
                    ]);

                    if ($order->payment_status !== 'paid') {
                        $order->update([
                            'payment_method' =>
                            null,

                            'payment_status' =>
                            'awaiting_payment',
                        ]);
                    }

                    return;
                }

                /*
            |--------------------------------------------------------------------------
            | Pending or unknown status
            |--------------------------------------------------------------------------
            */

                if ($lockedPayment->status === 'paid') {
                    return;
                }

                $lockedPayment->update([
                    'provider_status' =>
                    $rawProviderStatus
                        ?: $lockedPayment
                        ->provider_status,

                    'callback_payload' =>
                    $payload,

                    'transaction_id' =>
                    $normalized['transaction_id']
                        ?? $lockedPayment
                        ->transaction_id,

                    'card_order_id' =>
                    $normalized['card_order_id']
                        ?? $lockedPayment
                        ->card_order_id,

                    'provider_order_id' =>
                    $normalized['provider_order_id']
                        ?? $lockedPayment
                        ->provider_order_id,

                    'payment_request_id' =>
                    $normalized['payment_request_id']
                        ?? $lockedPayment
                        ->payment_request_id,

                    'verified_at' =>
                    now(),
                ]);
            });

            $payment->refresh();

            Log::info(
                'Kashier webhook processed successfully',
                [
                    'payment_id' =>
                    $payment->id,

                    'order_id' =>
                    $payment->order_id,

                    'payment_status' =>
                    $payment->status,

                    'provider_status' =>
                    $payment->provider_status,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Webhook processed successfully',
                'data' => [
                    'payment_id' =>
                    $payment->id,

                    'payment_status' =>
                    $payment->status,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error(
                'Kashier webhook processing failed',
                [
                    'payment_id' =>
                    $payment->id,

                    'order_id' =>
                    $payment->order_id,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),

                    'file' =>
                    $exception->getFile(),

                    'line' =>
                    $exception->getLine(),

                    'payload' =>
                    $payload,
                ]
            );

            /*
         * أظهري error مؤقتًا أثناء الاختبار فقط.
         * احذفيه قبل اعتماد نسخة Production النهائية.
         */
            return response()->json([
                'status' => false,
                'message' => 'Webhook processing failed',
                'error' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Find payment transaction
    |--------------------------------------------------------------------------
    */

    private function findPaymentTransaction(
        array $normalized
    ): ?PaymentTransaction {
        return PaymentTransaction::query()
            ->where(function ($query) use ($normalized) {
                if ($normalized['merchant_reference']) {
                    $query->orWhere(
                        'merchant_reference',
                        $normalized['merchant_reference']
                    );
                }

                if ($normalized['payment_request_id']) {
                    $query->orWhere(
                        'payment_request_id',
                        $normalized['payment_request_id']
                    );
                }

                if ($normalized['transaction_id']) {
                    $query->orWhere(
                        'transaction_id',
                        $normalized['transaction_id']
                    );
                }

                if ($normalized['card_order_id']) {
                    $query->orWhere(
                        'card_order_id',
                        $normalized['card_order_id']
                    );
                }

                if ($normalized['provider_order_id']) {
                    $query->orWhere(
                        'provider_order_id',
                        $normalized['provider_order_id']
                    );
                }
            })
            ->latest('id')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize payload
    |--------------------------------------------------------------------------
    */

    private function normalizePayload(
        array $payload
    ): array {
        $rawStatus = $this->firstValue(
            $payload,
            [
                'status',
                'paymentStatus',
                'payment_status',
                'transactionStatus',
                'transaction_status',

                'data.status',
                'data.paymentStatus',
                'data.payment_status',

                'response.status',
                'response.paymentStatus',

                'event.data.status',
                'event.data.paymentStatus',
            ]
        );

        return [
            'status' =>
            $this->normalizeStatus(
                $rawStatus
            ),

            'merchant_reference' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'merchantOrderId',
                        'merchant_order_id',

                        'invoiceReferenceId',
                        'invoice_reference_id',

                        'orderReference',
                        'order_reference',

                        'reference',

                        'data.merchantOrderId',
                        'data.invoiceReferenceId',
                        'data.orderReference',
                        'data.reference',

                        'response.invoiceReferenceId',
                        'response.orderReference',

                        'event.data.merchantOrderId',
                        'event.data.invoiceReferenceId',
                        'event.data.orderReference',
                    ]
                )
            ),

            'payment_request_id' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'paymentRequestId',
                        'payment_request_id',

                        'data.paymentRequestId',
                        'data.payment_request_id',

                        'response.paymentRequestId',

                        'event.data.paymentRequestId',
                    ]
                )
            ),

            'transaction_id' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'transactionId',
                        'transaction_id',
                        'transactionInfoId',
                        'transaction_info_id',

                        'data.transactionId',
                        'data.transactionInfoId',

                        'response.transactionId',
                        'response.transactionInfoId',

                        'event.data.transactionId',
                        'event.data.transactionInfoId',
                    ]
                )
            ),

            'card_order_id' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'cardOrderId',
                        'card_order_id',

                        'data.cardOrderId',
                        'data.card_order_id',

                        'response.cardOrderId',

                        'event.data.cardOrderId',
                    ]
                )
            ),

            'provider_order_id' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'orderId',
                        'order_id',
                        'kashierOrderId',
                        'kashier_order_id',

                        'data.orderId',
                        'data.kashierOrderId',

                        'response.orderId',
                        'response.kashierOrderId',

                        'event.data.orderId',
                        'event.data.kashierOrderId',
                    ]
                )
            ),

            'merchant_id' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'merchantId',
                        'merchant_id',

                        'data.merchantId',
                        'data.merchant_id',

                        'response.merchantId',

                        'event.data.merchantId',
                    ]
                )
            ),

            'amount' =>
            $this->floatValue(
                $this->firstValue(
                    $payload,
                    [
                        'amount',
                        'totalAmount',
                        'total_amount',

                        'data.amount',
                        'data.totalAmount',
                        'data.total_amount',

                        'response.amount',
                        'response.totalAmount',

                        'event.data.amount',
                        'event.data.totalAmount',
                    ]
                )
            ),

            'currency' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'currency',

                        'data.currency',

                        'response.currency',

                        'event.data.currency',
                    ]
                )
            ),

            'payment_method' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'method',
                        'paymentMethod',
                        'payment_method',

                        'data.method',
                        'data.paymentMethod',

                        'response.method',
                        'response.paymentMethod',

                        'event.data.method',
                        'event.data.paymentMethod',
                    ]
                )
            ),

            'failure_reason' =>
            $this->stringValue(
                $this->firstValue(
                    $payload,
                    [
                        'message',
                        'failureReason',
                        'failure_reason',
                        'error',

                        'data.message',
                        'data.failureReason',
                        'data.failure_reason',

                        'response.message',

                        'event.data.message',
                        'event.data.failureReason',
                    ]
                )
            ),

            'paid_at' =>
            $this->dateValue(
                $this->firstValue(
                    $payload,
                    [
                        'paidAt',
                        'paid_at',
                        'creationDate',

                        'data.paidAt',
                        'data.paid_at',
                        'data.creationDate',

                        'response.paidAt',
                        'response.creationDate',

                        'event.data.paidAt',
                        'event.data.creationDate',
                    ]
                )
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize status
    |--------------------------------------------------------------------------
    */

    private function normalizeStatus(mixed $status): string
    {
        $status = strtoupper(
            trim((string) $status)
        );

        return match ($status) {
            'SUCCESS',
            'PAID',
            'COMPLETED',
            'CAPTURED',
            'APPROVED' => 'paid',

            'FAILED',
            'FAIL',
            'DECLINED',
            'ERROR',
            'REJECTED' => 'failed',

            'CANCELLED',
            'CANCELED',
            'VOIDED' => 'cancelled',

            'PENDING',
            'PROCESSING',
            'UNPAID',
            'INITIATED' => 'pending',

            default => 'pending',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Verify signature
    |--------------------------------------------------------------------------
    */

    private function verifySignature(Request $request): bool
    {
        /*
    |--------------------------------------------------------------------------
    | Signature received from Kashier
    |--------------------------------------------------------------------------
    */

        $receivedSignature = trim(
            (string) $request->header('x-kashier-signature')
        );

        if ($receivedSignature === '') {
            Log::warning(
                'Kashier webhook signature header is missing'
            );

            return false;
        }

        /*
    |--------------------------------------------------------------------------
    | Webhook transaction data
    |--------------------------------------------------------------------------
    */

        $data = $request->input('data');

        if (!is_array($data)) {
            Log::warning(
                'Kashier webhook data is missing or invalid'
            );

            return false;
        }

        /*
    |--------------------------------------------------------------------------
    | Signature keys
    |--------------------------------------------------------------------------
    */

        $signatureKeys = $data['signatureKeys'] ?? null;

        if (
            !is_array($signatureKeys)
            || empty($signatureKeys)
        ) {
            Log::warning(
                'Kashier webhook signatureKeys are missing'
            );

            return false;
        }

        /*
    |--------------------------------------------------------------------------
    | Sort signature keys alphabetically
    |--------------------------------------------------------------------------
    */

        sort($signatureKeys, SORT_STRING);

        $signatureParts = [];

        foreach ($signatureKeys as $key) {
            if (!array_key_exists($key, $data)) {
                Log::warning(
                    'Kashier signature key is missing from data',
                    [
                        'missing_key' => $key,
                    ]
                );

                return false;
            }

            $value = $data[$key];

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = '';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                );
            } else {
                $value = (string) $value;
            }

            // Encode the value only.
            $signatureParts[] =
                $key . '=' . rawurlencode($value);
        }

        /*
    |--------------------------------------------------------------------------
    | Build signature payload
    |--------------------------------------------------------------------------
    */

        $signaturePayload = implode(
            '&',
            $signatureParts
        );

        /*
    |--------------------------------------------------------------------------
    | Payment API Key
    |--------------------------------------------------------------------------
    */

        $paymentApiKey = trim(
            (string) config('services.kashier.api_key')
        );

        if ($paymentApiKey === '') {
            Log::error(
                'Kashier Payment API Key is missing'
            );

            return false;
        }

        /*
    |--------------------------------------------------------------------------
    | Calculate expected signature
    |--------------------------------------------------------------------------
    */

        $calculatedSignature = hash_hmac(
            'sha256',
            $signaturePayload,
            $paymentApiKey
        );

        $matches = hash_equals(
            strtolower($calculatedSignature),
            strtolower($receivedSignature)
        );

        Log::info('Kashier webhook signature checked', [
            'signature_matches' => $matches,
        ]);

        return $matches;
    }
    /*
            |--------------------------------------------------------------------------
            | Helpers
            |--------------------------------------------------------------------------
            */

    private function firstValue(array $payload, array $paths): mixed
    {
        foreach ($paths as $path) {
            $value = data_get(
                $payload,
                $path
            );

            if (
                $value !== null
                && $value !== ''
            ) {
                return $value;
            }
        }

        return null;
    }

    private function stringValue(
        mixed $value
    ): ?string {
        if (
            $value === null
            || is_array($value)
            || is_object($value)
        ) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    private function floatValue(
        mixed $value
    ): ?float {
        if (
            $value === null
            || $value === ''
            || !is_numeric($value)
        ) {
            return null;
        }

        return round(
            (float) $value,
            2
        );
    }

    private function dateValue(
        mixed $value
    ): mixed {
        if (!$value) {
            return null;
        }

        try {
            return now()->parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
