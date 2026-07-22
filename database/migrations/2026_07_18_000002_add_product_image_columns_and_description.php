<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'description')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->text('description')->nullable()->after('stock');
            });
        }

        if (Schema::hasTable('product_images')) {
            if (!Schema::hasColumn('product_images', 'is_primary')) {
                Schema::table('product_images', function (Blueprint $table): void {
                    $table->boolean('is_primary')->default(false)->after('path');
                });
            }

            if (!Schema::hasColumn('product_images', 'sort_order')) {
                Schema::table('product_images', function (Blueprint $table): void {
                    $table->unsignedInteger('sort_order')->default(0)->after('is_primary');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'description')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('description');
            });
        }

        if (Schema::hasTable('product_images')) {
            if (Schema::hasColumn('product_images', 'is_primary')) {
                Schema::table('product_images', function (Blueprint $table): void {
                    $table->dropColumn('is_primary');
                });
            }

            if (Schema::hasColumn('product_images', 'sort_order')) {
                Schema::table('product_images', function (Blueprint $table): void {
                    $table->dropColumn('sort_order');
                });
            }
        }
    }
};
