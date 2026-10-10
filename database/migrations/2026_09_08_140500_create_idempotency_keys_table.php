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
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Client-provided idempotency key');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('request_path', 500)->comment('API endpoint path');
            $table->integer('response_code')->comment('HTTP response status code');
            $table->json('response_body')->comment('Cached API response');
            $table->timestamp('expires_at')->comment('When this key expires');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('user_id');
            $table->index('expires_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
