<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table): void {
            $table->dateTime('booking_date')->nullable()->after('status');
            $table->string('time_slot')->nullable()->after('booking_date');
            $table->foreignId('assigned_staff_id')->nullable()->after('time_slot')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('assigned_staff_id');
            $table->dropColumn(['booking_date', 'time_slot']);
        });
    }
};

