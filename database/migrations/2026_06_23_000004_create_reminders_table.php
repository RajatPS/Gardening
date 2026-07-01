<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plant_name')->nullable();
            $table->string('reminder_type')->index();
            $table->dateTime('scheduled_at')->index();
            $table->string('repeat_rule')->nullable();
            $table->json('notification_channels')->nullable();
            $table->string('alarm_sound')->default('default');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
