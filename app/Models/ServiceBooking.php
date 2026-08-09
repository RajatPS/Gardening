<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Branch;
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
        'branch_id',
        'address_line',
        'city',
        'pin_code',
        'latitude',
        'longitude',
        'customer_notes',
        'uploaded_images',
    ];

    protected $casts = [
        'preferred_at' => 'datetime',
        'booking_date' => 'datetime',
        'uploaded_images' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function assignedStaff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'appointment_staff', 'appointment_id', 'staff_id')
            ->withPivot(['assigned_at'])
            ->withTimestamps();
    }
}
