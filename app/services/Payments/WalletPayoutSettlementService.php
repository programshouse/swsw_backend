<?php

namespace App\services\Payments;

use App\Models\Wallet;
use App\Models\WalletDebitRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletPayoutSettlementService
{
    /*
    |--------------------------------------------------------------------------
    | Transferred statuses
    |--------------------------------------------------------------------------
    |
    | المبلغ تم إرساله للمستفيد، لكنه ما زال مفتوحًا
    | لاحتمال الإرجاع خلال فترة المراجعة.
    |
    */

    private const TRANSFERRED_STATUSES = [
        'APPROVED',
        'TRANSFERRED',
        'TRANSFERRED_OPEN_FOR_RETURN',
        'TRANSFERRED - OPEN FOR RETURN',
    ];

    /*
    |--------------------------------------------------------------------------
    | Final successful statuses
    |--------------------------------------------------------------------------
    */

    private const SUCCESS_STATUSES = [
        'PAID',
        'SUCCESS',
        'SUCCESSFUL',
        'COMPLETED',
        'PROCESSED',
        'FINAL_APPROVED',
    ];

    /*
    |--------------------------------------------------------------------------
    | Returned statuses
    |--------------------------------------------------------------------------
    |
    | التحويل خرج ثم عاد مرة أخرى.
    |
    */

    private const RETURNED_STATUSES = [
        'RETURNED',
        'REVERSED',
        'REFUNDED',
    ];

    /*
    |--------------------------------------------------------------------------
    | Final failed statuses
    |--------------------------------------------------------------------------
    */

    private const FAILED_STATUSES = [
        'FAILED',
        'REJECTED',
        'CANCELLED',
        'CANCELED',
        'DECLINED',
        'EXPIRED',
    ];

    /*
    |--------------------------------------------------------------------------
    | Processing statuses
    |--------------------------------------------------------------------------
    */

    private const PROCESSING_STATUSES = [
        'INITIATED',
        'PENDING',
        'PROCESSING',
        'IN_PROGRESS',
        'IN_TRANSIT',
        'QUEUED',
        'CREATED',
    ];

    /*
    |--------------------------------------------------------------------------
    | Synchronize payout status
    |--------------------------------------------------------------------------
    */

    public function synchronize(WalletDebitRequest $debitRequest, array $providerTransfer): WalletDebitRequest {
        $providerStatus = strtoupper(
            trim(
                (string) data_get(
                    $providerTransfer,
                    'status'
                )
            )
        );

        if ($providerStatus === '') {
            throw new RuntimeException(
                'Kashier transfer status is missing'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Kashier transfer
        |--------------------------------------------------------------------------
        */

        $this->validateTransfer(
            $debitRequest,
            $providerTransfer
        );

        /*
        |--------------------------------------------------------------------------
        | Transferred but open for return
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $providerStatus,
                self::TRANSFERRED_STATUSES,
                true
            )
        ) {
            return $this->markAsTransferred(
                $debitRequest,
                $providerTransfer,
                $providerStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Final successful status
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $providerStatus,
                self::SUCCESS_STATUSES,
                true
            )
        ) {
            return $this->complete(
                $debitRequest,
                $providerTransfer,
                $providerStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Returned after transfer
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $providerStatus,
                self::RETURNED_STATUSES,
                true
            )
        ) {
            return $this->returnTransferredAmount(
                $debitRequest,
                $providerTransfer,
                $providerStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Final failed status
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $providerStatus,
                self::FAILED_STATUSES,
                true
            )
        ) {
            /*
             * لو المبلغ خرج بالفعل ثم رجع Failed،
             * نعيد المبلغ إلى available بدل محاولة خصمه
             * مرة ثانية من pending.
             */
            if (
                in_array(
                    $debitRequest->payout_status,
                    [
                        'transferred',
                        'paid',
                    ],
                    true
                )
            ) {
                return $this->returnTransferredAmount(
                    $debitRequest,
                    $providerTransfer,
                    $providerStatus
                );
            }

            return $this->fail(
                $debitRequest,
                $providerTransfer,
                $providerStatus
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Still processing
        |--------------------------------------------------------------------------
        */

        return $this->keepProcessing(
            $debitRequest,
            $providerTransfer,
            $providerStatus
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mark as transferred
    |--------------------------------------------------------------------------
    */

    private function markAsTransferred(
        WalletDebitRequest $debitRequest,
        array $providerTransfer,
        string $providerStatus
    ): WalletDebitRequest {
        return DB::transaction(function () use (
            $debitRequest,
            $providerTransfer,
            $providerStatus
        ) {
            $lockedRequest =
                WalletDebitRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $debitRequest->id
                    );

            /*
             * منع خصم pending أكثر من مرة.
             */
            if (
                in_array(
                    $lockedRequest->payout_status,
                    [
                        'transferred',
                        'paid',
                    ],
                    true
                )
            ) {
                $lockedRequest->update([
                    'provider_status' =>
                        $providerStatus,

                    'provider_transfer_id' =>
                        data_get(
                            $providerTransfer,
                            'transferId',
                            $lockedRequest
                                ->provider_transfer_id
                        ),

                    'provider_response' =>
                        $providerTransfer,

                    'failure_reason' =>
                        null,
                ]);

                return $lockedRequest->fresh([
                    'wallet',
                ]);
            }

            if (
                in_array(
                    $lockedRequest->payout_status,
                    [
                        'failed',
                        'cancelled',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Payout request was already finalized as '
                    . $lockedRequest->payout_status
                );
            }

            $wallet =
                Wallet::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedRequest->wallet_id
                    );

            $amount = round(
                (float) $lockedRequest->amount,
                2
            );

            $pendingBefore = round(
                (float) $wallet->pending_amount,
                2
            );

            if ($pendingBefore < $amount) {
                throw new RuntimeException(
                    'Pending wallet balance is insufficient'
                );
            }

            $pendingAfter = round(
                $pendingBefore - $amount,
                2
            );

            /*
             * available_amount تم خصمه وقت إنشاء طلب السحب.
             * هنا فقط نحذف المبلغ من pending بعد خروجه.
             */
            $wallet->update([
                'pending_amount' =>
                    $pendingAfter,
            ]);

            WalletTransaction::firstOrCreate(
                [
                    'idempotency_key' =>
                        'wallet-payout-transferred-'
                        . $lockedRequest->id,
                ],
                [
                    'wallet_id' =>
                        $wallet->id,

                    'order_id' =>
                        null,

                    'user_id' =>
                        $lockedRequest->user_id,

                    'actor_id' =>
                        $lockedRequest->admin_id,

                    'actor_type' =>
                        'system',

                    'type' =>
                        'debit',

                    'status' =>
                        'completed',

                    'amount' =>
                        $amount,

                    'reference' =>
                        $lockedRequest->reference,

                    'balance_before' =>
                        $pendingBefore,

                    'balance_after' =>
                        $pendingAfter,

                    'description' =>
                        'Kashier payout transferred and open for return',

                    'meta' => [
                        'wallet_debit_request_id' =>
                            $lockedRequest->id,

                        'provider_transfer_id' =>
                            data_get(
                                $providerTransfer,
                                'transferId'
                            ),

                        'provider_status' =>
                            $providerStatus,

                        'pending_balance_before' =>
                            $pendingBefore,

                        'pending_balance_after' =>
                            $pendingAfter,

                        'available_amount' =>
                            round(
                                (float) $wallet
                                    ->available_amount,
                                2
                            ),

                        'provider_transfer' =>
                            $providerTransfer,
                    ],

                    'wallet_debit_request_id' =>
                        $lockedRequest->id,
                ]
            );

            $lockedRequest->update([
                'status' =>
                    'approved',

                'payout_status' =>
                    'transferred',

                'provider_status' =>
                    $providerStatus,

                'provider_transfer_id' =>
                    data_get(
                        $providerTransfer,
                        'transferId',
                        $lockedRequest
                            ->provider_transfer_id
                    ),

                'failed_at' =>
                    null,

                'failure_reason' =>
                    null,

                'provider_response' =>
                    $providerTransfer,
            ]);

            return $lockedRequest->fresh([
                'wallet',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Complete payout
    |--------------------------------------------------------------------------
    */

    private function complete(
        WalletDebitRequest $debitRequest,
        array $providerTransfer,
        string $providerStatus
    ): WalletDebitRequest {
        return DB::transaction(function () use (
            $debitRequest,
            $providerTransfer,
            $providerStatus
        ) {
            $lockedRequest =
                WalletDebitRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $debitRequest->id
                    );

            /*
             * Paid بالفعل: لا نكرر أي حركة مالية.
             */
            if (
                $lockedRequest->payout_status ===
                'paid'
            ) {
                return $lockedRequest;
            }

            /*
             * لو اتخصم المبلغ عند حالة transferred،
             * هنا نحدث الحالة فقط إلى paid.
             */
            if (
                $lockedRequest->payout_status ===
                'transferred'
            ) {
                $lockedRequest->update([
                    'status' =>
                        'approved',

                    'payout_status' =>
                        'paid',

                    'provider_status' =>
                        $providerStatus,

                    'provider_transfer_id' =>
                        data_get(
                            $providerTransfer,
                            'transferId',
                            $lockedRequest
                                ->provider_transfer_id
                        ),

                    'paid_at' =>
                        now(),

                    'failed_at' =>
                        null,

                    'failure_reason' =>
                        null,

                    'provider_response' =>
                        $providerTransfer,
                ]);

                return $lockedRequest->fresh([
                    'wallet',
                ]);
            }

            if (
                in_array(
                    $lockedRequest->payout_status,
                    [
                        'failed',
                        'cancelled',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Payout request was already finalized as '
                    . $lockedRequest->payout_status
                );
            }

            $wallet =
                Wallet::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedRequest->wallet_id
                    );

            $amount = round(
                (float) $lockedRequest->amount,
                2
            );

            $pendingBefore = round(
                (float) $wallet->pending_amount,
                2
            );

            if ($pendingBefore < $amount) {
                throw new RuntimeException(
                    'Pending wallet balance is insufficient'
                );
            }

            $pendingAfter = round(
                $pendingBefore - $amount,
                2
            );

            $wallet->update([
                'pending_amount' =>
                    $pendingAfter,
            ]);

            WalletTransaction::firstOrCreate(
                [
                    'idempotency_key' =>
                        'wallet-payout-paid-'
                        . $lockedRequest->id,
                ],
                [
                    'wallet_id' =>
                        $wallet->id,

                    'order_id' =>
                        null,

                    'user_id' =>
                        $lockedRequest->user_id,

                    'actor_id' =>
                        $lockedRequest->admin_id,

                    'actor_type' =>
                        'system',

                    'type' =>
                        'debit',

                    'status' =>
                        'completed',

                    'amount' =>
                        $amount,

                    'reference' =>
                        $lockedRequest->reference,

                    'balance_before' =>
                        $pendingBefore,

                    'balance_after' =>
                        $pendingAfter,

                    'description' =>
                        'Kashier payout completed',

                    'meta' => [
                        'wallet_debit_request_id' =>
                            $lockedRequest->id,

                        'provider_transfer_id' =>
                            data_get(
                                $providerTransfer,
                                'transferId'
                            ),

                        'provider_status' =>
                            $providerStatus,

                        'pending_balance_before' =>
                            $pendingBefore,

                        'pending_balance_after' =>
                            $pendingAfter,

                        'provider_transfer' =>
                            $providerTransfer,
                    ],

                    'wallet_debit_request_id' =>
                        $lockedRequest->id,
                ]
            );

            $lockedRequest->update([
                'status' =>
                    'approved',

                'payout_status' =>
                    'paid',

                'provider_status' =>
                    $providerStatus,

                'provider_transfer_id' =>
                    data_get(
                        $providerTransfer,
                        'transferId',
                        $lockedRequest
                            ->provider_transfer_id
                    ),

                'paid_at' =>
                    now(),

                'failed_at' =>
                    null,

                'failure_reason' =>
                    null,

                'provider_response' =>
                    $providerTransfer,
            ]);

            return $lockedRequest->fresh([
                'wallet',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Failed payout before transfer
    |--------------------------------------------------------------------------
    */

    private function fail(
        WalletDebitRequest $debitRequest,
        array $providerTransfer,
        string $providerStatus
    ): WalletDebitRequest {
        return DB::transaction(function () use (
            $debitRequest,
            $providerTransfer,
            $providerStatus
        ) {
            $lockedRequest =
                WalletDebitRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $debitRequest->id
                    );

            if (
                in_array(
                    $lockedRequest->payout_status,
                    [
                        'transferred',
                        'paid',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Transferred payout must be handled as returned'
                );
            }

            if (
                $lockedRequest->payout_status ===
                'failed'
            ) {
                return $lockedRequest;
            }

            if (
                $lockedRequest->payout_status ===
                'cancelled'
            ) {
                return $lockedRequest;
            }

            $wallet =
                Wallet::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedRequest->wallet_id
                    );

            $amount = round(
                (float) $lockedRequest->amount,
                2
            );

            $availableBefore = round(
                (float) $wallet->available_amount,
                2
            );

            $pendingBefore = round(
                (float) $wallet->pending_amount,
                2
            );

            if ($pendingBefore < $amount) {
                throw new RuntimeException(
                    'Pending wallet balance is insufficient'
                );
            }

            $availableAfter = round(
                $availableBefore + $amount,
                2
            );

            $pendingAfter = round(
                $pendingBefore - $amount,
                2
            );

            $wallet->update([
                'available_amount' =>
                    $availableAfter,

                'pending_amount' =>
                    $pendingAfter,
            ]);

            $failureReason =
                data_get(
                    $providerTransfer,
                    'failureReason'
                )
                ?? data_get(
                    $providerTransfer,
                    'failure_reason'
                )
                ?? data_get(
                    $providerTransfer,
                    'reason'
                )
                ?? data_get(
                    $providerTransfer,
                    'message'
                )
                ?? 'Kashier payout failed';

            $lockedRequest->update([
                'status' =>
                    'approved',

                'payout_status' =>
                    'failed',

                'provider_status' =>
                    $providerStatus,

                'provider_transfer_id' =>
                    data_get(
                        $providerTransfer,
                        'transferId',
                        $lockedRequest
                            ->provider_transfer_id
                    ),

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

                'provider_response' =>
                    $providerTransfer,
            ]);

            return $lockedRequest->fresh([
                'wallet',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Return transferred amount
    |--------------------------------------------------------------------------
    */

    private function returnTransferredAmount(
        WalletDebitRequest $debitRequest,
        array $providerTransfer,
        string $providerStatus
    ): WalletDebitRequest {
        return DB::transaction(function () use (
            $debitRequest,
            $providerTransfer,
            $providerStatus
        ) {
            $lockedRequest =
                WalletDebitRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $debitRequest->id
                    );

            $reversalKey =
                'wallet-payout-returned-'
                . $lockedRequest->id;

            $existingReversal =
                WalletTransaction::query()
                    ->where(
                        'idempotency_key',
                        $reversalKey
                    )
                    ->first();

            if ($existingReversal) {
                return $lockedRequest->fresh([
                    'wallet',
                ]);
            }

            if (
                $lockedRequest->payout_status ===
                'cancelled'
            ) {
                throw new RuntimeException(
                    'Cancelled payout cannot be returned'
                );
            }

            $wallet =
                Wallet::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedRequest->wallet_id
                    );

            $amount = round(
                (float) $lockedRequest->amount,
                2
            );

            $availableBefore = round(
                (float) $wallet->available_amount,
                2
            );

            $pendingBefore = round(
                (float) $wallet->pending_amount,
                2
            );

            $availableAfter = round(
                $availableBefore + $amount,
                2
            );

            /*
             * لو المبلغ اتخصم بالفعل من pending وقت transferred،
             * لا نخصمه مرة ثانية.
             */
            $wasAlreadyTransferred = in_array(
                $lockedRequest->payout_status,
                [
                    'transferred',
                    'paid',
                ],
                true
            );

            if ($wasAlreadyTransferred) {
                $pendingAfter =
                    $pendingBefore;
            } else {
                if ($pendingBefore < $amount) {
                    throw new RuntimeException(
                        'Pending wallet balance is insufficient'
                    );
                }

                $pendingAfter = round(
                    $pendingBefore - $amount,
                    2
                );
            }

            $wallet->update([
                'available_amount' =>
                    $availableAfter,

                'pending_amount' =>
                    $pendingAfter,
            ]);

            WalletTransaction::create([
                'wallet_id' =>
                    $wallet->id,

                'order_id' =>
                    null,

                'user_id' =>
                    $lockedRequest->user_id,

                'actor_id' =>
                    $lockedRequest->admin_id,

                'actor_type' =>
                    'system',

                'type' =>
                    'credit',

                'status' =>
                    'completed',

                'amount' =>
                    $amount,

                'reference' =>
                    $lockedRequest->reference,

                'balance_before' =>
                    $availableBefore,

                'balance_after' =>
                    $availableAfter,

                'description' =>
                    'Kashier payout returned to wallet',

                'meta' => [
                    'wallet_debit_request_id' =>
                        $lockedRequest->id,

                    'provider_transfer_id' =>
                        data_get(
                            $providerTransfer,
                            'transferId'
                        ),

                    'provider_status' =>
                        $providerStatus,

                    'previous_payout_status' =>
                        $lockedRequest
                            ->payout_status,

                    'available_balance_before' =>
                        $availableBefore,

                    'available_balance_after' =>
                        $availableAfter,

                    'pending_balance_before' =>
                        $pendingBefore,

                    'pending_balance_after' =>
                        $pendingAfter,

                    'provider_transfer' =>
                        $providerTransfer,
                ],

                'idempotency_key' =>
                    $reversalKey,

                'wallet_debit_request_id' =>
                    $lockedRequest->id,
            ]);

            $failureReason =
                data_get(
                    $providerTransfer,
                    'failureReason'
                )
                ?? data_get(
                    $providerTransfer,
                    'failure_reason'
                )
                ?? data_get(
                    $providerTransfer,
                    'reason'
                )
                ?? data_get(
                    $providerTransfer,
                    'message'
                )
                ?? 'Kashier payout was returned';

            $lockedRequest->update([
                'status' =>
                    'approved',

                'payout_status' =>
                    'failed',

                'provider_status' =>
                    $providerStatus,

                'provider_transfer_id' =>
                    data_get(
                        $providerTransfer,
                        'transferId',
                        $lockedRequest
                            ->provider_transfer_id
                    ),

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

                'provider_response' =>
                    $providerTransfer,
            ]);

            return $lockedRequest->fresh([
                'wallet',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Keep processing
    |--------------------------------------------------------------------------
    */

    private function keepProcessing(
        WalletDebitRequest $debitRequest,
        array $providerTransfer,
        string $providerStatus
    ): WalletDebitRequest {
        /*
         * لا نرجع transferred إلى processing لو حصل Sync آخر.
         */
        if (
            in_array(
                $debitRequest->payout_status,
                [
                    'transferred',
                    'paid',
                    'failed',
                    'cancelled',
                ],
                true
            )
        ) {
            $debitRequest->update([
                'provider_status' =>
                    $providerStatus,

                'provider_response' =>
                    $providerTransfer,
            ]);

            return $debitRequest->fresh([
                'wallet',
            ]);
        }

        $debitRequest->update([
            'status' =>
                'approved',

            'payout_status' =>
                'processing',

            'provider_status' =>
                $providerStatus,

            'provider_transfer_id' =>
                data_get(
                    $providerTransfer,
                    'transferId',
                    $debitRequest
                        ->provider_transfer_id
                ),

            'provider_response' =>
                $providerTransfer,

            'failure_reason' =>
                null,
        ]);

        return $debitRequest->fresh([
            'wallet',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validate provider transfer
    |--------------------------------------------------------------------------
    */

    private function validateTransfer(
        WalletDebitRequest $debitRequest,
        array $providerTransfer
    ): void {
        $providerReference = trim(
            (string) data_get(
                $providerTransfer,
                'merchantTransferId'
            )
        );

        $localReference = trim(
            (string) $debitRequest->reference
        );

        if (
            $providerReference === ''
            || $localReference === ''
            || !hash_equals(
                $localReference,
                $providerReference
            )
        ) {
            throw new RuntimeException(
                'Kashier payout reference mismatch'
            );
        }

        $providerAmount = round(
            (float) data_get(
                $providerTransfer,
                'amount',
                0
            ),
            2
        );

        $localAmount = round(
            (float) $debitRequest->amount,
            2
        );

        if (
            abs(
                $providerAmount - $localAmount
            ) > 0.01
        ) {
            throw new RuntimeException(
                'Kashier payout amount mismatch'
            );
        }

        $localTransferId = trim(
            (string) $debitRequest
                ->provider_transfer_id
        );

        $providerTransferId = trim(
            (string) data_get(
                $providerTransfer,
                'transferId'
            )
        );

        if (
            $localTransferId !== ''
            && $providerTransferId !== ''
            && !hash_equals(
                $localTransferId,
                $providerTransferId
            )
        ) {
            throw new RuntimeException(
                'Kashier transfer ID mismatch'
            );
        }
    }
}