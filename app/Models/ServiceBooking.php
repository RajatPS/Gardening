<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ServiceBooking extends Model
{
    protected $fillable = [
        'user_id',
        'service_type',
        'status',
        'preferred_at',
        'booking_date',
        'time_slot',
        'assigned_staff_id',
        'address_line',
        'city',
        'customer_notes',
        'uploaded_images',
    ];

    protected $casts = [
        'preferred_at' => 'datetime',
        'booking_date' => 'datetime',
        'uploaded_images' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }
}
