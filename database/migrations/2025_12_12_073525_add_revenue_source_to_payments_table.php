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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('revenue_source', [
                'student_fees_online',
                'student_fees_physical',
                'seminar_revenue',
                'pos_sales',
                'material_sales',
                'other'
            ])->default('student_fees_online')->after('payment_method');
            $table->string('source_reference')->nullable()->after('revenue_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['revenue_source', 'source_reference']);
        });
    }
};
