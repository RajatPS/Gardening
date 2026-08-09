<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentStaff extends Model
{
    protected $table = 'appointment_staff';

    protected $fillable = [
        'appointment_id',
        'staff_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class, 'appointment_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
