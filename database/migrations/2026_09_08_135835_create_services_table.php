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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration')->unsigned()->comment('Duration in minutes');
            $table->decimal('price', 10, 2)->unsigned();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('provider_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('price');
            $table->index('deleted_at');
        });

        // Add check constraints using raw SQL
        DB::statement('ALTER TABLE services ADD CONSTRAINT chk_services_duration CHECK (duration > 0)');
        DB::statement('ALTER TABLE services ADD CONSTRAINT chk_services_price CHECK (price >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
