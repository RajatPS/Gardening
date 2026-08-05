<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'public_id',
        'is_primary',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $image): void {
            $image->deleteCloudinaryResource();

            if (! str_starts_with($image->path, 'http://') && ! str_starts_with($image->path, 'https://')) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->path)) {
            return null;
        }

        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        if (! empty($this->public_id)) {
            return cloudinary()->image($this->public_id)->toUrl();
        }

        if (Storage::disk('public')->exists($this->path)) {
            return Storage::url($this->path);
        }

        return null;
    }

    public function getCloudinaryPublicId(): ?string
    {
        if (! empty($this->public_id)) {
            return $this->public_id;
        }

        if (empty($this->path) || ! str_starts_with($this->path, 'http://') && ! str_starts_with($this->path, 'https://')) {
            return null;
        }

        return $this->extractPublicIdFromUrl($this->path);
    }

    public function deleteCloudinaryResource(): void
    {
        $publicId = $this->getCloudinaryPublicId();

        if (empty($publicId)) {
            return;
        }

        try {
            cloudinary()->adminApi()->deleteAssets([$publicId]);
        } catch (\Throwable $e) {
            Log::warning('Unable to delete Cloudinary image', [
                'public_id' => $publicId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function extractPublicIdFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        if (empty($parsed['path'])) {
            return null;
        }

        $segments = explode('/', ltrim($parsed['path'], '/'));
        $uploadIndex = array_search('upload', $segments, true);
        if ($uploadIndex === false) {
            return null;
        }

        $publicSegments = array_slice($segments, $uploadIndex + 1);
        if (empty($publicSegments)) {
            return null;
        }

        if (preg_match('/^v\d+$/', $publicSegments[0])) {
            array_shift($publicSegments);
        }

        $publicId = implode('/', $publicSegments);

        return preg_replace('/\.[^.]+$/', '', $publicId);
    }
}
