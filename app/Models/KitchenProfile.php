<?php

namespace App\Models;

use App\Models\Area;
use App\Models\Government;

use App\Models\Order;
use App\Models\Rate;
use App\Models\User;
use App\Models\WorkDay;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KitchenProfile extends Model
{
    protected $fillable = [
        'user_id',
        'work_day_id',
        'government_id',
        'area_id',
        'logo',
        'cover',
        'name',
        'phone',
        'whatsapp',
        'facebook',
        'location',
        'statue',
        'rejected_note',
        'working_time_start',
        'working_time_end',
        'have_delivery',
        'have_star',
        'open_status',
    ];

    // create local scope query to get all kitchen that verified
    #[Scope]
    protected function verified_in_area(Builder $query): void
    {
        $query->where('statue', 'approved');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function government()
    {
        return $this->belongsTo(Government::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }


    public function work_day()
    {
        return $this->belongsTo(WorkDay::class, 'work_day_id');
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'kitchen_id');
    }

    public function rates()
    {
        return $this->hasMany(Rate::class, 'kitchen_profile_id');
    }
}
