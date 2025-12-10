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
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_installment')->default(false)->after('status');
            $table->tinyInteger('installment_count')->default(0)->after('is_installment');
            $table->text('installment_notes')->nullable()->after('installment_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['is_installment', 'installment_count', 'installment_notes']);
        });
    }
};
