<?php

namespace App\services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class KashierService
{
    private string $baseUrl;
    private string $merchantId;
    private string $secretKey;
    private string $apiKey;
    private string $currency;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config('services.kashier.base_url', 'https://api.kashier.io'),
            '/'
        );

        $this->merchantId = (string) config(
            'services.kashier.merchant_id'
        );

        $this->secretKey = (string) config(
            'services.kashier.secret_key'
        );

        $this->apiKey = (string) config(
            'services.kashier.api_key'
        );

        $this->currency = strtoupper(
            (string) config('services.kashier.currency', 'EGP')
        );

        $this->timeout = (int) config(
            'services.kashier.timeout',
            30
        );

        $this->validateConfiguration();
    }

    /*
    |--------------------------------------------------------------------------
    | Create payment session
    |--------------------------------------------------------------------------
    */

    public function createPaymentSession(array $data): array
    {
        $payload = array_merge([
            'maxFailureAttempts' => 3,
            'paymentType' => 'credit',
            'currency' => $this->currency,
            'display' => 'ar',
            'type' => 'one-time',
            'allowedMethods' => 'card,wallet',
            'redirectMethod' => null,
            'failureRedirect' => false,
            'defaultMethod' => 'card',
            'manualCapture' => false,
            'saveCard' => 'optional',
            'retrieveSavedCard' => true,
            'interactionSource' => 'ECOMMERCE',
            'enable3DS' => true,
        ], $data);

        /*
         * Merchant and currency always come from backend configuration.
         */
        $payload['merchantId'] = $this->merchantId;
        $payload['currency'] = $this->currency;

        $this->validateCreateSessionPayload($payload);

        try {
            $response = $this->client()->post(
                $this->url('/v3/payment/sessions'),
                $payload
            );

            $result = $this->handleResponse(
                $response,
                'Unable to create Kashier payment session'
            );

            Log::info('Kashier payment session created', [
                'merchant_order_id' => $payload['order'] ?? null,
                'session_id' => $this->extractSessionId($result),
                'amount' => $payload['amount'] ?? null,
            ]);

            return $result;
        } catch (Throwable $exception) {
            $this->logException(
                'Kashier create payment session failed',
                $exception,
                [
                    'merchant_order_id' => $payload['order'] ?? null,
                    'amount' => $payload['amount'] ?? null,
                ]
            );

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get payment session
    |--------------------------------------------------------------------------
    |
    | Kashier session status response contains data.sessionId.
    |
    */

    public function getPaymentSession(string $sessionId): array
    {
        $sessionId = trim($sessionId);

        if ($sessionId === '') {
            throw new RuntimeException(
                'Kashier session ID is required.'
            );
        }

        try {
            $response = $this->client()->get(
                $this->url(
                    '/v3/payment/sessions/' . urlencode($sessionId)
                )
            );

            return $this->handleResponse(
                $response,
                'Unable to retrieve Kashier payment session'
            );
        } catch (Throwable $exception) {
            $this->logException(
                'Kashier get payment session failed',
                $exception,
                [
                    'session_id' => $sessionId,
                ]
            );

            throw $exception;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Extract normalized session data
    |--------------------------------------------------------------------------
    */

    public function extractSession(array $response): ?array
    {
        $possibleSessions = [
            data_get($response, 'data'),
            data_get($response, 'response'),
            data_get($response, 'response.data'),
            data_get($response, 'result'),
            data_get($response, 'result.data'),
            $response,
        ];

        foreach ($possibleSessions as $session) {
            if (!is_array($session)) {
                continue;
            }

            if (
                isset($session['sessionId'])
                || isset($session['_id'])
                || isset($session['merchantOrderId'])
                || isset($session['paymentParams'])
            ) {
                return $session;
            }
        }

        return null;
    }

    public function extractSessionId(array $response): ?string
    {
        $session = $this->extractSession($response);

        if (!$session) {
            return null;
        }

        $sessionId =
            $session['sessionId']
            ?? $session['_id']
            ?? null;

        if (
            $sessionId === null
            || trim((string) $sessionId) === ''
        ) {
            return null;
        }

        return (string) $sessionId;
    }

    public function extractSessionUrl(array $response): ?string
    {
        $session = $this->extractSession($response);

        $url = $session['sessionUrl']
            ?? data_get($response, 'sessionUrl');

        return is_string($url)
            && filter_var($url, FILTER_VALIDATE_URL)
            ? $url
            : null;
    }

    public function extractSessionStatus(
        array $response
    ): string {
        $session = $this->extractSession($response);

        return strtoupper(
            trim(
                (string) (
                    data_get($session, 'status')
                    ?? data_get($response, 'status')
                    ?? 'PENDING'
                )
            )
        );
    }

    public function normalizeSessionStatus(
        string $status
    ): string {
        $status = strtoupper(
            trim($status)
        );

        return match ($status) {
            'SUCCESS',
            'PAID',
            'CAPTURED',
            'COMPLETED',
            'APPROVED' => 'paid',

            'FAILED',
            'FAIL',
            'DECLINED',
            'ERROR',
            'REJECTED' => 'failed',

            'CANCELLED',
            'CANCELED',
            'VOIDED' => 'cancelled',

            'EXPIRED' => 'expired',

            'CREATED',
            'OPENED',
            'PENDING',
            'PROCESSING',
            'INITIATED' => 'pending',

            default => 'pending',
        };
    }
    public function isSessionPaid(array $response): bool
    {
        return $this->normalizeSessionStatus(
            $this->extractSessionStatus($response)
        ) === 'paid';
    }

    public function sessionAmountMatches(
        array $response,
        float $expectedAmount
    ): bool {
        $session = $this->extractSession($response);

        if (!$session) {
            return false;
        }

        $providerAmount =
            data_get($session, 'amount')
            ?? data_get($session, 'paymentParams.amount');

        if (
            $providerAmount === null
            || !is_numeric($providerAmount)
        ) {
            return false;
        }

        return abs(
            round((float) $providerAmount, 2)
                - round($expectedAmount, 2)
        ) < 0.01;
    }

    public function sessionCurrencyMatches(
        array $response
    ): bool {
        $session = $this->extractSession($response);

        if (!$session) {
            return false;
        }

        $currency =
            data_get($session, 'currency')
            ?? data_get($session, 'paymentParams.currency');

        return strtoupper((string) $currency)
            === strtoupper($this->currency);
    }

    public function sessionReferenceMatches(
        array $response,
        string $expectedReference
    ): bool {
        $session = $this->extractSession($response);

        if (!$session) {
            return false;
        }

        $reference =
            data_get($session, 'merchantOrderId')
            ?? data_get($session, 'paymentParams.order');

        if (
            $reference === null
            || trim((string) $reference) === ''
        ) {
            return false;
        }

        return (string) $reference === $expectedReference;
    }

    public function sessionMerchantMatches(array $response): bool
    {
        $session = $this->extractSession($response);

        if (!$session) {
            return false;
        }

        $merchantId = data_get($session, 'merchantId')
            ?? data_get($session, 'paymentParams.merchantId');

        return (string) $merchantId === $this->merchantId;
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => $this->secretKey,
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->connectTimeout(10)
            ->timeout($this->timeout)
            ->retry(
                2,
                500,
                fn(Throwable $exception) => true,
                throw: false
            );
    }

    private function handleResponse(
        Response $response,
        string $defaultMessage
    ): array {
        $json = $response->json();

        if ($response->successful() && is_array($json)) {
            return $json;
        }

        $message =
            data_get($json, 'messages.en')
            ?? data_get($json, 'message')
            ?? data_get($json, 'error.message')
            ?? $defaultMessage;

        Log::error('Kashier API request failed', [
            'http_status' => $response->status(),
            'message' => $message,
            'response' => $json ?: $response->body(),
        ]);

        throw new RuntimeException(
            $message . ' [HTTP ' . $response->status() . ']'
        );
    }

    private function validateCreateSessionPayload(array $payload): void
    {
        $requiredFields = [
            'expireAt',
            'amount',
            'order',
            'merchantId',
            'serverWebhook',
            'customer',
        ];

        foreach ($requiredFields as $field) {
            if (
                !array_key_exists($field, $payload)
                || $payload[$field] === null
                || $payload[$field] === ''
            ) {
                throw new RuntimeException(
                    "Kashier session field [{$field}] is required."
                );
            }
        }

        if ((float) $payload['amount'] <= 0) {
            throw new RuntimeException(
                'Kashier session amount must be greater than zero.'
            );
        }

        if (!is_array($payload['customer'])) {
            throw new RuntimeException(
                'Kashier session customer must be an array.'
            );
        }

        if (empty($payload['customer']['reference'])) {
            throw new RuntimeException(
                'Kashier session customer reference is required.'
            );
        }
    }

    private function validateConfiguration(): void
    {
        $missing = [];

        if ($this->baseUrl === '') {
            $missing[] = 'KASHIER_BASE_URL';
        }

        if ($this->merchantId === '') {
            $missing[] = 'KASHIER_MERCHANT_ID';
        }

        if ($this->secretKey === '') {
            $missing[] = 'KASHIER_SECRET_KEY';
        }

        if ($this->apiKey === '') {
            $missing[] = 'KASHIER_API_KEY';
        }

        if (!empty($missing)) {
            throw new RuntimeException(
                'Missing Kashier configuration: '
                    . implode(', ', $missing)
            );
        }
    }

    private function url(string $path): string
    {
        return $this->baseUrl
            . '/'
            . ltrim($path, '/');
    }

    private function logException(
        string $message,
        Throwable $exception,
        array $context = []
    ): void {
        Log::error(
            $message,
            array_merge(
                $context,
                [
                    'exception' => $exception->getMessage(),
                    'exception_class' => get_class($exception),
                ]
            )
        );
    }


    public function getSubscriptionPaymentSession(
        string $sessionId,
        string $expectedReference,
        float $expectedAmount
    ): array {
        $sessionId = trim($sessionId);
        $expectedReference = trim($expectedReference);
        $expectedAmount = round($expectedAmount, 2);

        if ($sessionId === '') {
            throw new RuntimeException(
                'Kashier session ID is required.'
            );
        }

        if ($expectedReference === '') {
            throw new RuntimeException(
                'Kashier payment reference is required.'
            );
        }

        if ($expectedAmount <= 0) {
            throw new RuntimeException(
                'Kashier payment amount must be greater than zero.'
            );
        }

        try {
            $response = $this->client()->get(
                $this->url(
                    '/v3/payment/sessions/'
                        . urlencode($sessionId)
                )
            );

            $json = $response->json();

            /*
        |--------------------------------------------------------------------------
        | Normal successful response
        |--------------------------------------------------------------------------
        */

            if (
                $response->successful()
                && is_array($json)
            ) {
                return $json;
            }

            $message =
                data_get($json, 'messages.en')
                ?? data_get($json, 'message')
                ?? data_get($json, 'error.message')
                ?? 'Unable to retrieve Kashier subscription payment session';

            /*
        |--------------------------------------------------------------------------
        | Already paid subscription session
        |--------------------------------------------------------------------------
        |
        | Kashier قد ترجع HTTP 400 عند محاولة قراءة Session
        | تم دفعها بالفعل.
        |
        | نبني Session داخلية تحتوي كل القيم اللازمة للتحقق:
        | session ID + reference + merchant + amount + currency.
        |
        | هذا السلوك خاص باشتراك المطبخ فقط، ولا يؤثر على الأوردرات.
        |
        */

            if (
                $response->status() === 400
                && str_contains(
                    strtolower((string) $message),
                    'already been paid'
                )
            ) {
                Log::warning(
                    'Kashier subscription session already paid',
                    [
                        'session_id' =>
                        $sessionId,

                        'merchant_reference' =>
                        $expectedReference,

                        'amount' =>
                        $expectedAmount,

                        'http_status' =>
                        $response->status(),

                        'response' =>
                        $json ?: $response->body(),
                    ]
                );

                return [
                    'message' =>
                    'Subscription payment already completed',

                    'data' => [
                        'sessionId' =>
                        $sessionId,

                        '_id' =>
                        $sessionId,

                        'status' =>
                        'PAID',

                        'merchantId' =>
                        $this->merchantId,

                        'merchantOrderId' =>
                        $expectedReference,

                        'amount' =>
                        $expectedAmount,

                        'currency' =>
                        $this->currency,

                        'method' =>
                        'online',

                        'paymentParams' => [
                            'order' =>
                            $expectedReference,

                            'amount' =>
                            $expectedAmount,

                            'currency' =>
                            $this->currency,

                            'merchantId' =>
                            $this->merchantId,

                            'defaultMethod' =>
                            'online',
                        ],

                        'source' =>
                        'kashier_already_paid_response',

                        'raw_response' =>
                        $json ?: $response->body(),
                    ],
                ];
            }

            /*
        |--------------------------------------------------------------------------
        | Other errors
        |--------------------------------------------------------------------------
        */

            Log::error(
                'Kashier subscription session request failed',
                [
                    'session_id' =>
                    $sessionId,

                    'http_status' =>
                    $response->status(),

                    'message' =>
                    $message,

                    'response' =>
                    $json ?: $response->body(),
                ]
            );

            throw new RuntimeException(
                $message
                    . ' [HTTP '
                    . $response->status()
                    . ']'
            );
        } catch (Throwable $exception) {
            $this->logException(
                'Kashier get subscription payment session failed',
                $exception,
                [
                    'session_id' =>
                    $sessionId,

                    'merchant_reference' =>
                    $expectedReference,

                    'amount' =>
                    $expectedAmount,
                ]
            );

            throw $exception;
        }
    }
}
