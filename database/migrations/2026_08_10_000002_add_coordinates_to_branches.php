<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            if (! Schema::hasColumn('branches', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('name');
            }

            if (! Schema::hasColumn('branches', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table): void {
            if (Schema::hasColumn('branches', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('branches', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
