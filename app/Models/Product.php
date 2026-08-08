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
        'price',
        'stock',
        'description',
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

        static::deleting(function (self $product): void {
            $product->load('images');

            foreach ($product->images as $image) {
                $image->delete();
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->primaryImage?->image_url ?? $this->images->first()?->image_url;
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orderBy('sort_order')->orderBy('id');
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
