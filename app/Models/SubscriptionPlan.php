<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'monthly_price',
        'visit_cadence',
        'features',
        'priority_support',
        'emergency_assistance',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'priority_support' => 'boolean',
            'emergency_assistance' => 'boolean',
        ];
    }
}
