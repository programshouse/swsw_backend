<?php

namespace App\services\Payments;

use App\Models\DeliveryCashSettlement;
use App\Models\DeliveryOrder;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliveryCashSettlementService
{
    /*
    |--------------------------------------------------------------------------
    | Mark settlement as paid
    |--------------------------------------------------------------------------
    |
    | يتم استدعاؤها بعد تأكيد نجاح الدفع من Kashier.
    |
    | النتيجة:
    | - settlement.status = paid
    | - payment.status = paid
    | - delivery_orders.cash_settled = 1
    |
    */

    public function markAsPaid(
        DeliveryCashSettlement $settlement,
        PaymentTransaction $payment,
        array $providerPayload = []
    ): DeliveryCashSettlement {
        return DB::transaction(function () use (
            $settlement,
            $payment,
            $providerPayload
        ) {
            $lockedSettlement =
                DeliveryCashSettlement::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $settlement->id
                    );

            /*
             * Idempotency:
             * منع تنفيذ التسوية أكثر من مرة.
             */
            if (
                $lockedSettlement->status ===
                'paid'
            ) {
                return $lockedSettlement->fresh([
                    'deliveryOrders',
                    'paymentTransactions',
                ]);
            }

            /*
             * لا نحول التسوية إلى paid إذا كانت أُلغيت
             * أو انتهت صلاحيتها.
             */
            if (
                in_array(
                    $lockedSettlement->status,
                    [
                        'cancelled',
                        'expired',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Cash settlement cannot be paid in its current status'
                );
            }

            $lockedPayment =
                PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

            /*
            |--------------------------------------------------------------------------
            | Validate payment belongs to settlement
            |--------------------------------------------------------------------------
            */

            if (
                (int) $lockedPayment
                    ->delivery_cash_settlement_id
                !== (int) $lockedSettlement->id
            ) {
                throw new RuntimeException(
                    'Payment does not belong to this cash settlement'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate amount
            |--------------------------------------------------------------------------
            */

            $paymentAmount = round(
                (float) $lockedPayment->amount,
                2
            );

            $settlementAmount = round(
                (float) $lockedSettlement->amount,
                2
            );

            if (
                abs(
                    $paymentAmount
                    - $settlementAmount
                ) > 0.01
            ) {
                throw new RuntimeException(
                    'Cash settlement payment amount mismatch'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Lock settlement orders
            |--------------------------------------------------------------------------
            */

            $deliveryOrders =
                DeliveryOrder::query()
                    ->where(
                        'cash_settlement_id',
                        $lockedSettlement->id
                    )
                    ->where(
                        'delivery_user_id',
                        $lockedSettlement
                            ->delivery_user_id
                    )
                    ->lockForUpdate()
                    ->get();

            if ($deliveryOrders->isEmpty()) {
                throw new RuntimeException(
                    'Cash settlement orders were not found'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate orders are not already assigned elsewhere
            |--------------------------------------------------------------------------
            */

            $invalidOrders =
                $deliveryOrders->filter(
                    function (
                        DeliveryOrder $deliveryOrder
                    ) use ($lockedSettlement) {
                        return
                            (int) $deliveryOrder
                                ->cash_settlement_id
                            !== (int) $lockedSettlement->id;
                    }
                );

            if ($invalidOrders->isNotEmpty()) {
                throw new RuntimeException(
                    'One or more cash orders belong to another settlement'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Mark orders as settled
            |--------------------------------------------------------------------------
            |
            | الدليفري دفع الكاش للأونر بنجاح.
            |
            */

            DeliveryOrder::query()
                ->where(
                    'cash_settlement_id',
                    $lockedSettlement->id
                )
                ->where(
                    'delivery_user_id',
                    $lockedSettlement
                        ->delivery_user_id
                )
                ->update([
                    'cash_settled' => 1,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Resolve provider data
            |--------------------------------------------------------------------------
            */

            $providerStatus =
                data_get(
                    $providerPayload,
                    'status'
                )
                ?? data_get(
                    $providerPayload,
                    'paymentStatus'
                )
                ?? data_get(
                    $providerPayload,
                    'data.status'
                )
                ?? 'SUCCESS';

            $transactionId =
                data_get(
                    $providerPayload,
                    'transactionId'
                )
                ?? data_get(
                    $providerPayload,
                    'transaction_id'
                )
                ?? data_get(
                    $providerPayload,
                    'data.transactionId'
                )
                ?? data_get(
                    $providerPayload,
                    'data.transaction_id'
                )
                ?? $lockedPayment
                    ->transaction_id
                ?? $lockedSettlement
                    ->transaction_id;

            /*
            |--------------------------------------------------------------------------
            | Update payment transaction
            |--------------------------------------------------------------------------
            */

            $lockedPayment->update([
                'status' =>
                    'paid',

                'provider_status' =>
                    (string) $providerStatus,

                'transaction_id' =>
                    $transactionId,

                'paid_at' =>
                    $lockedPayment->paid_at
                    ?? now(),

                'verified_at' =>
                    now(),

                'failure_reason' =>
                    null,

                'verification_response' =>
                    $providerPayload,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update cash settlement
            |--------------------------------------------------------------------------
            */

            $lockedSettlement->update([
                'status' =>
                    'paid',

                'provider_status' =>
                    (string) $providerStatus,

                'transaction_id' =>
                    $transactionId,

                'paid_at' =>
                    $lockedSettlement->paid_at
                    ?? now(),

                'failed_at' =>
                    null,

                'failure_reason' =>
                    null,

                'verification_response' =>
                    $providerPayload,
            ]);

            return $lockedSettlement->fresh([
                'deliveryOrders',
                'paymentTransactions',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mark settlement as failed
    |--------------------------------------------------------------------------
    |
    | يتم استدعاؤها عند فشل الدفع النهائي.
    |
    | الأوردرات تظل:
    | cash_settled = 0
    |
    | ويتم فك cash_settlement_id حتى يستطيع الدليفري
    | إنشاء Session جديدة.
    |
    */

    public function markAsFailed(
        DeliveryCashSettlement $settlement,
        PaymentTransaction $payment,
        array $providerPayload = [],
        ?string $reason = null
    ): DeliveryCashSettlement {
        return DB::transaction(function () use (
            $settlement,
            $payment,
            $providerPayload,
            $reason
        ) {
            $lockedSettlement =
                DeliveryCashSettlement::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $settlement->id
                    );

            /*
             * لا يمكن تحويل تسوية ناجحة إلى فاشلة.
             */
            if (
                $lockedSettlement->status ===
                'paid'
            ) {
                throw new RuntimeException(
                    'Paid cash settlement cannot be marked as failed'
                );
            }

            /*
             * لو اتسجلت failed قبل كده لا نكرر التحديث.
             */
            if (
                $lockedSettlement->status ===
                'failed'
            ) {
                return $lockedSettlement->fresh([
                    'deliveryOrders',
                    'paymentTransactions',
                ]);
            }

            $lockedPayment =
                PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

            if (
                (int) $lockedPayment
                    ->delivery_cash_settlement_id
                !== (int) $lockedSettlement->id
            ) {
                throw new RuntimeException(
                    'Payment does not belong to this cash settlement'
                );
            }

            $providerStatus =
                data_get(
                    $providerPayload,
                    'status'
                )
                ?? data_get(
                    $providerPayload,
                    'paymentStatus'
                )
                ?? data_get(
                    $providerPayload,
                    'data.status'
                )
                ?? 'FAILED';

            $failureReason =
                $reason
                ?? data_get(
                    $providerPayload,
                    'failureReason'
                )
                ?? data_get(
                    $providerPayload,
                    'failure_reason'
                )
                ?? data_get(
                    $providerPayload,
                    'message'
                )
                ?? data_get(
                    $providerPayload,
                    'data.message'
                )
                ?? 'Kashier payment failed';

            /*
            |--------------------------------------------------------------------------
            | Update payment
            |--------------------------------------------------------------------------
            */

            $lockedPayment->update([
                'status' =>
                    'failed',

                'provider_status' =>
                    (string) $providerStatus,

                'failure_reason' =>
                    is_array($failureReason)
                        ? json_encode(
                            $failureReason,
                            JSON_UNESCAPED_UNICODE
                        )
                        : (string) $failureReason,

                'verification_response' =>
                    $providerPayload,

                'verified_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update settlement
            |--------------------------------------------------------------------------
            */

            $lockedSettlement->update([
                'status' =>
                    'failed',

                'provider_status' =>
                    (string) $providerStatus,

                'failed_at' =>
                    now(),

                'paid_at' =>
                    null,

                'failure_reason' =>
                    is_array($failureReason)
                        ? json_encode(
                            $failureReason,
                            JSON_UNESCAPED_UNICODE
                        )
                        : (string) $failureReason,

                'verification_response' =>
                    $providerPayload,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Release orders for new attempt
            |--------------------------------------------------------------------------
            |
            | الدفع فشل؛ إذن نزيل ربط التسوية الحالية
            | حتى تظهر الأوردرات مرة ثانية في summary.
            |
            */

            DeliveryOrder::query()
                ->where(
                    'cash_settlement_id',
                    $lockedSettlement->id
                )
                ->where(
                    'cash_settled',
                    0
                )
                ->update([
                    'cash_settlement_id' =>
                        null,
                ]);

            return $lockedSettlement->fresh([
                'deliveryOrders',
                'paymentTransactions',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mark settlement as expired
    |--------------------------------------------------------------------------
    |
    | عند انتهاء صلاحية Session بدون دفع.
    |
    */

    public function markAsExpired(
        DeliveryCashSettlement $settlement,
        PaymentTransaction $payment,
        array $providerPayload = []
    ): DeliveryCashSettlement {
        return DB::transaction(function () use (
            $settlement,
            $payment,
            $providerPayload
        ) {
            $lockedSettlement =
                DeliveryCashSettlement::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $settlement->id
                    );

            if (
                $lockedSettlement->status ===
                'paid'
            ) {
                throw new RuntimeException(
                    'Paid cash settlement cannot be marked as expired'
                );
            }

            if (
                $lockedSettlement->status ===
                'expired'
            ) {
                return $lockedSettlement->fresh([
                    'deliveryOrders',
                    'paymentTransactions',
                ]);
            }

            $lockedPayment =
                PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

            if (
                (int) $lockedPayment
                    ->delivery_cash_settlement_id
                !== (int) $lockedSettlement->id
            ) {
                throw new RuntimeException(
                    'Payment does not belong to this cash settlement'
                );
            }

            $providerStatus =
                data_get(
                    $providerPayload,
                    'status'
                )
                ?? data_get(
                    $providerPayload,
                    'paymentStatus'
                )
                ?? data_get(
                    $providerPayload,
                    'data.status'
                )
                ?? 'EXPIRED';

            $lockedPayment->update([
                'status' =>
                    'expired',

                'provider_status' =>
                    (string) $providerStatus,

                'failure_reason' =>
                    'Payment session expired',

                'verified_at' =>
                    now(),

                'verification_response' =>
                    $providerPayload,
            ]);

            $lockedSettlement->update([
                'status' =>
                    'expired',

                'provider_status' =>
                    (string) $providerStatus,

                'failed_at' =>
                    now(),

                'paid_at' =>
                    null,

                'failure_reason' =>
                    'Payment session expired',

                'verification_response' =>
                    $providerPayload,
            ]);

            /*
             * فك الأوردرات لإعادة المحاولة.
             */
            DeliveryOrder::query()
                ->where(
                    'cash_settlement_id',
                    $lockedSettlement->id
                )
                ->where(
                    'cash_settled',
                    0
                )
                ->update([
                    'cash_settlement_id' =>
                        null,
                ]);

            return $lockedSettlement->fresh([
                'deliveryOrders',
                'paymentTransactions',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mark settlement as cancelled
    |--------------------------------------------------------------------------
    */

    public function markAsCancelled(
        DeliveryCashSettlement $settlement,
        PaymentTransaction $payment,
        array $providerPayload = []
    ): DeliveryCashSettlement {
        return DB::transaction(function () use (
            $settlement,
            $payment,
            $providerPayload
        ) {
            $lockedSettlement =
                DeliveryCashSettlement::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $settlement->id
                    );

            if (
                $lockedSettlement->status ===
                'paid'
            ) {
                throw new RuntimeException(
                    'Paid cash settlement cannot be cancelled'
                );
            }

            if (
                $lockedSettlement->status ===
                'cancelled'
            ) {
                return $lockedSettlement->fresh([
                    'deliveryOrders',
                    'paymentTransactions',
                ]);
            }

            $lockedPayment =
                PaymentTransaction::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

            $providerStatus =
                data_get(
                    $providerPayload,
                    'status'
                )
                ?? data_get(
                    $providerPayload,
                    'paymentStatus'
                )
                ?? 'CANCELLED';

            $lockedPayment->update([
                'status' =>
                    'cancelled',

                'provider_status' =>
                    (string) $providerStatus,

                'failure_reason' =>
                    'Payment was cancelled',

                'verified_at' =>
                    now(),

                'verification_response' =>
                    $providerPayload,
            ]);

            $lockedSettlement->update([
                'status' =>
                    'cancelled',

                'provider_status' =>
                    (string) $providerStatus,

                'failed_at' =>
                    now(),

                'paid_at' =>
                    null,

                'failure_reason' =>
                    'Payment was cancelled',

                'verification_response' =>
                    $providerPayload,
            ]);

            DeliveryOrder::query()
                ->where(
                    'cash_settlement_id',
                    $lockedSettlement->id
                )
                ->where(
                    'cash_settled',
                    0
                )
                ->update([
                    'cash_settlement_id' =>
                        null,
                ]);

            return $lockedSettlement->fresh([
                'deliveryOrders',
                'paymentTransactions',
            ]);
        });
    }
}