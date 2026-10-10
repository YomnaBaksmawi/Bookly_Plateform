<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_location_id')->constrained('service_locations')->onDelete('cascade');
            $table->date('date')->comment('The specific date for this slot');
            $table->time('start_time')->comment('Slot start time');
            $table->time('end_time')->comment('Slot end time');
            $table->enum('status', ['available', 'booked', 'blocked'])->default('available');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->timestamp('locked_at')->nullable()->comment('When this slot was locked');
            $table->timestamp('expires_at')->nullable()->comment('When the lock expires');
            $table->timestamps();

            // Unique constraint to prevent duplicate slots
            $table->unique(['service_location_id', 'date', 'start_time'], 'uq_slot_time');

            // Indexes for performance
            $table->index(['service_location_id', 'date', 'status'], 'idx_slots_available');
            $table->index('booking_id');
            $table->index('expires_at');
            $table->index(['date', 'end_time'], 'idx_slots_cleanup');
        });

        // Add check constraint
        DB::statement('ALTER TABLE booking_slots ADD CONSTRAINT chk_slot_time CHECK (end_time > start_time)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
    }
};
