<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_slots', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropIndex(['booking_id']);
            $table->dropColumn('booking_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('booking_slot_id')
                ->after('service_location_id')
                ->constrained('booking_slots')
                ->restrictOnDelete();

            $table->unique('booking_slot_id', 'uq_booking_slot_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('uq_booking_slot');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unique(
                ['service_location_id', 'starts_at'],
                'uq_booking_start'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique('uq_booking_slot_id');
            $table->dropForeign(['booking_slot_id']);
            $table->dropColumn('booking_slot_id');

            $table->unique(
                ['service_location_id', 'starts_at'],
                'uq_booking_slot'
            );
        });

        Schema::table('booking_slots', function (Blueprint $table) {
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();

            $table->index('booking_id');
        });
    }
};