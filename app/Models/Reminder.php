<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Reminder extends Model
{
    protected $fillable = ['user_id', 'task', 'remind_at', 'type', 'payload', 'notified'];

    protected $casts = [
        'remind_at' => 'datetime',
        'payload' => 'array',
        'notified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
