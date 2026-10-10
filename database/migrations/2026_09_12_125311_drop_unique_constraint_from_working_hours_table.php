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
        
Schema::table('working_hours', function (Blueprint $table) {
    // $table->dropUnique('uq_working_hours');
});        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_hours', function (Blueprint $table) {
    $table->unique(['location_id', 'day_of_week'], 'uq_working_hours');

        });
    }
};
