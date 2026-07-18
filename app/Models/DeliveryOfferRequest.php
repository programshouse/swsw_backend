<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOfferRequest extends Model
{
    protected $fillable = [
        'delivery_user_id',
        'offer_id',
        'status',
        'points',
        'approved_at',
        'rejected_at',
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryUser::class, 'delivery_user_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
