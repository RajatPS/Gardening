<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action_type',
        'module',
        'record_id',
        'old_value',
        'new_value',
        'ip_address',
        'device_info',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
