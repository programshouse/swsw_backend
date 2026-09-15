<?php

namespace App\services;

use App\Models\FinancialTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Models\KitchenPackageSubscription;
use App\Models\PaymentTransaction;
use App\Models\Wallet;
use App\Models\KitchenProfile;
use App\Models\WalletTransaction;


class FinancialTransactionService
{
    /*
    |--------------------------------------------------------------------------
    | Register owner order payment
    |--------------------------------------------------------------------------
    |
    | نسجل المبلغ الفعلي الذي تم تحصيله من العميل.
    |
    | لا نسجل discount_value كدخل نقدي؛ لأنه جزء تم دفعه بالكاش كود.
    |
    */

    public function recordOwnerOrderPayment(
        Order $order,
        string $paymentChannel,
        ?string $reference = null
    ): ?FinancialTransaction {
        $paymentChannel = $this->validatePaymentChannel(
            $paymentChannel
        );

        /*
         * total هو المبلغ المتبقي بعد خصم الكاش كود.
         *
         * لو الكاش كود غطى كامل الطلب:
         * total = 0
         * وبالتالي لا يوجد تحصيل نقدي للأونر.
         */
        $collectedAmount = round(
            max((float) $order->total, 0),
            2
        );

        if ($collectedAmount <= 0) {
            return null;
        }

        return $this->credit(
            walletType: 'owner',
            walletId: null,
            amount: $collectedAmount,
            transactionType: 'order_payment',
            paymentChannel: $paymentChannel,
            order: $order,
            idempotencyKey: 'order-payment-owner-'
                . $order->id
                . '-'
                . $paymentChannel,
            reference: $reference,
            description: 'Customer payment for order '
                . $order->number,
            meta: [
                'total_before_discount' =>
                round(
                    (float) $order->total_before_discount,
                    2
                ),

                'discount_value' =>
                round(
                    (float) $order->discount_value,
                    2
                ),

                'collected_amount' =>
                $collectedAmount,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Record cash-code discount
    |--------------------------------------------------------------------------
    |
    | الكاش كود ليس مبلغًا نقديًا جديدًا دخل للأونر.
    | نسجله كتكلفة/خصم على الأونر لأغراض التقارير.
    |
    */

    public function recordCashCodeDiscount(
        Order $order
    ): ?FinancialTransaction {
        $discountAmount = round(
            max((float) $order->discount_value, 0),
            2
        );

        if ($discountAmount <= 0) {
            return null;
        }

        return $this->debit(
            walletType: 'owner',
            walletId: null,
            amount: $discountAmount,
            transactionType: 'cash_code_discount',
            paymentChannel: 'cash_code',
            order: $order,
            idempotencyKey: 'order-cash-code-discount-'
                . $order->id,
            reference: $order->number,
            description: 'Cash code discount for order '
                . $order->number,
            meta: [
                'cash_code_id' =>
                $order->cash_code_id,

                'discount_value' =>
                $discountAmount,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Register kitchen income
    |--------------------------------------------------------------------------
    |
    | نسجل مستحق المطبخ بعد اكتمال وتسليم الأوردر.
    |
    */

    public function recordKitchenIncome(
        Order $order
    ): ?FinancialTransaction {
        $amount = round(
            max(
                (float) $order->kitchen_net_amount,
                0
            ),
            2
        );

        if ($amount <= 0) {
            return null;
        }

        /*
    |--------------------------------------------------------------------------
    | Get kitchen profile
    |--------------------------------------------------------------------------
    |
    | orders.kitchen_id يشير إلى kitchen_profiles.id
    |
    */

        $kitchenProfile = KitchenProfile::query()
            ->with('user')
            ->find($order->kitchen_id);

        if (!$kitchenProfile) {
            throw new RuntimeException(
                'Kitchen profile not found for order '
                    . $order->id
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Get kitchen user
    |--------------------------------------------------------------------------
    |
    | wallets.owner_id يشير إلى users.id وليس kitchen_profiles.id
    |
    */

        $kitchenUser = $kitchenProfile->user;

        if (!$kitchenUser) {
            throw new RuntimeException(
                'Kitchen user not found for order '
                    . $order->id
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Get kitchen wallet
    |--------------------------------------------------------------------------
    */

        $wallet = Wallet::query()
            ->where(
                'owner_id',
                $kitchenUser->id
            )
            ->where(
                'owner_type',
                'kitchen'
            )
            ->lockForUpdate()
            ->first();

        if (!$wallet) {
            throw new RuntimeException(
                'Kitchen wallet not found for user '
                    . $kitchenUser->id
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Credit kitchen wallet
    |--------------------------------------------------------------------------
    */

        return $this->credit(
            walletType: 'kitchen',

            /*
         * هنا نرسل wallets.id
         * وليس kitchen_profiles.id.
         */
            walletId: (int) $wallet->id,

            amount: $amount,

            transactionType: 'kitchen_income',

            paymentChannel: $this->resolveOrderPaymentChannel(
                $order
            ),

            order: $order,

            idempotencyKey: 'order-kitchen-income-'
                . $order->id,

            reference: $order->number
                ?? $order->order_number
                ?? 'ORDER-' . $order->id,

            description: 'Kitchen income from order '
                . (
                    $order->number
                    ?? $order->order_number
                    ?? $order->id
                ),

            meta: [
                'subtotal' => round(
                    (float) $order->subtotal,
                    2
                ),

                'kitchen_service_fee' => round(
                    (float) $order
                        ->kitchen_service_fee,
                    2
                ),

                'kitchen_net_amount' =>
                $amount,

                'kitchen_profile_id' =>
                $kitchenProfile->id,

                'kitchen_user_id' =>
                $kitchenUser->id,

                'wallet_id' =>
                $wallet->id,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Register delivery income
    |--------------------------------------------------------------------------
    |
    | يجب تمرير الدليفري الذي سلّم الأوردر.
    |
    | نفترض حاليًا أن مستحق الدليفري هو delivery_price.
    | إذا كان عندك حساب مختلف لأرباح الدليفري نبدل amount فقط.
    |
    */

   public function recordDeliveryIncome(
    Order $order,
    int $deliveryId
): ?FinancialTransaction {
    $amount = round(
        max(
            (float) $order->delivery_price,
            0
        ),
        2
    );

    if ($amount <= 0) {
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Get delivery wallet
    |--------------------------------------------------------------------------
    |
    | $deliveryId هو delivery_users.id
    | لكن credit() و createTransaction() يحتاجان wallets.id
    |
    */

    $wallet = Wallet::query()
        ->where('owner_id', $deliveryId)
        ->where('owner_type', 'delivery')
        ->lockForUpdate()
        ->first();

    if (!$wallet) {
        throw new RuntimeException(
            'Delivery wallet not found for delivery user: '
            . $deliveryId
        );
    }

    return $this->credit(
        walletType: 'delivery',

        /*
         * نرسل رقم المحفظة نفسها.
         */
        walletId: (int) $wallet->id,

        amount: $amount,

        transactionType: 'delivery_income',

        paymentChannel:
            $this->resolveOrderPaymentChannel(
                $order
            ),

        order: $order,

        idempotencyKey:
            'order-delivery-income-'
            . $order->id
            . '-'
            . $deliveryId,

        reference:
            $order->number
            ?? $order->order_number
            ?? 'ORDER-' . $order->id,

        description:
            'Delivery income from order '
            . (
                $order->number
                ?? $order->order_number
                ?? $order->id
            ),

        meta: [
            'delivery_price' =>
                $amount,

            'distance_km' =>
                round(
                    (float) $order->distance_km,
                    2
                ),

            'delivery_user_id' =>
                $deliveryId,

            'wallet_id' =>
                $wallet->id,
        ]
    );
}
        /*
        |--------------------------------------------------------------------------
        | Settle delivered order
        |--------------------------------------------------------------------------
        |
        | تستخدم عند تسليم الأوردر:
        |
        | 1. لو Cash نسجل التحصيل للأونر.
        | 2. نسجل خصم الكاش كود إن وجد.
        | 3. نسجل مستحق المطبخ.
        | 4. نسجل مستحق الدليفري.
        |
        */

    public function settleDeliveredOrder(Order $order, int $deliveryId): void
    {
        DB::transaction(function () use (
            $order,
            $deliveryId
        ) {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            /*
             * الدفع Online يُسجل وقت نجاح Kashier.
             * الدفع Cash يُسجل عند التسليم.
             */
            if ($lockedOrder->payment_method === 'cash') {
                $this->recordOwnerOrderPayment(
                    $lockedOrder,
                    'cash',
                    $lockedOrder->number
                );
            }

            $this->recordCashCodeDiscount(
                $lockedOrder
            );

            $this->recordKitchenIncome(
                $lockedOrder
            );

            $this->recordDeliveryIncome(
                $lockedOrder,
                $deliveryId
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Credit wallet
    |--------------------------------------------------------------------------
    */

    public function credit(
        string $walletType,
        ?int $walletId,
        float $amount,
        string $transactionType,
        ?string $paymentChannel,
        ?Order $order,
        string $idempotencyKey,
        ?string $reference = null,
        ?string $description = null,
        array $meta = []
    ): FinancialTransaction {
        return $this->createTransaction(
            walletType: $walletType,
            walletId: $walletId,
            credit: $amount,
            debit: 0,
            transactionType: $transactionType,
            paymentChannel: $paymentChannel,
            order: $order,
            idempotencyKey: $idempotencyKey,
            reference: $reference,
            description: $description,
            meta: $meta
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Debit wallet
    |--------------------------------------------------------------------------
    */

    public function debit(
        string $walletType,
        ?int $walletId,
        float $amount,
        string $transactionType,
        ?string $paymentChannel,
        ?Order $order,
        string $idempotencyKey,
        ?string $reference = null,
        ?string $description = null,
        array $meta = []
    ): FinancialTransaction {
        return $this->createTransaction(
            walletType: $walletType,
            walletId: $walletId,
            credit: 0,
            debit: $amount,
            transactionType: $transactionType,
            paymentChannel: $paymentChannel,
            order: $order,
            idempotencyKey: $idempotencyKey,
            reference: $reference,
            description: $description,
            meta: $meta
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Current wallet balance
    |--------------------------------------------------------------------------
    */

    public function getBalance(
        string $walletType,
        ?int $walletId = null
    ): float {
        $walletType = $this->validateWalletType(
            $walletType
        );

        $query = FinancialTransaction::query()
            ->where('wallet_type', $walletType);

        if ($walletId === null) {
            $query->whereNull('wallet_id');
        } else {
            $query->where('wallet_id', $walletId);
        }

        return round(
            (float) (
                (clone $query)->sum('credit')
                - (clone $query)->sum('debit')
            ),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create transaction safely
    |--------------------------------------------------------------------------
    */

    private function createTransaction(
        string $walletType,
        ?int $walletId,
        float $credit,
        float $debit,
        string $transactionType,
        ?string $paymentChannel,
        ?Order $order,
        string $idempotencyKey,
        ?string $reference,
        ?string $description,
        array $meta
    ): FinancialTransaction {
        $walletType = $this->validateWalletType(
            $walletType
        );

        if ($paymentChannel !== null) {
            $paymentChannel =
                $this->validatePaymentChannel(
                    $paymentChannel
                );
        }

        $credit = round(
            max($credit, 0),
            2
        );

        $debit = round(
            max($debit, 0),
            2
        );

        if (
            ($credit <= 0 && $debit <= 0)
            || ($credit > 0 && $debit > 0)
        ) {
            throw new RuntimeException(
                'Financial transaction must contain either credit or debit.'
            );
        }

        return DB::transaction(function () use (
            $walletType,
            $walletId,
            $credit,
            $debit,
            $transactionType,
            $paymentChannel,
            $order,
            $idempotencyKey,
            $reference,
            $description,
            $meta
        ) {
            /*
        |--------------------------------------------------------------------------
        | Idempotency
        |--------------------------------------------------------------------------
        */

            $existing =
                FinancialTransaction::query()
                ->where(
                    'idempotency_key',
                    $idempotencyKey
                )
                ->first();

            if ($existing) {
                return $existing;
            }

            /*
        |--------------------------------------------------------------------------
        | Wallet
        |--------------------------------------------------------------------------
        |
        | walletId هنا هو wallets.id.
        |
        */

            $wallet = null;
            $balanceBefore = 0;

            if ($walletId !== null) {
                $wallet = Wallet::query()
                    ->lockForUpdate()
                    ->find($walletId);

                if (!$wallet) {
                    throw new RuntimeException(
                        'Wallet not found: '
                            . $walletId
                    );
                }

                if (
                    (string) $wallet->owner_type !==
                    (string) $walletType
                ) {
                    throw new RuntimeException(
                        'Wallet type mismatch. Expected '
                            . $walletType
                            . ', found '
                            . $wallet->owner_type
                    );
                }

                $balanceBefore = round(
                    (float) $wallet->available_amount,
                    2
                );
            } else {
                /*
             * للمحافظ العامة مثل owner بدون Wallet فعلية،
             * نستمر في الاعتماد على آخر قيد مالي.
             */

                $lastTransaction =
                    FinancialTransaction::query()
                    ->where(
                        'wallet_type',
                        $walletType
                    )
                    ->whereNull('wallet_id')
                    ->lockForUpdate()
                    ->latest('id')
                    ->first();

                $balanceBefore =
                    $lastTransaction
                    ? round(
                        (float) $lastTransaction
                            ->balance_after,
                        2
                    )
                    : 0;
            }

            /*
        |--------------------------------------------------------------------------
        | Calculate balance
        |--------------------------------------------------------------------------
        */

            $balanceAfter = round(
                $balanceBefore
                    + $credit
                    - $debit,
                2
            );

            if (
                $wallet
                && $balanceAfter < 0
            ) {
                throw new RuntimeException(
                    'Insufficient wallet balance'
                );
            }

            /*
        |--------------------------------------------------------------------------
        | Check idempotency after lock
        |--------------------------------------------------------------------------
        */

            $existing =
                FinancialTransaction::query()
                ->where(
                    'idempotency_key',
                    $idempotencyKey
                )
                ->first();

            if ($existing) {
                return $existing;
            }

            /*
        |--------------------------------------------------------------------------
        | Create financial transaction
        |--------------------------------------------------------------------------
        */

            $transaction =
                FinancialTransaction::create([
                    'order_id' =>
                    $order?->id,

                    'wallet_type' =>
                    $walletType,

                    'wallet_id' =>
                    $walletId,

                    'transaction_type' =>
                    $transactionType,

                    'payment_channel' =>
                    $paymentChannel,

                    'credit' =>
                    $credit,

                    'debit' =>
                    $debit,

                    'balance_after' =>
                    $balanceAfter,

                    'idempotency_key' =>
                    $idempotencyKey,

                    'reference' =>
                    $reference,

                    'description' =>
                    $description,

                    'meta' =>
                    $meta,
                ]);

            /*
        |--------------------------------------------------------------------------
        | Update real wallet balance
        |--------------------------------------------------------------------------
        */

            if ($wallet) {
                $wallet->update([
                    'available_amount' =>
                    $balanceAfter,
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Create wallet transaction
        |--------------------------------------------------------------------------
        */

            if ($wallet) {
                WalletTransaction::firstOrCreate(
                    [
                        'idempotency_key' =>
                        $idempotencyKey,
                    ],
                    [
                        'wallet_id' =>
                        $wallet->id,

                        'order_id' =>
                        $order?->id,

                        /*
                     * kitchen موجود في users.
                     * delivery موجود في delivery_users.
                     */
                        'user_id' =>
                        $wallet->owner_type === 'kitchen'
                            ? $wallet->owner_id
                            : null,

                        'actor_id' =>
                        null,

                        'actor_type' =>
                        'system',

                        'type' =>
                        $credit > 0
                            ? 'credit'
                            : 'debit',

                        'status' =>
                        'completed',

                        'amount' =>
                        $credit > 0
                            ? $credit
                            : $debit,

                        'reference' =>
                        $reference,

                        'balance_before' =>
                        $balanceBefore,

                        'balance_after' =>
                        $balanceAfter,

                        'description' =>
                        $description,

                        'meta' =>
                        $meta,
                    ]
                );
            }

            return $transaction;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve order payment channel
    |--------------------------------------------------------------------------
    */

    private function resolveOrderPaymentChannel(
        Order $order
    ): string {
        return match ($order->payment_method) {
            'online' => 'online',
            'cash' => 'cash',
            'cash_code' => 'cash_code',
            default => 'manual',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Validation helpers
    |--------------------------------------------------------------------------
    */

    private function validateWalletType(
        string $walletType
    ): string {
        if (
            !in_array(
                $walletType,
                [
                    'owner',
                    'kitchen',
                    'delivery',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Invalid wallet type.'
            );
        }

        return $walletType;
    }

    private function validatePaymentChannel(
        string $paymentChannel
    ): string {
        if (
            !in_array(
                $paymentChannel,
                [
                    'cash',
                    'online',
                    'cash_code',
                    'manual',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Invalid payment channel.'
            );
        }

        return $paymentChannel;
    }


    public function recordKitchenSubscriptionPayment(
        KitchenPackageSubscription $subscription,
        PaymentTransaction $payment
    ): void {
        $idempotencyKey =
            'kitchen-subscription-payment-' . $payment->id;

        /*
    |--------------------------------------------------------------------------
    | Prevent duplicate recording
    |--------------------------------------------------------------------------
    */

        $exists = FinancialTransaction::query()
            ->where('idempotency_key', $idempotencyKey)
            ->exists();

        if ($exists) {
            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Validate subscription payment
    |--------------------------------------------------------------------------
    */

        if ($payment->status !== 'paid') {
            throw new RuntimeException(
                'Subscription payment is not paid'
            );
        }

        if (
            (int) $payment->kitchen_subscription_id !==
            (int) $subscription->id
        ) {
            throw new RuntimeException(
                'Subscription payment mismatch'
            );
        }

        /*
    |--------------------------------------------------------------------------
    | Record owner credit
    |--------------------------------------------------------------------------
    */

        FinancialTransaction::create([
            'order_id' => null,

            'kitchen_subscription_id' =>
            $subscription->id,

            'wallet_type' =>
            'owner',

            'wallet_id' =>
            null,

            'transaction_type' =>
            'subscription',

            'payment_channel' =>
            'online',

            'credit' =>
            round((float) $payment->amount, 2),

            'debit' =>
            0,

            'balance_after' =>
            null,

            'reference' =>
            $payment->merchant_reference,

            'idempotency_key' =>
            $idempotencyKey,

            'description' =>
            'Kitchen subscription payment: '
                . $subscription->package_name,

            'meta' => [
                'subscription_id' =>
                $subscription->id,

                'package_id' =>
                $subscription->kitchen_package_id,

                'payment_id' =>
                $payment->id,

                'user_id' =>
                $subscription->user_id,

                'kitchen_id' =>
                $subscription->kitchen_id,

                'amount' =>
                round((float) $payment->amount, 2),

                'currency' =>
                $payment->currency,
            ],
        ]);
    }
}
