<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(ServiceBooking::class, 'branch_id');
    }
}
