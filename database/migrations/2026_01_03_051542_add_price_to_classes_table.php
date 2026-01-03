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
        Schema::table('classes', function (Blueprint $table) {
            // Individual class price - nullable for backward compatibility
            // Existing classes without price will continue to work
            $table->decimal('price', 10, 2)->default(0.00)->after('capacity')
                  ->comment('Individual class price (required)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
