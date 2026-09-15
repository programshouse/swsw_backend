<?php

namespace App\Models;

use App\Models\Wallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;

class WalletDebitRequest extends Model
{
    protected $fillable = [
        'wallet_id',

        'user_id',
        'requester_id',
        'requester_type',

        'amount',
        'status',

        'phone',
        'payment_method',

        'reference',
        'provider',
        'provider_transfer_id',
        'provider_status',
        'payout_status',

        'approved_at',
        'rejected_at',
        'processing_at',
        'paid_at',
        'failed_at',

        'failure_reason',
        'provider_response',
        'callback_payload',
        'destination_snapshot',

        'admin_id',
        'phone',
    'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',

        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processing_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',

        'provider_response' => 'array',
        'callback_payload' => 'array',
        'destination_snapshot' => 'array',
    ];
    public function wallet()
    {
        return $this->belongsTo(
            Wallet::class
        );
    }

    /*
     * للتوافق مع طلبات المطابخ القديمة.
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function kitchenRequester()
    {
        return $this->belongsTo(
            User::class,
            'requester_id'
        );
    }

    public function deliveryRequester()
    {
        return $this->belongsTo(
            DeliveryUser::class,
            'requester_id'
        );
    }

    public function admin()
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }

    public function transactions()
    {
        return $this->hasMany(
            WalletTransaction::class,
            'wallet_debit_request_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Requester accessor
    |--------------------------------------------------------------------------
    */

    public function getRequesterAttribute()
    {
        return match ($this->requester_type) {
            'driver' =>
            $this->deliveryRequester,

            'kitchen' =>
            $this->kitchenRequester
                ?? $this->user,

            default =>
            $this->user,
        };
    }
}
