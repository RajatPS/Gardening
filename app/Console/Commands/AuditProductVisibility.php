<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class AuditProductVisibility extends Command
{
    protected $signature = 'audit:products-visibility {--days=7}';
    protected $description = 'Audit recently created products and explain why they may not appear on the homepage';

    public function handle()
    {
        $days = (int) $this->option('days');
        $since = now()->subDays($days);

        $products = Product::where('created_at', '>=', $since)->orderBy('created_at','desc')->get();

        if ($products->isEmpty()) {
            $this->info('No products created in the last ' . $days . ' days.');
            return 0;
        }

        $rows = [];
        foreach ($products as $p) {
            $reasons = [];
            if (! $p->is_active) $reasons[] = 'is_active=false';
            if (empty($p->name)) $reasons[] = 'name empty';
            if ($p->trashed ?? false) $reasons[] = 'soft-deleted';

            // check images
            $images = $p->images()->get();
            $missingFiles = 0;
            foreach ($images as $img) {
                if (! Storage::disk('public')->exists($img->path)) $missingFiles++;
            }

            if ($images->count() === 0) $reasons[] = 'no images';
            elseif ($missingFiles === $images->count()) $reasons[] = 'all image files missing';

            $visible = true;
            if ($p->is_active === false || empty($p->name)) $visible = false;

            $rows[] = [
                'id' => $p->id,
                'name' => $p->name,
                'is_active' => $p->is_active ? '1' : '0',
                'images' => $images->count(),
                'missing_files' => $missingFiles,
                'visible_by_rules' => $visible ? 'yes' : 'no',
                'reasons' => implode(', ', $reasons),
            ];
        }

        $this->table(['id','name','is_active','images','missing_files','visible_by_rules','reasons'], $rows);

        return 0;
    }
}
