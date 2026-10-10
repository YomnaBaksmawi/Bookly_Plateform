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
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('country', 100);
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->timestamps();

            // Unique constraint to prevent same holiday being added twice for the same country/location
            $table->unique(['date', 'country', 'location_id'], 'uq_public_holiday');

            // Indexes
            $table->index('date');
            $table->index('country');
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};
