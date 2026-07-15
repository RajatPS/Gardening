<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            if (! Schema::hasColumn('subscriptions', 'plan_name')) {
                $table->string('plan_name')->nullable()->after('subscription_plan_id');
            }
            if (! Schema::hasColumn('subscriptions', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('renewal_count');
            }
            if (! Schema::hasColumn('subscriptions', 'start_date')) {
                $table->dateTime('start_date')->nullable();
            }
            if (! Schema::hasColumn('subscriptions', 'end_date')) {
                $table->dateTime('end_date')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropColumn(['plan_name', 'payment_gateway']);
        });
    }
};
