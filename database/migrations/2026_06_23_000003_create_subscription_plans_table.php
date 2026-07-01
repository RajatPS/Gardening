<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('status');
            $table->unsignedInteger('monthly_price');
            $table->string('visit_cadence');
            $table->date('end_date');
            $table->json('features');
            $table->boolean('priority_support')->default(false);
            $table->boolean('emergency_assistance')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};