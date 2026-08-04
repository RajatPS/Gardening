<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuditImages extends Command
{
    protected $signature = 'audit:images {--delete-missing=false} {--limit=0}';
    protected $description = 'Audit product_images rows and report missing files on the public disk';

    public function handle()
    {
        $delete = $this->option('delete-missing') === 'true';
        $limit = (int) $this->option('limit');

        $query = DB::table('product_images')->select(['id','product_id','path','is_primary','sort_order']);
        if ($limit > 0) $query->limit($limit);

        $rows = $query->orderBy('id')->get();
        $missing = [];

        foreach ($rows as $row) {
            if (! Storage::disk('public')->exists($row->path)) {
                $missing[] = (array) $row;
            }
        }

        if (empty($missing)) {
            $this->info('No missing image files found for product_images rows.');
            return 0;
        }

        $this->info('Missing image files (product_images rows with no file on disk):');
        $this->table(['id','product_id','path','is_primary','sort_order'], $missing);

        if ($delete) {
            if (! $this->confirm('Delete these product_images rows from database? This cannot be undone.')) {
                $this->info('Aborted deletion.');
                return 0;
            }

            $ids = array_column($missing, 'id');
            DB::table('product_images')->whereIn('id', $ids)->delete();
            $this->info('Deleted ' . count($ids) . ' rows.');
        }

        return 0;
    }
}
