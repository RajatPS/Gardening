<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_disease_reports')) {
            Schema::create('ai_disease_reports', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('plant_name');
                $table->string('disease_name');
                $table->string('medicine_name');
                $table->text('medicine_instructions');
                $table->string('treatment_frequency');
                $table->dateTime('next_reminder_at')->nullable();
                $table->dateTime('treatment_end_date')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('scheduled_notifications')) {
            Schema::create('scheduled_notifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->text('message');
                $table->string('notification_type');
                $table->dateTime('scheduled_at')->index();
                $table->boolean('is_sent')->default(false);
                $table->dateTime('sent_at')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'notification_type', 'scheduled_at'], 'sched_notif_user_type_at_idx');
                $table->index(['is_sent', 'scheduled_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_notifications');
        Schema::dropIfExists('ai_disease_reports');
        Schema::dropIfExists('notifications');
    }
};
