<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['confirmed', 'cancelled', 'completed'])->default('confirmed');
            $table->integer('total_duration_minutes');
            $table->timestamps();

            $table->unique([
                'service_id',
                'booking_date',
                'start_time',
                'status'
            ], 'unique_active_booking');
            $table->index(['service_id', 'booking_date', 'status']);
            $table->index(['booking_date', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
