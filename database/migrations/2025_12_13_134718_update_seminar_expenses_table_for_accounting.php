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
        Schema::table('seminar_expenses', function (Blueprint $table) {
            // Add approval workflow fields
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('notes');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->foreignId('created_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();

            // Add payment details
            $table->date('expense_date')->after('amount');
            $table->string('payment_method', 50)->nullable()->after('expense_date');
            $table->string('reference_number', 100)->nullable()->after('payment_method');
            $table->text('notes')->nullable()->after('receipt_path');

            // Add timestamps if not exist
            if (!Schema::hasColumn('seminar_expenses', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_expenses', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_reason',
                'created_by',
                'expense_date',
                'payment_method',
                'reference_number',
                'notes',
            ]);
        });
    }
};
