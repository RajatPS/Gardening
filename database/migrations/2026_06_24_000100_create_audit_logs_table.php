<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action_type'); // e.g., 'User Created', 'Product Updated'
            $table->string('module'); // e.g., 'users', 'products', 'orders'
            $table->unsignedBigInteger('record_id')->nullable(); // ID of the affected record
            $table->longText('old_value')->nullable(); // JSON encoded old values
            $table->longText('new_value')->nullable(); // JSON encoded new values
            $table->string('ip_address')->nullable();
            $table->longText('device_info')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'module', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
