<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'category',
        'sku',
        'quantity',
        'price',
        'stock',
        'specifications',
        'care_profile',
        'is_pet_safe',
        'is_active',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'care_profile' => 'array',
            'is_pet_safe' => 'boolean',
            'is_active' => 'boolean',
            'quantity' => 'integer',
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            if (empty($product->sku)) {
                $product->sku = self::generateUniqueSku($product->name ?? 'product');
            }
        });
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        $image = $this->images()->latest()->first();
        
        if ($image && $image->path) {
            return asset('storage/' . $image->path);
        }
        
        return null;
    }

    public static function generateUniqueSku(?string $name = 'product'): string
    {
        $prefix = strtoupper(Str::slug(substr($name ?? 'product', 0, 3)) ?: 'PRD');
        $date = now()->format('dmy');
        $random = random_int(1000, 9999);
        $sku = $prefix . '-' . $random . '-' . $date;

        while (self::where('sku', $sku)->exists()) {
            $random = random_int(1000, 9999);
            $sku = $prefix . '-' . $random . '-' . $date;
        }

        return $sku;
    }
}
