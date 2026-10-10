<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('notifications', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn(['id', 'user_id']);

        $table->uuid('id')->primary();
        $table->string('notifiable_type');
        $table->unsignedBigInteger('notifiable_id');

        $table->index(['notifiable_type', 'notifiable_id']);
    });
}

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            $table->dropColumn(['notifiable_type', 'notifiable_id']);

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
    }
};