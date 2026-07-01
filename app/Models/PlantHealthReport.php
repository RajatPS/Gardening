<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantHealthReport extends Model
{
    protected $fillable = [
        'user_id',
        'service_booking_id',
        'plant_name',
        'health_status',
        'before_images',
        'after_images',
        'treatments_applied',
        'products_used',
        'expert_notes',
    ];

    protected function casts(): array
    {
        return [
            'before_images' => 'array',
            'after_images' => 'array',
            'treatments_applied' => 'array',
            'products_used' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceBooking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class);
    }
}
