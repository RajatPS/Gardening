<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('transactions', 'subscription_id')) {
                $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('cascade')->after('user_id');
            }
            if (! Schema::hasColumn('transactions', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('transactions', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('payment_details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'payment_gateway', 'gateway_response']);
        });
    }
};
