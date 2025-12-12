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
        Schema::table('expenses', function (Blueprint $table) {
            $table->date('approved_at')->nullable()->after('approved_by');
            $table->date('rejected_at')->nullable()->after('approved_at');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->decimal('budget_amount', 10, 2)->nullable()->after('amount');
            $table->string('vendor_name')->nullable()->after('notes');
            $table->string('invoice_number')->nullable()->after('vendor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn([
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'budget_amount',
                'vendor_name',
                'invoice_number'
            ]);
        });
    }
};
