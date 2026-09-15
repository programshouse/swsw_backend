<?php

namespace App\Http\Controllers;

use App\Models\DeliveryUser;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletDebitRequest;
use App\services\Payments\KashierPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use App\services\Payments\WalletPayoutSettlementService;
use Illuminate\Http\JsonResponse;
use App\Exports\WalletDebitRequestsExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Exports\WalletDebitRequestsPdfExport;
use Barryvdh\DomPDF\Facade\Pdf;



class WalletController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | My wallet
    |--------------------------------------------------------------------------
    */

    public function my_wallet(Request $request)
    {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $isDelivery = $account instanceof DeliveryUser;

        $ownerType = $isDelivery
            ? 'delivery'
            : 'kitchen';

        $wallet = Wallet::query()
            ->where('owner_id', $account->id)
            ->where('owner_type', $ownerType)
            ->first();

        return response()->json([
            'status' => true,
            'wallet' => $wallet,
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Create debit request
    |--------------------------------------------------------------------------
    */

    public function create_debit_request(Request $request)
    {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'password' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * في المحافظ يكون رقم الهاتف.
             * في التحويل البنكي يمكن إرسال رقم الحساب في نفس الحقل.
             */
            'phone' => [
                'required',
                'string',
                'max:191',
            ],

            'payment_method' => [
                'required',
                'in:wallet,vodafone_cash,orange_cash,etisalat_cash,instapay,bank_transfer,card',
            ],

            'recipient_bank' => [
                'required_if:payment_method,bank_transfer',
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        if (
            !Hash::check(
                $validated['password'],
                $account->password
            )
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid password',
            ], 401);
        }

        $isDelivery = $account instanceof DeliveryUser;

        /*
         * wallet_debit_requests.requester_type:
         * kitchen | driver
         */
        $requesterType = $isDelivery
            ? 'driver'
            : 'kitchen';

        /*
         * wallets.owner_type:
         * kitchen | client | delivery
         */
        $ownerType = $isDelivery
            ? 'delivery'
            : 'kitchen';

        try {
            $debitRequest = DB::transaction(
                function () use (
                    $account,
                    $validated,
                    $requesterType,
                    $ownerType,
                    $isDelivery
                ) {
                    $wallet = Wallet::query()
                        ->where('owner_id', $account->id)
                        ->where('owner_type', $ownerType)
                        ->lockForUpdate()
                        ->first();

                    if (!$wallet) {
                        throw new RuntimeException(
                            'Wallet not found'
                        );
                    }

                    $requestedAmount = round(
                        (float) $validated['amount'],
                        2
                    );

                    $availableAmount = round(
                        (float) $wallet->available_amount,
                        2
                    );

                    if (
                        $requestedAmount >
                        $availableAmount
                    ) {
                        throw new RuntimeException(
                            'Insufficient available wallet balance'
                        );
                    }

                    do {
                        $reference =
                            'PAYOUT-'
                            . strtoupper($requesterType)
                            . '-'
                            . $account->id
                            . '-'
                            . now()->format('YmdHis')
                            . '-'
                            . strtoupper(Str::random(6));
                    } while (
                        WalletDebitRequest::query()
                        ->where('reference', $reference)
                        ->exists()
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Reserve withdrawal amount
                    |--------------------------------------------------------------------------
                    |
                    | نقل المبلغ من الرصيد المتاح إلى الرصيد المعلق.
                    |
                    */

                    $wallet->update([
                        'available_amount' => round(
                            $availableAmount -
                                $requestedAmount,
                            2
                        ),

                        'pending_amount' => round(
                            (float) $wallet->pending_amount +
                                $requestedAmount,
                            2
                        ),
                    ]);

                    return WalletDebitRequest::create([
                        'wallet_id' => $wallet->id,

                        /*
                         * المطبخ موجود في users.
                         * الدليفري موجود في delivery_users.
                         */
                        'user_id' => $isDelivery
                            ? null
                            : $account->id,

                        'requester_id' => $account->id,

                        'requester_type' => $requesterType,

                        'amount' => $requestedAmount,

                        'status' => 'pending',

                        'phone' => $validated['phone'],

                        'payment_method' =>
                        $validated['payment_method'],

                        'reference' => $reference,

                        'provider' => 'kashier',

                        'provider_transfer_id' => null,

                        'provider_status' => null,

                        'payout_status' => 'not_started',

                        'destination_snapshot' => [
                            'payment_method' =>
                            $validated['payment_method'],

                            'phone' =>
                            $validated['phone'],

                            'recipient_number' =>
                            $validated['phone'],

                            'recipient_bank' =>
                            $validated['recipient_bank']
                                ?? null,

                            'beneficiary_name' =>
                            $account->name,

                            'requester_type' =>
                            $requesterType,

                            'requester_id' =>
                            $account->id,
                        ],
                    ]);
                }
            );

            return response()->json([
                'status' => true,

                'message' =>
                'Debit request created successfully',

                'debit_request' =>
                $debitRequest,
            ], 201);
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            Log::error(
                'Create debit request failed',
                [
                    'requester_id' => $account->id,
                    'requester_type' => $requesterType,
                    'message' => $exception->getMessage(),
                    'exception_class' => get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,

                'message' =>
                'Unable to create debit request',

                'error' =>
                $exception->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | My debit requests
    |--------------------------------------------------------------------------
    */

    public function my_debit_requests(Request $request)
    {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $requesterType =
            $account instanceof DeliveryUser
            ? 'driver'
            : 'kitchen';

        $debitRequests =
            WalletDebitRequest::query()
            ->where(
                'requester_id',
                $account->id
            )
            ->where(
                'requester_type',
                $requesterType
            )
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'debit_requests' => $debitRequests,
        ], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Kitchen wallet transactions
    |--------------------------------------------------------------------------
    */

    public function wallet_transactions_for_kitchen(
        Request $request,
        User $kitchen
    ) {
        $role = $kitchen->role instanceof \BackedEnum
            ? $kitchen->role->value
            : $kitchen->role;

        if ($role !== 'kitchen') {
            abort(404);
        }

        $wallet = Wallet::query()
            ->where('owner_id', $kitchen->id)
            ->where('owner_type', 'kitchen')
            ->first();

        if ($wallet) {
            $wallet->load([
                'transactions.order',
            ]);
        }

        return view(
            'admin.wallet.kitchen-transactions',
            [
                'kitchen' => $kitchen,
                'wallet' => $wallet,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | All debit requests Blade page
    |--------------------------------------------------------------------------
    */

   public function all_debit_requests(Request $request)
{
    $status = $request->query('status');

    $payoutStatus =
        $request->query('payout_status');

    $requesterType =
        $request->query('requester_type');

    $providerTransferId =
        trim((string) $request->query('provider_transfer_id'));

    $requesterName =
        trim((string) $request->query('requester_name'));

    $dateFrom =
        $request->query('date_from');

    $dateTo =
        $request->query('date_to');

    $keyword =
        trim((string) $request->query('keyword'));

    $debitRequests =
        WalletDebitRequest::query()
        ->with([
            'wallet',
            'kitchenRequester.profile',
            'deliveryRequester',
            'admin',
        ])
        ->when(
            in_array(
                $status,
                [
                    'pending',
                    'approved',
                    'rejected',
                ],
                true
            ),
            function ($query) use ($status) {
                $query->where(
                    'status',
                    $status
                );
            }
        )
        ->when(
            in_array(
                $payoutStatus,
                [
                    'not_started',
                    'processing',
                    'paid',
                    'failed',
                    'cancelled',
                ],
                true
            ),
            function ($query) use (
                $payoutStatus
            ) {
                $query->where(
                    'payout_status',
                    $payoutStatus
                );
            }
        )
        ->when(
            in_array(
                $requesterType,
                [
                    'kitchen',
                    'driver',
                ],
                true
            ),
            function ($query) use (
                $requesterType
            ) {
                $query->where(
                    'requester_type',
                    $requesterType
                );
            }
        )
        ->when(
            $providerTransferId !== '',
            function ($query) use ($providerTransferId) {
                $query->where(
                    'provider_transfer_id',
                    'like',
                    "%{$providerTransferId}%"
                );
            }
        )
        ->when(
            $requesterName !== '',
            function ($query) use ($requesterName) {
                $query->where(function ($subQuery) use ($requesterName) {
                    $subQuery
                        ->whereHas('kitchenRequester.profile', function ($q) use ($requesterName) {
                            $q->where('name', 'like', "%{$requesterName}%");
                        })
                        ->orWhereHas('deliveryRequester', function ($q) use ($requesterName) {
                            $q->where('name', 'like', "%{$requesterName}%");
                        });
                });
            }
        )
        ->when(
            !empty($dateFrom),
            function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        )
        ->when(
            !empty($dateTo),
            function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        )
        ->when(
            $keyword !== '',
            function ($query) use ($keyword) {
                $query->where(
                    function ($subQuery) use (
                        $keyword
                    ) {
                        $subQuery
                            ->where(
                                'reference',
                                'like',
                                "%{$keyword}%"
                            )
                            ->orWhere(
                                'provider_transfer_id',
                                'like',
                                "%{$keyword}%"
                            )
                            ->orWhere(
                                'phone',
                                'like',
                                "%{$keyword}%"
                            );
                    }
                );
            }
        )
        ->latest('id')
        ->paginate(20)
        ->withQueryString();

    return view(
        'admin.wallet.debit-requests',
        compact(
            'debitRequests',
            'status',
            'payoutStatus',
            'requesterType',
            'providerTransferId',
            'requesterName',
            'dateFrom',
            'dateTo',
            'keyword'
        )
    );
}

    /*
    |--------------------------------------------------------------------------
    | Approve or reject debit request
    |--------------------------------------------------------------------------
    */

    public function approve_debit_request(
        Request $request,
        WalletDebitRequest $debitRequest
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

        if (
            $debitRequest->status !== 'pending'
            || $debitRequest->payout_status !== 'not_started'
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
    | Reject
    |--------------------------------------------------------------------------
    */

        if ($validated['status'] === 'rejected') {
            try {
                DB::transaction(function () use (
                    $debitRequest,
                    $admin
                ) {
                    $lockedRequest = WalletDebitRequest::query()
                        ->lockForUpdate()
                        ->findOrFail($debitRequest->id);

                    if (
                        $lockedRequest->status !== 'pending'
                        || $lockedRequest->payout_status !== 'not_started'
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

                    $amount = round(
                        (float) $lockedRequest->amount,
                        2
                    );

                    if (
                        (float) $wallet->pending_amount
                        < $amount
                    ) {
                        throw new RuntimeException(
                            'المبلغ المحجوز داخل المحفظة غير كافٍ'
                        );
                    }

                    $wallet->update([
                        'available_amount' => round(
                            (float) $wallet->available_amount
                                + $amount,
                            2
                        ),

                        'pending_amount' => round(
                            (float) $wallet->pending_amount
                                - $amount,
                            2
                        ),
                    ]);

                    $lockedRequest->update([
                        'status' => 'rejected',
                        'payout_status' => 'cancelled',
                        'provider_status' => 'REJECTED_BY_ADMIN',
                        'rejected_at' => now(),
                        'admin_id' => $admin->id,
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
    | Approve locally only
    |--------------------------------------------------------------------------
    */

        try {
            DB::transaction(function () use (
                $debitRequest,
                $admin
            ) {
                $lockedRequest = WalletDebitRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($debitRequest->id);

                if (
                    $lockedRequest->status !== 'pending'
                    || $lockedRequest->payout_status !== 'not_started'
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
                    (float) $wallet->pending_amount
                    < (float) $lockedRequest->amount
                ) {
                    throw new RuntimeException(
                        'المبلغ المحجوز داخل المحفظة غير كافٍ'
                    );
                }

                $lockedRequest->update([
                    'status' => 'approved',

                    'payout_status' =>
                    'approved_to_withdraw',

                    'provider_status' =>
                    'APPROVED_BY_ADMIN',

                    'approved_at' => now(),

                    'processing_at' => null,

                    'admin_id' => $admin->id,

                    'failure_reason' => null,
                ]);
            });

            return redirect()
                ->back()
                ->with(
                    'success',
                    'تمت الموافقة على طلب السحب ويمكن للمستخدم تنفيذ التحويل من التطبيق'
                );
        } catch (Throwable $exception) {
            Log::error(
                'Approve wallet debit request failed',
                [
                    'debit_request_id' =>
                    $debitRequest->id,

                    'message' =>
                    $exception->getMessage(),
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
    | Kashier payout account Blade page
    |--------------------------------------------------------------------------
    */

    public function kashierPayoutAccount(
        KashierPayoutService $payoutService
    ) {
        $account = null;
        $error = null;

        try {
            $account =
                $payoutService
                ->getPrimaryAccount();
        } catch (Throwable $exception) {
            $error = $exception->getMessage();

            Log::error(
                'Load Kashier payout account page failed',
                [
                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );
        }

        return view(
            'admin.wallet.kashier-payout-account',
            compact(
                'account',
                'error'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Kashier transfers Blade page
    |--------------------------------------------------------------------------
    */

    public function kashierTransfers(
        Request $request,
        KashierPayoutService $payoutService
    ) {
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

        $page = (int) (
            $validated['page'] ?? 1
        );

        $limit = (int) (
            $validated['limit'] ?? 20
        );

        $sortType =
            $validated['sort_type'] ?? 'desc';

        $transfers = [];
        $pagination = null;
        $error = null;

        try {
            $response =
                $payoutService
                ->listTransfers(
                    page: $page,
                    limit: $limit,
                    sortType: $sortType
                );

            $transfers = data_get(
                $response,
                'data',
                []
            );

            $pagination = data_get(
                $response,
                'pagination'
            );
        } catch (Throwable $exception) {
            $error = $exception->getMessage();

            Log::error(
                'Load Kashier transfers page failed',
                [
                    'page' => $page,
                    'limit' => $limit,
                    'sort_type' => $sortType,
                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );
        }

        return view(
            'admin.wallet.kashier-transfers',
            compact(
                'transfers',
                'pagination',
                'error',
                'page',
                'limit',
                'sortType'
            )
        );
    }






    public function executeApprovedWithdrawal(Request $request, WalletDebitRequest $debitRequest, KashierPayoutService $payoutService)
    {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Resolve requester type
    |--------------------------------------------------------------------------
    */

        $requesterType =
            $account instanceof \App\Models\DeliveryUser
            ? 'driver'
            : 'kitchen';

        /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

        if (
            (int) $debitRequest->requester_id !==
            (int) $account->id
            || $debitRequest->requester_type !==
            $requesterType
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Request must be approved by admin
    |--------------------------------------------------------------------------
    */

        if (
            $debitRequest->status !== 'approved'
            || $debitRequest->payout_status !==
            'approved_to_withdraw'
        ) {
            return response()->json([
                'status' => false,

                'message' =>
                'Withdrawal request is not ready for transfer',

                'request_status' =>
                $debitRequest->status,

                'payout_status' =>
                $debitRequest->payout_status,
            ], 422);
        }

        try {
            /*
        |--------------------------------------------------------------------------
        | Mark request as processing
        |--------------------------------------------------------------------------
        */

            DB::transaction(function () use (
                $debitRequest
            ) {
                $lockedRequest =
                    WalletDebitRequest::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $debitRequest->id
                    );

                if (
                    $lockedRequest->status !==
                    'approved'
                    || $lockedRequest->payout_status !==
                    'approved_to_withdraw'
                ) {
                    throw new RuntimeException(
                        'Withdrawal request is already being processed'
                    );
                }

                $wallet = Wallet::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedRequest->wallet_id
                    );

                $amount = round(
                    (float) $lockedRequest->amount,
                    2
                );

                if (
                    (float) $wallet->pending_amount <
                    $amount
                ) {
                    throw new RuntimeException(
                        'Pending wallet balance is insufficient'
                    );
                }

                $lockedRequest->update([
                    'payout_status' =>
                    'processing',

                    'provider_status' =>
                    'CREATING_TRANSFER',

                    'processing_at' =>
                    now(),

                    'failure_reason' =>
                    null,
                ]);
            });

            /*
        |--------------------------------------------------------------------------
        | Create transfer through Kashier
        |--------------------------------------------------------------------------
        |
        | المبلغ لا يأتي من Flutter.
        | يتم استخدام المبلغ الموافق عليه والمخزن داخل الطلب.
        |
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
        | Save Kashier transfer data
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

            return response()->json([
                'status' => true,

                'message' =>
                'Withdrawal transfer started successfully',

                'data' => [
                    'request_id' =>
                    $debitRequest->id,

                    'amount' =>
                    round(
                        (float) $debitRequest->amount,
                        2
                    ),

                    'status' =>
                    $debitRequest->status,

                    'payout_status' =>
                    'processing',

                    'provider_status' =>
                    $providerStatus,

                    'provider_transfer_id' =>
                    (string) $transferId,

                    'reference' =>
                    $debitRequest->reference,
                ],
            ], 200);
        } catch (Throwable $exception) {
            $debitRequest->refresh();

            $errorMessage =
                $exception->getMessage();

            /*
        |--------------------------------------------------------------------------
        | Authentication or configuration failure
        |--------------------------------------------------------------------------
        |
        | Invalid token معناها أن Kashier رفضت الطلب قبل إنشاء التحويل.
        | لذلك نرجع الطلب إلى approved_to_withdraw حتى يمكن إعادة المحاولة.
        |
        */

            $normalizedError =
                strtolower($errorMessage);

            $isAuthenticationError =
                str_contains(
                    $normalizedError,
                    'invalid token'
                )
                || str_contains(
                    $normalizedError,
                    'unauthorized'
                )
                || str_contains(
                    $normalizedError,
                    'forbidden'
                )
                || str_contains(
                    $normalizedError,
                    'authentication'
                );

            if ($isAuthenticationError) {
                $debitRequest->update([
                    'payout_status' =>
                    'approved_to_withdraw',

                    'provider_status' =>
                    'AUTHENTICATION_FAILED',

                    'provider_transfer_id' =>
                    null,

                    'processing_at' =>
                    null,

                    'failure_reason' =>
                    $errorMessage,
                ]);

                Log::error(
                    'Kashier payout authentication failed',
                    [
                        'debit_request_id' =>
                        $debitRequest->id,

                        'requester_id' =>
                        $account->id,

                        'requester_type' =>
                        $requesterType,

                        'reference' =>
                        $debitRequest->reference,

                        'message' =>
                        $errorMessage,

                        'exception_class' =>
                        get_class($exception),
                    ]
                );

                return response()->json([
                    'status' => false,

                    'message' =>
                    'Kashier payout authentication failed',

                    'error' =>
                    $errorMessage,

                    'data' => [
                        'request_id' =>
                        $debitRequest->id,

                        'status' =>
                        'approved',

                        'payout_status' =>
                        'approved_to_withdraw',

                        'provider_status' =>
                        'AUTHENTICATION_FAILED',

                        'can_retry' =>
                        true,
                    ],
                ], 502);
            }

            /*
        |--------------------------------------------------------------------------
        | Search transfer after uncertain failure
        |--------------------------------------------------------------------------
        |
        | في timeout أو انقطاع الاتصال قد يكون Kashier أنشأت التحويل
        | ولكن الرد لم يصل إلى الباك إند.
        |
        */

            $foundTransfer = null;

            try {
                if ($debitRequest->reference) {
                    $foundTransfer =
                        $payoutService
                        ->findTransferByReference(
                            $debitRequest->reference
                        );
                }
            } catch (Throwable $searchException) {
                Log::error(
                    'Search Kashier transfer after execution failure failed',
                    [
                        'debit_request_id' =>
                        $debitRequest->id,

                        'reference' =>
                        $debitRequest->reference,

                        'message' =>
                        $searchException->getMessage(),
                    ]
                );
            }

            /*
        |--------------------------------------------------------------------------
        | Transfer was found
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

                return response()->json([
                    'status' => true,

                    'message' =>
                    'Withdrawal transfer was found and is being processed',

                    'data' => [
                        'request_id' =>
                        $debitRequest->id,

                        'amount' =>
                        round(
                            (float) $debitRequest->amount,
                            2
                        ),

                        'status' =>
                        'approved',

                        'payout_status' =>
                        'processing',

                        'provider_status' =>
                        $providerStatus,

                        'provider_transfer_id' =>
                        $transferId,

                        'reference' =>
                        $debitRequest->reference,
                    ],
                ], 200);
            }

            /*
        |--------------------------------------------------------------------------
        | Unknown transfer state
        |--------------------------------------------------------------------------
        |
        | هنا لا نرجع المبلغ ولا نسمح بإعادة التنفيذ؛
        | لأن التحويل قد يكون تم إنشاؤه بالفعل.
        |
        */

            $debitRequest->update([
                'payout_status' =>
                'processing',

                'provider_status' =>
                'CREATE_TRANSFER_UNKNOWN',

                'failure_reason' =>
                $errorMessage,
            ]);

            Log::error(
                'Execute approved withdrawal failed',
                [
                    'debit_request_id' =>
                    $debitRequest->id,

                    'requester_id' =>
                    $account->id,

                    'requester_type' =>
                    $requesterType,

                    'reference' =>
                    $debitRequest->reference,

                    'message' =>
                    $errorMessage,

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,

                'message' =>
                'Unable to confirm withdrawal transfer creation',

                'error' =>
                $errorMessage,

                'data' => [
                    'request_id' =>
                    $debitRequest->id,

                    'status' =>
                    $debitRequest->status,

                    'payout_status' =>
                    'processing',

                    'provider_status' =>
                    'CREATE_TRANSFER_UNKNOWN',

                    'can_retry' =>
                    false,
                ],
            ], 502);
        }
    }




    public function syncApprovedWithdrawal(
        Request $request,
        WalletDebitRequest $debitRequest,
        KashierPayoutService $payoutService,
        WalletPayoutSettlementService $settlementService
    ) {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $requesterType =
            $account instanceof \App\Models\DeliveryUser
            ? 'driver'
            : 'kitchen';

        /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    */

        if (
            (int) $debitRequest->requester_id !==
            (int) $account->id
            || $debitRequest->requester_type !==
            $requesterType
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        /*
    |--------------------------------------------------------------------------
    | Already paid
    |--------------------------------------------------------------------------
    */

        if ($debitRequest->payout_status === 'paid') {
            return response()->json([
                'status' => true,
                'message' =>
                'Withdrawal was transferred successfully',

                'data' =>
                $this->withdrawalResponse(
                    $debitRequest->fresh([
                        'wallet',
                    ])
                ),
            ], 200);
        }

        /*
    |--------------------------------------------------------------------------
    | Already failed
    |--------------------------------------------------------------------------
    */

        if ($debitRequest->payout_status === 'failed') {
            return response()->json([
                'status' => false,
                'message' =>
                'Withdrawal transfer failed',

                'data' =>
                $this->withdrawalResponse(
                    $debitRequest->fresh([
                        'wallet',
                    ])
                ),
            ], 200);
        }

        if (
            $debitRequest->status !== 'approved'
            || $debitRequest->payout_status !==
            'processing'
        ) {
            return response()->json([
                'status' => false,

                'message' =>
                'Withdrawal request is not being processed',

                'data' =>
                $this->withdrawalResponse(
                    $debitRequest->fresh([
                        'wallet',
                    ])
                ),
            ], 422);
        }

        if (!$debitRequest->reference) {
            return response()->json([
                'status' => false,
                'message' =>
                'Withdrawal reference is missing',
            ], 422);
        }

        try {
            /*
        |--------------------------------------------------------------------------
        | Find transfer in Kashier
        |--------------------------------------------------------------------------
        |
        | يتم البحث باستخدام merchantTransferId.
        |
        */

            $providerTransfer =
                $payoutService
                ->findTransferByReference(
                    merchantTransferId: (string) $debitRequest->reference,

                    providerTransferId: (string) $debitRequest
                        ->provider_transfer_id
                );

            if (!$providerTransfer) {
                return response()->json([
                    'status' => false,

                    'message' =>
                    'Transfer was not found in Kashier',

                    'data' =>
                    $this->withdrawalResponse(
                        $debitRequest->fresh([
                            'wallet',
                        ])
                    ),
                ], 404);
            }

            /*
        |--------------------------------------------------------------------------
        | Apply provider status
        |--------------------------------------------------------------------------
        */

            $updatedRequest =
                $settlementService->synchronize(
                    $debitRequest,
                    $providerTransfer
                );

            $message = match ($updatedRequest->payout_status) {
                'paid' =>
                'Withdrawal was transferred successfully',

                'failed' =>
                'Withdrawal failed and the amount was returned to the wallet',

                default =>
                'Withdrawal transfer is still being processed',
            };

            return response()->json([
                'status' =>
                $updatedRequest
                    ->payout_status !==
                    'failed',

                'message' =>
                $message,

                'data' =>
                $this->withdrawalResponse(
                    $updatedRequest
                ),
            ], 200);
        } catch (Throwable $exception) {
            Log::error(
                'Sync approved withdrawal failed',
                [
                    'debit_request_id' =>
                    $debitRequest->id,

                    'requester_id' =>
                    $account->id,

                    'requester_type' =>
                    $requesterType,

                    'reference' =>
                    $debitRequest->reference,

                    'provider_transfer_id' =>
                    $debitRequest
                        ->provider_transfer_id,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            return response()->json([
                'status' => false,

                'message' =>
                'Unable to update withdrawal status',

                'error' =>
                $exception->getMessage(),

                'data' =>
                $this->withdrawalResponse(
                    $debitRequest->fresh([
                        'wallet',
                    ])
                ),
            ], 502);
        }
    }


    private function withdrawalResponse(
        WalletDebitRequest $debitRequest
    ): array {
        $wallet = $debitRequest->wallet;

        return [
            'request_id' =>
            $debitRequest->id,

            'amount' =>
            round(
                (float) $debitRequest->amount,
                2
            ),

            'status' =>
            $debitRequest->status,

            'payout_status' =>
            $debitRequest->payout_status,

            'provider_status' =>
            $debitRequest->provider_status,

            'provider_transfer_id' =>
            $debitRequest
                ->provider_transfer_id,

            'reference' =>
            $debitRequest->reference,

            'failure_reason' =>
            $debitRequest->failure_reason,

            'can_execute' =>
            $debitRequest->status ===
                'approved'
                && $debitRequest
                ->payout_status ===
                'approved_to_withdraw',

            'is_processing' =>
            $debitRequest
                ->payout_status ===
                'processing',

            'is_paid' =>
            $debitRequest
                ->payout_status ===
                'paid',

            'is_failed' =>
            $debitRequest
                ->payout_status ===
                'failed',

            'wallet' => $wallet
                ? [
                    'available_amount' =>
                    round(
                        (float) $wallet
                            ->available_amount,
                        2
                    ),

                    'pending_amount' =>
                    round(
                        (float) $wallet
                            ->pending_amount,
                        2
                    ),
                ]
                : null,

            'approved_at' =>
            $debitRequest->approved_at
                ?->toISOString(),

            'processing_at' =>
            $debitRequest->processing_at
                ?->toISOString(),

            'paid_at' =>
            $debitRequest->paid_at
                ?->toISOString(),

            'failed_at' =>
            $debitRequest->failed_at
                ?->toISOString(),
        ];
    }







    public function myWalletWithDebitRequests(
        Request $request
    ): JsonResponse {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Resolve account type
    |--------------------------------------------------------------------------
    |
    | المطبخ موجود داخل users.
    | الدليفري موجود داخل delivery_users.
    |
    */

        $isDelivery =
            $account instanceof \App\Models\DeliveryUser;

        $ownerType = $isDelivery
            ? 'delivery'
            : 'kitchen';

        $requesterType = $isDelivery
            ? 'driver'
            : 'kitchen';

        /*
    |--------------------------------------------------------------------------
    | Get wallet
    |--------------------------------------------------------------------------
    */

        $wallet = Wallet::query()
            ->where(
                'owner_id',
                $account->id
            )
            ->where(
                'owner_type',
                $ownerType
            )
            ->first();

        /*
    |--------------------------------------------------------------------------
    | Get withdrawal requests
    |--------------------------------------------------------------------------
    */

        $debitRequests =
            WalletDebitRequest::query()
            ->where(
                'requester_id',
                $account->id
            )
            ->where(
                'requester_type',
                $requesterType
            )
            ->latest('id')
            ->get()
            ->map(function (
                WalletDebitRequest $debitRequest
            ) {
                $statusMessageAr = match ($debitRequest->payout_status) {
                    'not_started' =>
                    'طلب السحب قيد مراجعة الإدارة',

                    'approved_to_withdraw' =>
                    'تمت الموافقة على طلب السحب ويمكنك تنفيذ التحويل الآن',

                    'processing' =>
                    'جارٍ تنفيذ التحويل',

                    'paid' =>
                    'تم تحويل المبلغ بنجاح',

                    'failed' =>
                    'فشل التحويل وتمت إعادة المبلغ إلى المحفظة',

                    'cancelled' =>
                    'تم رفض أو إلغاء طلب السحب',

                    default =>
                    'حالة طلب السحب غير معروفة',
                };

                $statusMessageEn = match ($debitRequest->payout_status) {
                    'not_started' =>
                    'Withdrawal request is under admin review',

                    'approved_to_withdraw' =>
                    'Withdrawal request was approved and is ready for transfer',

                    'processing' =>
                    'Withdrawal transfer is being processed',

                    'paid' =>
                    'Withdrawal was transferred successfully',

                    'failed' =>
                    'Withdrawal failed and the amount was returned to the wallet',

                    'cancelled' =>
                    'Withdrawal request was rejected or cancelled',

                    default =>
                    'Unknown withdrawal status',
                };

                return [
                    'id' =>
                    $debitRequest->id,

                    'amount' =>
                    round(
                        (float) $debitRequest->amount,
                        2
                    ),

                    'status' =>
                    $debitRequest->status,

                    'payout_status' =>
                    $debitRequest->payout_status,

                    'provider_status' =>
                    $debitRequest->provider_status,

                    'provider_transfer_id' =>
                    $debitRequest
                        ->provider_transfer_id,

                    'reference' =>
                    $debitRequest->reference,

                    'payment_method' =>
                    $debitRequest
                        ->payment_method,

                    'phone' =>
                    $debitRequest->phone,

                    'failure_reason' =>
                    $debitRequest
                        ->failure_reason,

                    /*
                    |--------------------------------------------------------------------------
                    | Flutter actions
                    |--------------------------------------------------------------------------
                    */

                    'can_execute_transfer' =>
                    $debitRequest->status ===
                        'approved'
                        && $debitRequest
                        ->payout_status ===
                        'approved_to_withdraw',

                    'can_sync_transfer' =>
                    $debitRequest->status ===
                        'approved'
                        && $debitRequest
                        ->payout_status ===
                        'processing',

                    'can_cancel' =>
                    $debitRequest->status ===
                        'pending'
                        && $debitRequest
                        ->payout_status ===
                        'not_started',

                    'is_pending_admin' =>
                    $debitRequest->status ===
                        'pending'
                        && $debitRequest
                        ->payout_status ===
                        'not_started',

                    'is_approved' =>
                    $debitRequest->status ===
                        'approved',

                    'is_processing' =>
                    $debitRequest
                        ->payout_status ===
                        'processing',

                    'is_paid' =>
                    $debitRequest
                        ->payout_status ===
                        'paid',

                    'is_failed' =>
                    $debitRequest
                        ->payout_status ===
                        'failed',

                    'is_cancelled' =>
                    $debitRequest
                        ->payout_status ===
                        'cancelled',

                    'message_ar' =>
                    $statusMessageAr,

                    'message_en' =>
                    $statusMessageEn,

                    /*
                    |--------------------------------------------------------------------------
                    | Dates
                    |--------------------------------------------------------------------------
                    */

                    'created_at' =>
                    $debitRequest->created_at
                        ?->toISOString(),

                    'approved_at' =>
                    $debitRequest->approved_at
                        ?->toISOString(),

                    'rejected_at' =>
                    $debitRequest->rejected_at
                        ?->toISOString(),

                    'processing_at' =>
                    $debitRequest->processing_at
                        ?->toISOString(),

                    'paid_at' =>
                    $debitRequest->paid_at
                        ?->toISOString(),

                    'failed_at' =>
                    $debitRequest->failed_at
                        ?->toISOString(),
                ];
            });

        return response()->json([
            'status' => true,

            'message' =>
            'Wallet and withdrawal requests retrieved successfully',

            'data' => [
                'wallet' => [
                    'id' =>
                    $wallet?->id,

                    'owner_id' =>
                    $account->id,

                    'owner_type' =>
                    $ownerType,

                    'available_amount' =>
                    round(
                        (float) (
                            $wallet?->available_amount
                            ?? 0
                        ),
                        2
                    ),

                    'pending_amount' =>
                    round(
                        (float) (
                            $wallet?->pending_amount
                            ?? 0
                        ),
                        2
                    ),

                    'total_amount' =>
                    round(
                        (float) (
                            (
                                $wallet?->available_amount
                                ?? 0
                            )
                            +
                            (
                                $wallet?->pending_amount
                                ?? 0
                            )
                        ),
                        2
                    ),
                ],

                'debit_requests_count' =>
                $debitRequests->count(),

                'debit_requests' =>
                $debitRequests,
            ],
        ], 200);
    }







    public function withdrawalRequestStatus(
        Request $request,
        WalletDebitRequest $debitRequest
    ): JsonResponse {
        $account = $request->user();

        if (!$account) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

        $requesterType = $account instanceof DeliveryUser
            ? 'driver'
            : 'kitchen';

        if (
            (int) $debitRequest->requester_id !== (int) $account->id
            || $debitRequest->requester_type !== $requesterType
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal status retrieved successfully',
            'data' => [
                'id' => $debitRequest->id,
                'amount' => round((float) $debitRequest->amount, 2),
                'status' => $debitRequest->status,
                'payout_status' => $debitRequest->payout_status,
                'provider_status' => $debitRequest->provider_status,
                'failure_reason' => $debitRequest->failure_reason,

                /*
            |--------------------------------------------------------------------------
            | Flutter Actions
            |--------------------------------------------------------------------------
            */

                'can_execute_transfer' =>
                $debitRequest->status === 'approved'
                    && $debitRequest->payout_status === 'approved_to_withdraw',

                'can_sync_transfer' =>
                $debitRequest->status === 'approved'
                    && $debitRequest->payout_status === 'processing',
            ],
        ], 200);
    }



    public function syncDebitRequest(
    WalletDebitRequest $debitRequest,
    KashierPayoutService $payoutService,
    WalletPayoutSettlementService $settlementService
) {
    try {
        if (
            $debitRequest->status !== 'approved'
            || !in_array(
                $debitRequest->payout_status,
                [
                    'processing',
                    'transferred',
                ],
                true
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'طلب السحب غير جاهز لتحديث حالة التحويل'
                );
        }

        if (
            !$debitRequest->reference
            && !$debitRequest->provider_transfer_id
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'بيانات التحويل غير مكتملة'
                );
        }

        $providerTransfer =
            $payoutService->findTransferByReference(
                merchantTransferId:
                    (string) $debitRequest->reference,

                providerTransferId:
                    (string) $debitRequest
                        ->provider_transfer_id
            );

        if (!$providerTransfer) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'لم يتم العثور على التحويل داخل Kashier'
                );
        }

        $updatedRequest =
            $settlementService->synchronize(
                $debitRequest,
                $providerTransfer
            );

        $message = match (
            $updatedRequest->payout_status
        ) {
            'transferred' =>
                'تم تنفيذ التحويل وأصبح مفتوحًا لاحتمال الإرجاع',

            'paid' =>
                'تم اعتماد التحويل نهائيًا',

            'failed' =>
                'فشل أو تم إرجاع التحويل، وتمت إعادة المبلغ إلى المحفظة',

            default =>
                'تم تحديث حالة التحويل، وما زال قيد المعالجة',
        };

        return redirect()
            ->back()
            ->with(
                'success',
                $message
            );
    } catch (\Throwable $exception) {
        Log::error(
            'Admin sync wallet debit request failed',
            [
                'debit_request_id' =>
                    $debitRequest->id,

                'reference' =>
                    $debitRequest->reference,

                'provider_transfer_id' =>
                    $debitRequest
                        ->provider_transfer_id,

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


public function export_debit_requests(Request $request)
{
    
    $filters = $request->only([
        'status',
        'payout_status',
        'requester_type',
        'provider_transfer_id',
        'requester_name',
        'date_from',
        'date_to',
        'keyword',
    ]);

    $fileName = 'wallet-debit-requests-' . now()->format('Y-m-d_H-i') . '.xlsx';

    return Excel::download(
        new WalletDebitRequestsExport($filters),
        $fileName
    );
}
}






























// function approve_debit_request(Request $request, WalletDebitRequest $debit_request , WalletService $walletService) {
    //     $validated = $request->validate([
    //         'status' => 'required|in:approved,rejected'
    //     ]);

    //     $debit_request->update([
    //         'status' => $validated['status']
    //     ]);
    //     if ($validated['status'] == 'approved') {
    //         $walletService->debit($request, $debit_request);
    //     }

    //     return response()->json([
    //         'message' => 'Debit request ' . $validated['status'] . ' successfully',
    //         'debit_request' => $debit_request
    //     ], 200);
    // }

    // function all_debit_requests(Request $request) {
    //     $debit_requests = WalletDebitRequest::with('wallet', 'user' , 'user.profile')->get();

    //     return response()->json([
    //         'debit_requests' => $debit_requests
    //     ], 200);
    // }

    // function wallet_transactions_for_kitchen(Request $request , User $kitchen) {
    //     $wallet_owner = $kitchen ;

    //     $kitchen_wallet = $wallet_owner->wallet;
    //     $kitchen_wallet->load('transactions', 'transactions.order' );

    //     return response()->json([
    //         'wallet' => $kitchen_wallet ,
    //     ] , 200);
    // }