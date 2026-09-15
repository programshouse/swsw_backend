<?php

namespace App\services\Payments;

use App\Models\WalletDebitRequest;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class KashierPayoutService
{
    private string $apiBaseUrl;

    private string $transferBaseUrl;

    private string $secretKey;

    private int $timeout;

    public function __construct()
    {
        /*
        |--------------------------------------------------------------------------
        | Kashier API Base URL
        |--------------------------------------------------------------------------
        |
        | يُستخدم في:
        | - Get Account Info
        | - List Transfers
        |
        */

        $this->apiBaseUrl = rtrim(
            (string) config(
                'services.kashier_payout.api_base_url'
            ),
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Kashier Transfer Base URL
        |--------------------------------------------------------------------------
        |
        | يُستخدم في:
        | - Create Single Transfer
        |
        */

        $this->transferBaseUrl = rtrim(
            (string) config(
                'services.kashier_payout.transfer_base_url'
            ),
            '/'
        );

        $this->secretKey = trim(
            (string) config(
                'services.kashier_payout.secret_key'
            )
        );

        $this->timeout = (int) config(
            'services.kashier_payout.timeout',
            30
        );

        $this->validateConfiguration();
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    */

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => $this->secretKey,
                'Content-Type' => 'application/json',
            ])
            ->connectTimeout(10)
            ->timeout($this->timeout);
    }

    /*
    |--------------------------------------------------------------------------
    | Get account information
    |--------------------------------------------------------------------------
    */

    public function getAccountInfo(): array
    {
        try {
            $response = $this->client()->get(
                $this->apiBaseUrl . '/account'
            );

            $responseData = $response->json();

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Unable to retrieve Kashier payout account: '
                        . $this->responseMessage(
                            $responseData,
                            $response->body()
                        )
                );
            }

            if (!is_array($responseData)) {
                throw new RuntimeException(
                    'Invalid Kashier payout account response'
                );
            }

            return $responseData;
        } catch (Throwable $exception) {
            Log::error(
                'Kashier get payout account failed',
                [
                    'message' => $exception->getMessage(),
                    'exception_class' => get_class($exception),
                ]
            );

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get primary account
    |--------------------------------------------------------------------------
    */

    public function getPrimaryAccount(): array
    {
        $response = $this->getAccountInfo();

        $account = data_get(
            $response,
            'data.0'
        );

        if (!is_array($account)) {
            throw new RuntimeException(
                'Kashier payout account was not found'
            );
        }

        if (
            empty($account['accountId'])
        ) {
            throw new RuntimeException(
                'Kashier payout account ID is missing'
            );
        }

        return $account;
    }

    /*
    |--------------------------------------------------------------------------
    | List Kashier transfers
    |--------------------------------------------------------------------------
    */

    public function listTransfers(
        int $page = 1,
        int $limit = 20,
        int|string $sortType = -1
    ): array {
        $page = max($page, 1);

        $limit = min(
            max($limit, 1),
            100
        );

        /*
    |--------------------------------------------------------------------------
    | Normalize Kashier sort type
    |--------------------------------------------------------------------------
    |
    | Kashier accepts:
    |
    |  1  = ascending
    | -1  = descending
    |
    | ندعم asc وdesc داخليًا أيضًا، ثم نحولهم للقيمة المطلوبة.
    |
    */

        $normalizedSortType = match (strtolower(
            trim(
                (string) $sortType
            )
        )) {
            '1',
            'asc',
            'ascending' => 1,

            '-1',
            'desc',
            'descending' => -1,

            default => -1,
        };

        try {
            $response = $this->client()->get(
                $this->apiBaseUrl . '/transfers',
                [
                    'sortType' =>
                    $normalizedSortType,

                    'limit' =>
                    $limit,

                    'page' =>
                    $page,
                ]
            );

            $responseData = $response->json();

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Unable to retrieve Kashier transfers: '
                        . $this->responseMessage(
                            $responseData,
                            $response->body()
                        )
                );
            }

            if (!is_array($responseData)) {
                throw new RuntimeException(
                    'Invalid Kashier transfers response'
                );
            }

            return $responseData;
        } catch (Throwable $exception) {
            Log::error(
                'Kashier list payout transfers failed',
                [
                    'page' =>
                    $page,

                    'limit' =>
                    $limit,

                    'sort_type' =>
                    $normalizedSortType,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Find transfer by merchant reference
    |--------------------------------------------------------------------------
    |
    | تستخدم بعد خطأ اتصال غير مؤكد، للتأكد هل Kashier
    | أنشأت التحويل بالفعل أم لا.
    |
    */

    public function findTransferByMerchantReference(string $merchantTransferId, int $maxPages = 5): ?array
    {
        $merchantTransferId = trim(
            $merchantTransferId
        );

        if ($merchantTransferId === '') {
            throw new RuntimeException(
                'Merchant transfer ID is required'
            );
        }

        $maxPages = min(
            max($maxPages, 1),
            20
        );

        for ($page = 1; $page <= $maxPages; $page++) {
            $response = $this->listTransfers(
                page: $page,
                limit: 100,
                sortType: 'desc'
            );

            $transfers = data_get(
                $response,
                'data',
                []
            );

            if (!is_array($transfers)) {
                continue;
            }

            foreach ($transfers as $transfer) {
                if (!is_array($transfer)) {
                    continue;
                }

                if (
                    (string) data_get(
                        $transfer,
                        'merchantTransferId'
                    ) === $merchantTransferId
                ) {
                    return $transfer;
                }
            }

            $totalPages = (int) data_get(
                $response,
                'pagination.pages',
                1
            );

            if ($page >= $totalPages) {
                break;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Create single transfer
    |--------------------------------------------------------------------------
    */

    public function createTransfer(
        WalletDebitRequest $debitRequest
    ): array {
        $debitRequest->loadMissing([
            'kitchenRequester',
            'deliveryRequester',
            'user',
        ]);

        $recipient = match ($debitRequest->requester_type) {
            'driver' =>
            $debitRequest->deliveryRequester,

            'kitchen' =>
            $debitRequest->kitchenRequester
                ?? $debitRequest->user,

            default =>
            $debitRequest->user,
        };

        if (!$recipient) {
            throw new RuntimeException(
                'Transfer recipient was not found'
            );
        }

        $method = $this->resolveTransferMethod(
            $debitRequest->payment_method
        );

        $recipientNumber = trim(
            (string) (
                data_get(
                    $debitRequest->destination_snapshot,
                    'recipient_number'
                )
                ?: $debitRequest->phone
            )
        );

        if ($recipientNumber === '') {
            throw new RuntimeException(
                'Recipient number is missing'
            );
        }

        $recipientName = trim(
            (string) (
                data_get(
                    $debitRequest->destination_snapshot,
                    'beneficiary_name'
                )
                ?: $recipient->name
            )
        );

        if ($recipientName === '') {
            throw new RuntimeException(
                'Recipient name is missing'
            );
        }

        $amount = round(
            (float) $debitRequest->amount,
            2
        );

        if ($amount <= 0) {
            throw new RuntimeException(
                'Transfer amount must be greater than zero'
            );
        }

        $merchantTransferId = trim(
            (string) $debitRequest->reference
        );

        if ($merchantTransferId === '') {
            throw new RuntimeException(
                'Merchant transfer reference is missing'
            );
        }

        $payload = [
            'amount' =>
            $amount,

            'method' =>
            $method,

            'recipientName' =>
            $recipientName,

            'merchantTransferId' =>
            $merchantTransferId,

            'recipientNumber' =>
            $recipientNumber,
        ];

        $recipientBank = trim(
            (string) data_get(
                $debitRequest->destination_snapshot,
                'recipient_bank'
            )
        );

        if ($method === 'bank') {
            if ($recipientBank === '') {
                throw new RuntimeException(
                    'Recipient bank is required for bank transfer'
                );
            }

            $payload['recipientBank'] =
                $recipientBank;
        }

        try {
            Log::info(
                'Creating Kashier payout transfer',
                [
                    'debit_request_id' =>
                    $debitRequest->id,

                    'merchant_transfer_id' =>
                    $merchantTransferId,

                    'amount' =>
                    $amount,

                    'method' =>
                    $method,

                    'recipient_number' =>
                    $recipientNumber,

                    'recipient_bank' =>
                    $recipientBank ?: null,
                ]
            );

            $response = $this->client()->post(
                $this->transferBaseUrl
                    . '/transfers/single',
                $payload
            );

            $responseData = $response->json();

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Kashier transfer request failed: '
                        . $this->responseMessage(
                            $responseData,
                            $response->body()
                        )
                );
            }

            if (!is_array($responseData)) {
                throw new RuntimeException(
                    'Invalid Kashier transfer response'
                );
            }

            $transfer = data_get(
                $responseData,
                'data.0'
            );

            if (!is_array($transfer)) {
                throw new RuntimeException(
                    'Kashier transfer data is missing'
                );
            }

            if (empty($transfer['transferId'])) {
                throw new RuntimeException(
                    'Kashier did not return transfer ID'
                );
            }

            Log::info(
                'Kashier payout transfer created',
                [
                    'debit_request_id' =>
                    $debitRequest->id,

                    'merchant_transfer_id' =>
                    $merchantTransferId,

                    'provider_transfer_id' =>
                    data_get(
                        $transfer,
                        'transferId'
                    ),

                    'provider_status' =>
                    data_get(
                        $transfer,
                        'status'
                    ),
                ]
            );

            return $responseData;
        } catch (Throwable $exception) {
            Log::error(
                'Create Kashier payout transfer failed',
                [
                    'debit_request_id' =>
                    $debitRequest->id,

                    'merchant_transfer_id' =>
                    $merchantTransferId,

                    'message' =>
                    $exception->getMessage(),

                    'exception_class' =>
                    get_class($exception),
                ]
            );

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve transfer method
    |--------------------------------------------------------------------------
    */

    private function resolveTransferMethod(
        string $paymentMethod
    ): string {
        return match ($paymentMethod) {
            'wallet',
            'vodafone_cash',
            'etisalat_cash',
            'orange_cash' => 'wallet',

            'instapay' => 'instant wallet',

            'bank_transfer' => 'bank',

            default => throw new RuntimeException(
                'Unsupported payout method: '
                    . $paymentMethod
            ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Response message
    |--------------------------------------------------------------------------
    */

    private function responseMessage(
        mixed $responseData,
        string $responseBody
    ): string {
        if (is_array($responseData)) {
            return (string) (
                data_get($responseData, 'messages.en')
                ?? data_get($responseData, 'message')
                ?? data_get($responseData, 'error.message')
                ?? json_encode(
                    $responseData,
                    JSON_UNESCAPED_UNICODE
                )
            );
        }

        return $responseBody !== ''
            ? $responseBody
            : 'Unknown Kashier response';
    }

    /*
    |--------------------------------------------------------------------------
    | Validate configuration
    |--------------------------------------------------------------------------
    */

    private function validateConfiguration(): void
    {
        $missing = [];

        if ($this->apiBaseUrl === '') {
            $missing[] =
                'KASHIER_PAYOUT_API_BASE_URL';
        }

        if ($this->transferBaseUrl === '') {
            $missing[] =
                'KASHIER_PAYOUT_TRANSFER_BASE_URL';
        }

        if ($this->secretKey === '') {
            $missing[] =
                'KASHIER_PAYOUT_SECRET_KEY';
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                'Missing Kashier payout configuration: '
                    . implode(', ', $missing)
            );
        }
    }



    public function findTransferByReference(
        string $merchantTransferId,
        ?string $providerTransferId = null,
        int $maxPages = 20
    ): ?array {
        $merchantTransferId = trim(
            $merchantTransferId
        );

        $providerTransferId = trim(
            (string) $providerTransferId
        );

        if (
            $merchantTransferId === ''
            && $providerTransferId === ''
        ) {
            throw new RuntimeException(
                'Transfer reference or transfer ID is required'
            );
        }

        $maxPages = min(
            max($maxPages, 1),
            50
        );

        for (
            $page = 1;
            $page <= $maxPages;
            $page++
        ) {
            $response = $this->listTransfers(
                page: $page,
                limit: 100,
                sortType: -1
            );

            /*
        |--------------------------------------------------------------------------
        | Resolve transfers array
        |--------------------------------------------------------------------------
        |
        | ندعم أكثر من شكل محتمل للـ response.
        |
        */

            $transfers = data_get(
                $response,
                'data',
                []
            );

            if (
                is_array($transfers)
                && isset($transfers['transfers'])
                && is_array($transfers['transfers'])
            ) {
                $transfers =
                    $transfers['transfers'];
            }

            if (!is_array($transfers)) {
                throw new RuntimeException(
                    'Invalid Kashier transfers response'
                );
            }

            foreach ($transfers as $transfer) {
                if (!is_array($transfer)) {
                    continue;
                }

                /*
            |--------------------------------------------------------------------------
            | Provider transfer ID
            |--------------------------------------------------------------------------
            */

                $remoteTransferId = trim(
                    (string) (
                        data_get(
                            $transfer,
                            'transferId'
                        )
                        ?? data_get(
                            $transfer,
                            '_id'
                        )
                        ?? data_get(
                            $transfer,
                            'id'
                        )
                    )
                );

                if (
                    $providerTransferId !== ''
                    && $remoteTransferId !== ''
                    && hash_equals(
                        $providerTransferId,
                        $remoteTransferId
                    )
                ) {
                    return $transfer;
                }

                /*
            |--------------------------------------------------------------------------
            | Merchant transfer reference
            |--------------------------------------------------------------------------
            */

                $remoteMerchantReference = trim(
                    (string) (
                        data_get(
                            $transfer,
                            'merchantTransferId'
                        )
                        ?? data_get(
                            $transfer,
                            'merchantTransferID'
                        )
                        ?? data_get(
                            $transfer,
                            'merchant_transfer_id'
                        )
                        ?? data_get(
                            $transfer,
                            'reference'
                        )
                    )
                );

                if (
                    $merchantTransferId !== ''
                    && $remoteMerchantReference !== ''
                    && hash_equals(
                        $merchantTransferId,
                        $remoteMerchantReference
                    )
                ) {
                    return $transfer;
                }
            }

            $totalPages = max(
                (int) (
                    data_get(
                        $response,
                        'pagination.pages'
                    )
                    ?? data_get(
                        $response,
                        'pagination.totalPages'
                    )
                    ?? 1
                ),
                1
            );

            if ($page >= $totalPages) {
                break;
            }
        }

        return null;
    }
}
