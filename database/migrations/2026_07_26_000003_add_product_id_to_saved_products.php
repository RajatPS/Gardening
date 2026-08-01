<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('session_id');
        });

        // Backfill product_id based on product_name matching
        DB::statement('
            UPDATE saved_products sp
            JOIN products p ON sp.product_name = p.name
            SET sp.product_id = p.id
        ');

        // Delete any orphan records that couldn't match a product
        DB::statement('DELETE FROM saved_products WHERE product_id IS NULL');

        Schema::table('saved_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unique(['user_id', 'product_id'], 'saved_products_user_product_id_unique');
            $table->dropUnique('saved_products_user_product_unique');
        });
    }

    public function down(): void
    {
        Schema::table('saved_products', function (Blueprint $table) {
            $table->dropUnique('saved_products_user_product_id_unique');
            $table->dropColumn('product_id');
            $table->unique(['user_id', 'product_name'], 'saved_products_user_product_unique');
        });
    }
};
