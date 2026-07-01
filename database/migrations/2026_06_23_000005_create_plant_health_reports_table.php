<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plant_health_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plant_name');
            $table->string('health_status')->index();
            $table->json('before_images')->nullable();
            $table->json('after_images')->nullable();
            $table->json('treatments_applied')->nullable();
            $table->json('products_used')->nullable();
            $table->text('expert_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_health_reports');
    }
};
