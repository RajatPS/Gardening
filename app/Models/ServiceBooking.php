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
        'address_line',
        'city',
        'customer_notes',
        'uploaded_images',
    ];

    protected $casts = [
        'preferred_at' => 'datetime',
        'uploaded_images' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
