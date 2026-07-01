<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reminders')) {
            Schema::table('reminders', function (Blueprint $table) {
                if (!Schema::hasColumn('reminders', 'type')) {
                    $table->string('type')->nullable();
                }
                if (!Schema::hasColumn('reminders', 'payload')) {
                    $table->json('payload')->nullable();
                }
                if (!Schema::hasColumn('reminders', 'notified')) {
                    $table->boolean('notified')->default(false);
                }
                if (!Schema::hasColumn('reminders', 'remind_at')) {
                    $table->dateTime('remind_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reminders')) {
            Schema::table('reminders', function (Blueprint $table) {
                if (Schema::hasColumn('reminders', 'payload')) {
                    $table->dropColumn('payload');
                }
                if (Schema::hasColumn('reminders', 'notified')) {
                    $table->dropColumn('notified');
                }
                // do not drop remind_at to avoid removing core data
            });
        }
    }
};
