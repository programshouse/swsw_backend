<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'owner_id' ,
        'owner_type' ,
        'available_amount' ,
        'pending_amount'
    ];

    public function owner() {
        return $this->belongsTo(User::class , 'owner_id' , 'id');
    }

    public function transactions() {
        return $this->hasMany(WalletTransaction::class);
    }

}
