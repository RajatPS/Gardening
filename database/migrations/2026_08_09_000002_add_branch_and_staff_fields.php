<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('city')->constrained('branches')->restrictOnDelete();
            $table->json('capabilities')->nullable()->after('branch_id');
            $table->string('current_duty')->nullable()->after('capabilities');
        });

        Schema::table('service_bookings', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('city')->constrained('branches')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn(['capabilities', 'current_duty']);
        });
    }
};
