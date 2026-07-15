<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'status',
        'monthly_price',
        'visit_cadence',
        'end_date',
        'features',
        'priority_support',
        'emergency_assistance',
    ];

    protected function casts(): array
    {
        return [
            'end_date' => 'date',
            'features' => 'array',
            'priority_support' => 'boolean',
            'emergency_assistance' => 'boolean',
        ];
    }
}
