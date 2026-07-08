<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscription_plans', 'status')) {
                $table->string('status')->default('active')->after('name');
            } else {
                $table->string('status')->default('active')->change();
            }
        });

        DB::statement('ALTER TABLE subscription_plans MODIFY end_date DATE NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE subscription_plans MODIFY end_date DATE NOT NULL');
    }
};
