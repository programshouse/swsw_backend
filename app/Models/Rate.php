<?php

namespace App\Models;

use App\Models\KitchenProfile;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'user_id',
        'kitchen_profile_id',
        'star',
        'note',
        'role'
    ];

    public function kitchen()
    {
        return $this->belongsTo(KitchenProfile::class, 'kitchen_profile_id');
    }
}
