<?php

namespace App\Models;

use App\Models\Wallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;

class WalletDebitRequest extends Model
{
    protected $fillable = [
        'wallet_id' ,
        'user_id' ,
        'amount' ,
        'status' ,
        'phone' ,
        'payment_method' ,
    ];

    public function wallet() {
        return $this->belongsTo(Wallet::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->hasMany(WalletTransaction::class);
    }
}
