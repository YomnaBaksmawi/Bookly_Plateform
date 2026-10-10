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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('restrict');
            $table->decimal('amount', 10, 2)->unsigned();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('provider', ['mock', 'stripe', 'paypal'])->default('mock');
            $table->string('transaction_id')->nullable();
            $table->string('idempotency_key')->unique();
            $table->json('payload')->nullable()->comment('Raw gateway response for debugging');
            $table->timestamps();

            // Indexes
            $table->index('booking_id');
            $table->index('status');
            $table->index('transaction_id');
            $table->index('idempotency_key');
        });

        // Add check constraint using raw SQL
        DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_amount CHECK (amount >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
