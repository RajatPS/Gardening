<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id')->nullable()->after('session_id');
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        $products = DB::table('products')->select('id', 'name')->get();
        foreach ($products as $product) {
            DB::table('cart_items')
                ->whereNull('product_id')
                ->where('product_name', $product->name)
                ->update(['product_id' => $product->id]);
        }
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
