<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table): void {
            if (! Schema::hasColumn('service_bookings', 'pin_code')) {
                $table->string('pin_code', 6)->nullable()->after('city');
            }

            if (! Schema::hasColumn('service_bookings', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('pin_code');
            }

            if (! Schema::hasColumn('service_bookings', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table): void {
            if (Schema::hasColumn('service_bookings', 'longitude')) {
                $table->dropColumn('longitude');
            }

            if (Schema::hasColumn('service_bookings', 'latitude')) {
                $table->dropColumn('latitude');
            }

            if (Schema::hasColumn('service_bookings', 'pin_code')) {
                $table->dropColumn('pin_code');
            }
        });
    }
};
