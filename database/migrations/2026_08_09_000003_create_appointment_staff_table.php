<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_id')->constrained('service_bookings')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['appointment_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_staff');
    }
};
