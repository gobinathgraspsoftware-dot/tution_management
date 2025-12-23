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
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Add phone column
            $table->string('phone')->nullable()->after('email');

            // Make email nullable since we can use phone instead
            $table->string('email')->nullable()->change();

            // Add composite index for better performance
            $table->index(['phone', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropIndex(['phone', 'created_at']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
