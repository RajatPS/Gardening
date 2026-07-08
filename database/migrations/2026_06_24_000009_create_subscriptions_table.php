<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('plan_name')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable()->index();
            $table->unsignedInteger('amount');
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('renewal_count')->default(0);
            $table->string('payment_gateway')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

