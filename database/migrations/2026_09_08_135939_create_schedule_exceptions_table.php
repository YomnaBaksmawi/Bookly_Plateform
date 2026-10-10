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
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['holiday', 'custom_hours']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate exceptions for same location and date
            $table->unique(['location_id', 'date'], 'uq_schedule_exception');

            // Indexes
            $table->index('location_id');
            $table->index('date');
        });

        // Add check constraint using raw SQL
        DB::statement("
            ALTER TABLE schedule_exceptions 
            ADD CONSTRAINT chk_custom_hours 
            CHECK (
                (type = 'holiday') OR 
                (type = 'custom_hours' AND start_time IS NOT NULL AND end_time IS NOT NULL AND end_time > start_time)
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
