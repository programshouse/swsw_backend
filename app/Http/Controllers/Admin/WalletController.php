<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletDebitRequest;
use App\services\Payments\KashierPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\services\Payments\WalletPayoutSettlementService;
use RuntimeException;
use Throwable;

class WalletController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | All debit requests
    |--------------------------------------------------------------------------
    */

    public function all_debit_requests(
        Request $request
    ) {
        $debitRequests =
            WalletDebitRequest::query()
                ->with([
                    'wallet',
                    'kitchenRequester.profile',
                    'deliveryRequester',
                    'admin',
                ])
                ->latest('id')
                ->get();

        return view(
            'admin.wallet.debit-requests',
            compact('debitRequests')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve or reject debit request
    |--------------------------------------------------------------------------
    */

    public function approve_debit_request(
        Request $request,
        WalletDebitRequest $debitRequest,
        KashierPayoutService $payoutService
    ) {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:approved,rejected',
            ],
        ]);

        $admin = auth('web')->user();

        if (!$admin) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate admin processing
        |--------------------------------------------------------------------------
        */

        if (
            $debitRequest->status !== 'pending'
            || $debitRequest->payout_status !==
                'not_started'
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'تم التعامل مع هذا الطلب من قبل'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Reject request
        |--------------------------------------------------------------------------
        */

        if ($validated['status'] === 'rejected') {
            try {
                DB::transaction(function () use (
                    $debitRequest,
                    $admin
                ) {
                    $lockedRequest =
                        WalletDebitRequest::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $debitRequest->id
                            );

                    if (
                        $lockedRequest->status !==
                            'pending'
                        || $lockedRequest
                            ->payout_status !==
                            'not_started'
                    ) {
                        throw new RuntimeException(
                            'تم التعامل مع الطلب من قبل'
                        );
                    }

                    $wallet = Wallet::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $lockedRequest
                                ->wallet_id
                        );

                    $amount = round(
                        (float) $lockedRequest
                            ->amount,
                        2
                    );

                    if (
                        (float) $wallet
                            ->pending_amount <
                        $amount
                    ) {
                        throw new RuntimeException(
                            'المبلغ المحجوز داخل المحفظة غير كافٍ'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Release reserved amount
                    |--------------------------------------------------------------------------
                    */

                    $wallet->update([
                        'available_amount' =>
                            round(
                                (float) $wallet
                                    ->available_amount
                                + $amount,
                                2
                            ),

                        'pending_amount' =>
                            round(
                                (float) $wallet
                                    ->pending_amount
                                - $amount,
                                2
                            ),
                    ]);

                    $lockedRequest->update([
                        'status' =>
                            'rejected',

                        'payout_status' =>
                            'cancelled',

                        'provider_status' =>
                            'REJECTED_BY_ADMIN',

                        'rejected_at' =>
                            now(),

                        'admin_id' =>
                            $admin->id,

                        'failure_reason' =>
                            'Withdrawal request rejected by admin',
                    ]);
                });

                return redirect()
                    ->back()
                    ->with(
                        'success',
                        'تم رفض طلب السحب وإعادة المبلغ إلى الرصيد المتاح'
                    );
            } catch (Throwable $exception) {
                Log::error(
                    'Reject wallet debit request failed',
                    [
                        'debit_request_id' =>
                            $debitRequest->id,

                        'message' =>
                            $exception->getMessage(),

                        'exception_class' =>
                            get_class(
                                $exception
                            ),
                    ]
                );

                return redirect()
                    ->back()
                    ->with(
                        'error',
                        $exception->getMessage()
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Approve request locally
        |--------------------------------------------------------------------------
        */

        try {
            DB::transaction(function () use (
                $debitRequest,
                $admin
            ) {
                $lockedRequest =
                    WalletDebitRequest::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $debitRequest->id
                        );

                if (
                    $lockedRequest->status !==
                        'pending'
                    || $lockedRequest
                        ->payout_status !==
                        'not_started'
                ) {
                    throw new RuntimeException(
                        'تم التعامل مع الطلب من قبل'
                    );
                }

                $wallet = Wallet::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedRequest->wallet_id
                    );

                if (
                    (float) $wallet
                        ->pending_amount <
                    (float) $lockedRequest
                        ->amount
                ) {
                    throw new RuntimeException(
                        'المبلغ المحجوز داخل المحفظة غير كافٍ'
                    );
                }

                $lockedRequest->update([
                    'status' =>
                        'approved',

                    'payout_status' =>
                        'processing',

                    'provider_status' =>
                        'CREATING_TRANSFER',

                    'approved_at' =>
                        now(),

                    'processing_at' =>
                        now(),

                    'admin_id' =>
                        $admin->id,

                    'failure_reason' =>
                        null,
                ]);
            });

            /*
            |--------------------------------------------------------------------------
            | Create Kashier transfer
            |--------------------------------------------------------------------------
            */

            $providerResponse =
                $payoutService->createTransfer(
                    $debitRequest->fresh()
                );

            $transfer = data_get(
                $providerResponse,
                'data.0'
            );

            if (!is_array($transfer)) {
                throw new RuntimeException(
                    'Invalid Kashier transfer response'
                );
            }

            $transferId = data_get(
                $transfer,
                'transferId'
            );

            if (!$transferId) {
                throw new RuntimeException(
                    'Kashier did not return transfer ID'
                );
            }

            $providerStatus = strtoupper(
                trim(
                    (string) data_get(
                        $transfer,
                        'status',
                        'INITIATED'
                    )
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Save transfer data
            |--------------------------------------------------------------------------
            */

            $debitRequest->update([
                'provider_transfer_id' =>
                    (string) $transferId,

                'provider_status' =>
                    $providerStatus,

                'payout_status' =>
                    'processing',

                'provider_response' =>
                    $providerResponse,

                'failure_reason' =>
                    null,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    'تمت الموافقة وبدأت Kashier معالجة التحويل'
                );
        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Unknown transfer state
            |--------------------------------------------------------------------------
            |
            | لا نعيد المبلغ لأن Kashier ربما أنشأت التحويل
            | وانقطع الاتصال قبل وصول الاستجابة.
            |
            */

            $debitRequest->refresh();

            $foundTransfer = null;

            try {
                if ($debitRequest->reference) {
                    $foundTransfer =
                        $payoutService
                            ->findTransferByMerchantReference(
                                $debitRequest
                                    ->reference
                            );
                }
            } catch (Throwable $searchException) {
                Log::error(
                    'Search Kashier transfer after create failure failed',
                    [
                        'debit_request_id' =>
                            $debitRequest->id,

                        'reference' =>
                            $debitRequest
                                ->reference,

                        'message' =>
                            $searchException
                                ->getMessage(),
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Transfer found despite request error
            |--------------------------------------------------------------------------
            */

            if (is_array($foundTransfer)) {
                $transferId = data_get(
                    $foundTransfer,
                    'transferId'
                );

                $providerStatus = strtoupper(
                    trim(
                        (string) data_get(
                            $foundTransfer,
                            'status',
                            'INITIATED'
                        )
                    )
                );

                $debitRequest->update([
                    'provider_transfer_id' =>
                        $transferId,

                    'provider_status' =>
                        $providerStatus,

                    'payout_status' =>
                        'processing',

                    'provider_response' => [
                        'recovered_from_transfer_list' =>
                            true,

                        'transfer' =>
                            $foundTransfer,
                    ],

                    'failure_reason' =>
                        null,
                ]);

                return redirect()
                    ->back()
                    ->with(
                        'success',
                        'تم العثور على التحويل في Kashier وهو قيد المعالجة'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Transfer state still unknown
            |--------------------------------------------------------------------------
            */

            $debitRequest->update([
                'payout_status' =>
                    'processing',

                'provider_status' =>
                    'CREATE_TRANSFER_UNKNOWN',

                'failure_reason' =>
                    $exception->getMessage(),
            ]);

            Log::error(
                'Create Kashier payout transfer state unknown',
                [
                    'debit_request_id' =>
                        $debitRequest->id,

                    'reference' =>
                        $debitRequest
                            ->reference,

                    'message' =>
                        $exception->getMessage(),

                    'exception_class' =>
                        get_class($exception),
                ]
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'تعذر تأكيد نتيجة إنشاء التحويل. لم تتم إعادة المبلغ لحين مراجعة Kashier.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Kashier payout account
    |--------------------------------------------------------------------------
    */

    public function kashierPayoutAccount(
        KashierPayoutService $payoutService
    ): JsonResponse {
        try {
            $account =
                $payoutService
                    ->getPrimaryAccount();

            return response()->json([
                'status' => true,

                'message' =>
                    'Kashier payout account retrieved successfully',

                'data' => [
                    'account_id' =>
                        data_get(
                            $account,
                            'accountId'
                        ),

                    'merchant_id' =>
                        data_get(
                            $account,
                            'merchantId'
                        ),

                    'merchant_name' =>
                        data_get(
                            $account,
                            'merchantName'
                        ),

                    'total_balance' =>
                        round(
                            (float) data_get(
                                $account,
                                'totalBalance',
                                0
                            ),
                            2
                        ),

                    'available_balance' =>
                        round(
                            (float) data_get(
                                $account,
                                'availableBalance',
                                0
                            ),
                            2
                        ),

                    'allowed_negative_balance' =>
                        round(
                            (float) data_get(
                                $account,
                                'allowedNegativeBalance',
                                0
                            ),
                            2
                        ),

                    'payout_method' =>
                        data_get(
                            $account,
                            'payoutMethod.method'
                        ),

                    'payout_fields' =>
                        data_get(
                            $account,
                            'payoutMethod.payoutFields',
                            []
                        ),
                ],
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'status' => false,

                'message' =>
                    'Unable to retrieve Kashier payout account',

                'error' =>
                    $exception->getMessage(),
            ], 502);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Kashier transfers
    |--------------------------------------------------------------------------
    */

    public function kashierTransfers(
        Request $request,
        KashierPayoutService $payoutService
    ): JsonResponse {
        $validated = $request->validate([
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            'sort_type' => [
                'nullable',
                'in:asc,desc',
            ],
        ]);

        try {
            $response =
                $payoutService
                    ->listTransfers(
                        page: (int) (
                            $validated['page']
                            ?? 1
                        ),

                        limit: (int) (
                            $validated['limit']
                            ?? 20
                        ),

                        sortType:
                            $validated[
                                'sort_type'
                            ] ?? 'desc'
                    );

            return response()->json([
                'status' => true,

                'message' =>
                    'Kashier transfers retrieved successfully',

                'data' =>
                    data_get(
                        $response,
                        'data',
                        []
                    ),

                'pagination' =>
                    data_get(
                        $response,
                        'pagination'
                    ),
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'status' => false,

                'message' =>
                    'Unable to retrieve Kashier transfers',

                'error' =>
                    $exception->getMessage(),
            ], 502);
        }
    }



    public function syncDebitRequest(
    WalletDebitRequest $debitRequest,
    KashierPayoutService $payoutService,
    WalletPayoutSettlementService $settlementService
) {
    if (
        $debitRequest->status !== 'approved'
        || $debitRequest->payout_status !==
            'processing'
    ) {
        return redirect()
            ->back()
            ->with(
                'error',
                'طلب السحب ليس في حالة تسمح بالمزامنة'
            );
    }

    try {
        /*
        |--------------------------------------------------------------------------
        | Find transfer at Kashier
        |--------------------------------------------------------------------------
        */

        $providerTransfer =
            $payoutService
                ->findTransferByReference(
                    $debitRequest->reference
                );

        if (!$providerTransfer) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'لم يتم العثور على التحويل في Kashier'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Update local request and wallet
        |--------------------------------------------------------------------------
        */

        $updatedRequest =
            $settlementService->synchronize(
                $debitRequest,
                $providerTransfer
            );

        $message = match (
            $updatedRequest->payout_status
        ) {
            'paid' =>
                'تم تأكيد نجاح التحويل وخصم المبلغ من الرصيد المعلق',

            'failed' =>
                'فشل التحويل وتمت إعادة المبلغ إلى الرصيد المتاح',

            default =>
                'تم تحديث حالة التحويل وهو ما زال قيد المعالجة',
        };

        return redirect()
            ->back()
            ->with(
                'success',
                $message
            );
    } catch (Throwable $exception) {
        Log::error(
            'Sync Kashier payout failed',
            [
                'debit_request_id' =>
                    $debitRequest->id,

                'reference' =>
                    $debitRequest->reference,

                'message' =>
                    $exception->getMessage(),

                'exception_class' =>
                    get_class($exception),
            ]
        );

        return redirect()
            ->back()
            ->with(
                'error',
                'تعذر تحديث حالة التحويل: '
                . $exception->getMessage()
            );
    }
}
}