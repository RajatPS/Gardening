<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $specifications = is_array($this->specifications) ? $this->specifications : [];
        $careProfile = is_array($this->care_profile) ? $this->care_profile : [];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'type' => $this->type,
            'category' => $this->category,
            'sku' => $this->sku,
            'price' => (int) $this->price,
            'stock' => (int) $this->stock,
            'in_stock' => $this->stock > 0,
            'is_pet_safe' => $this->is_pet_safe,
            'description' => $this->description ?? ($specifications['description'] ?? null),
            'care' => $careProfile['care'] ?? $careProfile['watering'] ?? null,
            'image' => $this->image_url,
            'gallery' => $this->images
                ->map(fn ($image): ?string => $image->image_url)
                ->filter()
                ->values(),
        ];
    }
}