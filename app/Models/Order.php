<?php

namespace App\Models;

use App\Models\OrderPayment;
use App\Models\UserAddress;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'kitchen_id',
        'total',
        'address_id',
        'status',
        'receive_date',
        'receive_time',
        'book_for_later',
        'cancel_date',
        'delivered_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kitchen()
    {
        return $this->belongsTo(KitchenProfile::class, 'kitchen_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function address() {
        return $this->belongsTo(UserAddress::class);
    }

     public function transaction() {
        return $this->hasOne(WalletTransaction::class);
    }

}
