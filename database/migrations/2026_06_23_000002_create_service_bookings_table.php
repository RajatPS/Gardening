<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type')->index();
            $table->string('status')->default('requested')->index();
            $table->dateTime('preferred_at')->nullable();
            $table->string('address_line')->nullable();
            $table->string('city')->nullable();
            $table->text('customer_notes')->nullable();
            $table->json('uploaded_images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
