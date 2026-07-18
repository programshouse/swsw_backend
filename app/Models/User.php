<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Area;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\Government;
use App\Models\UserAddress;
use App\Models\KitchenProfile;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\traits\HasFirebaseNotifications;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens , HasFirebaseNotifications;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'government_id',
        'area_id',
        'role',
        'status',
        'password',
        'referral_code',
        'company_id',
        'sales_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function profile()
    {
        return $this->hasOne(KitchenProfile::class);
    }
    public function government()
    {
        return $this->belongsTo(Government::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function address()
    {
        return $this->hasMany(UserAddress::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'owner_id');
    }

    public function pointTransactions()
    {
        return $this->morphMany(PointTransaction::class, 'owner');
    }

    public function getTotalPointsAttribute()
    {
        return $this->pointTransactions()->sum('points');
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class, 'user_id')->where('is_default', 1);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function cashCodes()
    {
        return $this->hasMany(CashCode::class);
    }

    public function cashCodeUsages()
    {
        return $this->hasMany(CashCodeUsage::class);
    }

  public function salesEmployee()
{
    return $this->belongsTo(Sale::class, 'sales_id');
}
}
