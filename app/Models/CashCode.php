<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashCode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'remaining_balance' => 'decimal:2',

        'discount_percentage' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',

        'used_count' => 'integer',
        'max_uses' => 'integer',

        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CashCodeUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->max_uses !== null
            && $this->used_count >= $this->max_uses;
    }
}
