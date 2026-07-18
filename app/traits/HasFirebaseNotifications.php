<?php

namespace App\traits;

use App\Models\AppNotification;
use App\Models\FirebaseToken;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFirebaseNotifications
{
    public function firebaseTokens(): MorphMany
    {
        return $this->morphMany(
            FirebaseToken::class,
            'tokenable'
        );
    }

    public function appNotifications(): MorphMany
    {
        return $this->morphMany(
            AppNotification::class,
            'notifiable'
        );
    }
}