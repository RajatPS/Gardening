<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDiseaseReport extends Model
{
    protected $fillable = [
        'user_id',
        'plant_name',
        'disease_name',
        'medicine_name',
        'medicine_instructions',
        'treatment_frequency',
        'next_reminder_at',
        'treatment_end_date',
        'status',
    ];

    protected $casts = [
        'next_reminder_at' => 'datetime',
        'treatment_end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
