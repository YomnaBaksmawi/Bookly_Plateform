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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action')->comment('Action performed (create, update, delete, etc.)');
            $table->string('auditable_type', 100)->comment('Model class name');
            $table->unsignedBigInteger('auditable_id')->comment('Model record ID');
            $table->json('old_values')->nullable()->comment('Before values');
            $table->json('new_values')->nullable()->comment('After values');
            $table->string('ip_address', 45)->nullable()->comment('Client IP address');
            $table->text('user_agent')->nullable()->comment('Client user agent');
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('action');
            $table->index(['auditable_type', 'auditable_id'], 'idx_audit_logs_auditable');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
