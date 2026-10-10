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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('service_location_id')->constrained('service_locations')->onDelete('restrict');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->decimal('price_at_booking', 10, 2)->unsigned()->comment('Price snapshot at booking time');
            $table->string('idempotency_key')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('customer_id');
            $table->index('service_location_id');
            $table->index('status');
            $table->index('starts_at');
            $table->index('idempotency_key');
            $table->index('deleted_at');

            // Unique constraint to prevent double booking: same service+location, same start time
            $table->unique(['service_location_id', 'starts_at'], 'uq_booking_slot');
        });

        // Add check constraints using raw SQL
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_booking_time CHECK (ends_at > starts_at)');
        DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_booking_price CHECK (price_at_booking >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
