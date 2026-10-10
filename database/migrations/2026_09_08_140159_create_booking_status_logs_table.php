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
        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->enum('from_status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->nullable();
            $table->enum('to_status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show']);
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('booking_id');
            $table->index('changed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status_logs');
    }
};
