<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('payment_gateway')->nullable();
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending');
            $table->json('payment_details')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
