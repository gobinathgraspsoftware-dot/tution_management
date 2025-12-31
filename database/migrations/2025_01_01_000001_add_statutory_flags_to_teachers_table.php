<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds enable/disable flags for EPF, SOCSO, and SOCSO Insurance
     * to the teachers table for per-teacher statutory contribution control.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // EPF enable flag - default true for backward compatibility
            $table->boolean('epf_enabled')->default(true)->after('socso_number');
            
            // SOCSO enable flag - default true for backward compatibility
            $table->boolean('socso_enabled')->default(true)->after('epf_enabled');
            
            // SOCSO type: 'regular' for standard SOCSO, 'insurance_only' for employees 60+ or foreign workers
            $table->enum('socso_type', ['regular', 'insurance_only'])->default('regular')->after('socso_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['epf_enabled', 'socso_enabled', 'socso_type']);
        });
    }
};
