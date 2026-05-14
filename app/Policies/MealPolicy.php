<?php

namespace App\Policies;

use App\Models\User;

class MealPolicy
{
    public function create(User $user): bool
    {
        return $user->profile && $user->profile->statue === 'approved';
    }
}
