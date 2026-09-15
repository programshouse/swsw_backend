<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'owner_id',
        'owner_type',
        'available_amount',
        'pending_amount'
    ];

    protected $casts = [
        'available_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
    ];

    // public function owner()
    // {
    //     return $this->belongsTo(User::class, 'owner_id', 'id');
    // }


    public function kitchenOwner()
    {
        return $this->belongsTo(
            User::class,
            'owner_id'
        );
    }

    public function deliveryOwner()
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'owner_id'
        );
    }

    public function getOwnerModelAttribute()
    {
        return match ($this->owner_type) {
            'driver' => $this->deliveryOwner,
            'kitchen',
            'client' => $this->kitchenOwner,
            default => null,
        };
    }

    public function transactions()
    {
        return $this->hasMany(
            WalletTransaction::class
        );
    }

    public function debitRequests()
    {
        return $this->hasMany(
            WalletDebitRequest::class
        );
    }
}
