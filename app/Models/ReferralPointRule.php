<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralPointRule extends Model
{
     protected $fillable = [
        'users_count',
        'points',
    ];
}
