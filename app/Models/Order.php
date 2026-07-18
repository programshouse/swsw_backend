<?php

namespace App\Models;

use App\Models\OrderPayment;
use App\Models\UserAddress;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded = []; 

   protected $casts = [
    'subtotal' => 'decimal:2',
    'vat_percentage' => 'decimal:2',
    'vat_value' => 'decimal:2',

    'distance_km' => 'decimal:2',
    'delivery_meter_price' => 'decimal:2',
    'delivery_price' => 'decimal:2',

    'kitchen_service_fee' => 'decimal:2',
    'client_service_fee' => 'decimal:2',
    'kitchen_net_amount' => 'decimal:2',

    'total_before_discount' => 'decimal:2',
    'discount_value' => 'decimal:2',
    'total' => 'decimal:2',

    'book_for_later' => 'boolean',
     'delivered_at' => 'datetime',
];

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function deliveryUsers()
    {
        return $this->belongsToMany(
            DeliveryUser::class,
            'delivery_orders'
        );
    }

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

    public function userAddress()
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function transaction()
    {
        return $this->hasOne(WalletTransaction::class);
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }


    public function cashCode()
    {
        return $this->belongsTo(CashCode::class);
    }

    public function cashCodeUsage()
    {
        return $this->hasOne(CashCodeUsage::class);
    }
}
