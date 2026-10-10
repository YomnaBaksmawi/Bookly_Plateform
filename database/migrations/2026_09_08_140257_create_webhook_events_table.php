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
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique()->comment('Unique identifier from webhook provider');
            $table->string('provider', 100)->comment('Payment gateway provider name');
            $table->string('event_type', 100)->comment('Type of webhook event');
            $table->json('payload')->comment('Full webhook payload');
            $table->timestamp('processed_at')->nullable()->comment('When the webhook was processed');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('event_id');
            $table->index('provider');
            $table->index('processed_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
