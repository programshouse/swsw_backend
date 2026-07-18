<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Sale extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'sales';

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active'
            ? 'نشط'
            : 'غير نشط';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

     public function kitchens()
    {
        return $this->hasMany(User::class, 'sales_id')
            ->where('role', 'kitchen');
    }
}
