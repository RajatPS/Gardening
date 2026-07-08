<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'plan_name',
        'start_date',
        'end_date',
        'amount',
        'status',
        'renewal_count',
        'payment_gateway',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'record_id')->where('module', 'subscriptions');
    }

    public function getPlanNameAttribute(): ?string
    {
        return $this->plan?->name;
    }
}

