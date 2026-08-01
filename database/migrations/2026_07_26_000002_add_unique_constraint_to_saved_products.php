<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any existing duplicate saved products before adding unique constraints
        // Keep only the most recent save per user+product_name and session+product_name
        \Illuminate\Support\Facades\DB::statement('
            DELETE sp1 FROM saved_products sp1
            INNER JOIN saved_products sp2
            ON sp1.user_id = sp2.user_id
            AND sp1.user_id IS NOT NULL
            AND sp1.product_name = sp2.product_name
            AND sp1.id < sp2.id
        ');

        \Illuminate\Support\Facades\DB::statement('
            DELETE sp1 FROM saved_products sp1
            INNER JOIN saved_products sp2
            ON sp1.session_id = sp2.session_id
            AND sp1.session_id IS NOT NULL
            AND sp1.user_id IS NULL
            AND sp2.user_id IS NULL
            AND sp1.product_name = sp2.product_name
            AND sp1.id < sp2.id
        ');

        Schema::table('saved_products', function (Blueprint $table): void {
            // Unique per authenticated user + product name
            $table->unique(['user_id', 'product_name'], 'saved_products_user_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('saved_products', function (Blueprint $table): void {
            $table->dropUnique('saved_products_user_product_unique');
        });
    }
};
