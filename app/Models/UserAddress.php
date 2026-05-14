<?php

namespace App\Models;

use App\Models\Area;
use App\Models\User;
use App\Models\Government;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    public $fillable = [
        'user_id',
        'government_id',
        'area_id',
        'full_address',
        'location_link',
        'phone' ,
        'is_default'
    ];

    protected $table = 'user_address';

    public function government()
    {
        return $this->belongsTo(Government::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
