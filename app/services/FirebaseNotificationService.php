<?php

namespace App\services;

use App\Models\AppNotification;
use App\Models\FirebaseToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use RuntimeException;
use Throwable;

class FirebaseNotificationService
{
    private Messaging $messaging;

   public function __construct()
{
    $credentialsPath = storage_path(
        'app/firebase/firebase-service-account.json'
    );

    if (!file_exists($credentialsPath)) {
        throw new RuntimeException(
            'Firebase service account file not found at: '
            . $credentialsPath
        );
    }

    $factory = (new Factory())
        ->withServiceAccount($credentialsPath);

    $this->messaging = $factory->createMessaging();
}

    public function sendToUser(
        Model $user,
        string $appType,
        string $type,
        string $titleAr,
        string $titleEn,
        string $bodyAr,
        string $bodyEn,
        array $data = []
    ): ?AppNotification {
        if (!$user->exists) {
            return null;
        }

        $storedNotification = AppNotification::create([
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->getKey(),

            'type' => $type,

            'title_ar' => $titleAr,
            'title_en' => $titleEn,

            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,

            'data' => $data,
        ]);

        $tokens = FirebaseToken::query()
            ->where(
                'tokenable_type',
                $user->getMorphClass()
            )
            ->where(
                'tokenable_id',
                $user->getKey()
            )
            ->where('app_type', $appType)
            ->where('is_active', true)
            ->get();

        foreach ($tokens as $firebaseToken) {
            $isArabic = $firebaseToken->language === 'ar';

            $title = $isArabic
                ? $titleAr
                : $titleEn;

            $body = $isArabic
                ? $bodyAr
                : $bodyEn;

            $this->sendToToken(
                firebaseToken: $firebaseToken,
                title: $title,
                body: $body,
                data: array_merge($data, [
                    'notification_id' =>
                        (string) $storedNotification->id,

                    'notification_type' => $type,
                    'app_type' => $appType,
                ])
            );
        }

        return $storedNotification;
    }

    private function sendToToken(
        FirebaseToken $firebaseToken,
        string $title,
        string $body,
        array $data
    ): void {
        try {
            $normalizedData = $this->normalizeData($data);

            $message = CloudMessage::withTarget(
                'token',
                $firebaseToken->token
            )
                ->withNotification(
                    Notification::create(
                        $title,
                        $body
                    )
                )
                ->withData($normalizedData)
                ->withAndroidConfig([
                    'priority' => 'high',

                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'orders_channel',
                    ],
                ])
                ->withApnsConfig([
                    'headers' => [
                        'apns-priority' => '10',
                    ],

                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'content-available' => 1,
                        ],
                    ],
                ]);

            $this->messaging->send($message);

            $firebaseToken->update([
                'last_used_at' => now(),
            ]);
        } catch (NotFound|InvalidMessage $exception) {
            $firebaseToken->update([
                'is_active' => false,
            ]);

            Log::warning('Invalid Firebase token', [
                'firebase_token_id' => $firebaseToken->id,
                'message' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Firebase notification failed', [
                'firebase_token_id' => $firebaseToken->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $normalized[$key] = $value
                    ? '1'
                    : '0';

                continue;
            }

            if (is_array($value) || is_object($value)) {
                $normalized[$key] = json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE
                );

                continue;
            }

            $normalized[$key] = (string) ($value ?? '');
        }

        return $normalized;
    }
}