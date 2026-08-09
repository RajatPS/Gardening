<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'staff_id')) {
                $table->string('staff_id')->nullable()->after('branch_id')->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'staff_id')) {
                $table->dropUnique(['staff_id']);
                $table->dropColumn('staff_id');
            }
        });
    }
};
