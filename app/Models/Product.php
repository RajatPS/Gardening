<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'category',
        'sku',
        'price',
        'stock',
        'specifications',
        'care_profile',
        'is_pet_safe',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'care_profile' => 'array',
            'is_pet_safe' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
