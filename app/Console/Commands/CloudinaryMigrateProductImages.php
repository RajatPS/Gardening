<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CloudinaryMigrateProductImages extends Command
{
    protected $signature = 'cloudinary:migrate-product-images {--folder=products} {--cleanup-orphaned=false} {--dry-run=false}';
    protected $description = 'Upload local product images to Cloudinary and remove the local copies.';

    public function handle()
    {
        $folder = $this->option('folder');
        $dryRun = $this->option('dry-run') === 'true';
        $cleanupOrphaned = $this->option('cleanup-orphaned') === 'true';

        $query = ProductImage::query()
            ->where(function ($query) {
                $query->whereNull('public_id')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('path')
                            ->where(function ($q) {
                                $q->where('path', 'not like', 'http://%')
                                    ->where('path', 'not like', 'https://%');
                            });
                    });
            });

        $images = $query->orderBy('id')->get();

        if ($images->isEmpty()) {
            $this->info('No local product images found that need migration.');
        } else {
            $this->info('Found ' . $images->count() . ' product image(s) to inspect.');

            foreach ($images as $image) {
                $path = $image->path;

                if (empty($path)) {
                    $this->warn("Skipping image #{$image->id}: no path available.");
                    continue;
                }

                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $publicId = $image->getCloudinaryPublicId();
                    if ($publicId && empty($image->public_id)) {
                        if (! $dryRun) {
                            $image->update(['public_id' => $publicId]);
                        }
                        $this->info("Updated existing Cloudinary URL for image #{$image->id} with public_id.");
                        continue;
                    }

                    $this->info("Skipping image #{$image->id}: already stored as a URL.");
                    continue;
                }

                if (! Storage::disk('public')->exists($path)) {
                    $this->warn("Missing local file for image #{$image->id}: {$path}");
                    continue;
                }

                $localPath = Storage::disk('public')->path($path);

                if ($dryRun) {
                    $this->info("Dry run: would upload image #{$image->id} from {$path} to Cloudinary.");
                    continue;
                }

                try {
                    $this->info("Uploading image #{$image->id} from {$path} to Cloudinary...");

                    $uploadResult = cloudinary()->uploadApi()->upload($localPath, [
                        'folder' => $folder,
                        'resource_type' => 'image',
                    ]);

                    $image->update([
                        'path' => $uploadResult['secure_url'] ?? $uploadResult['url'] ?? $image->path,
                        'public_id' => $uploadResult['public_id'] ?? null,
                    ]);

                    Storage::disk('public')->delete($path);
                    $this->info("Migrated image #{$image->id} to Cloudinary and removed local file.");
                } catch (\Throwable $e) {
                    $this->error("Failed to migrate image #{$image->id}: {$e->getMessage()}");
                }
            }
        }

        if ($cleanupOrphaned) {
            $this->cleanupOrphanedLocalProductFiles($folder, $dryRun);
        }

        return 0;
    }

    private function cleanupOrphanedLocalProductFiles(string $folder, bool $dryRun): void
    {
        $this->info('Cleaning up orphaned local product image files...');

        $allFiles = Storage::disk('public')->allFiles($folder);
        $referencedPaths = ProductImage::query()
            ->whereNotNull('path')
            ->pluck('path')
            ->filter(function ($path) {
                return ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://');
            })
            ->values()
            ->all();

        foreach ($allFiles as $file) {
            if (! in_array($file, $referencedPaths, true)) {
                if ($dryRun) {
                    $this->info("Dry run: would delete orphaned local file {$file}.");
                    continue;
                }

                Storage::disk('public')->delete($file);
                $this->info("Deleted orphaned local file {$file}.");
            }
        }
    }
}
