<?php

namespace App\Policies;

use App\Models\KitchenProfile;
use App\Models\User;

class KitchenProfilePolicy
{
    public function create(User $user): bool
    {
        return $user->status === 'active';
    }
}
