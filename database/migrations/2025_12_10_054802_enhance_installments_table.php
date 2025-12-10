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
        Schema::table('installments', function (Blueprint $table) {
            // Additional tracking fields
            if (!Schema::hasColumn('installments', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('installments', 'reminder_count')) {
                $table->unsignedTinyInteger('reminder_count')->default(0)->after('notes');
            }
            if (!Schema::hasColumn('installments', 'last_reminder_at')) {
                $table->timestamp('last_reminder_at')->nullable()->after('reminder_count');
            }
            if (!Schema::hasColumn('installments', 'payment_id')) {
                $table->foreignId('payment_id')->nullable()->after('invoice_id')
                    ->constrained('payments')->nullOnDelete();
            }
            if (!Schema::hasColumn('installments', 'grace_period_days')) {
                $table->unsignedTinyInteger('grace_period_days')->default(3)->after('due_date');
            }
            if (!Schema::hasColumn('installments', 'late_fee')) {
                $table->decimal('late_fee', 10, 2)->default(0)->after('paid_amount');
            }
            if (!Schema::hasColumn('installments', 'waived_fee')) {
                $table->decimal('waived_fee', 10, 2)->default(0)->after('late_fee');
            }
        });

        // Add indexes for better performance
        Schema::table('installments', function (Blueprint $table) {
            $table->index(['status', 'due_date'], 'installments_status_due_date_index');
            $table->index(['invoice_id', 'installment_number'], 'installments_invoice_number_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropIndex('installments_status_due_date_index');
            $table->dropIndex('installments_invoice_number_index');

            $table->dropColumn([
                'notes',
                'reminder_count',
                'last_reminder_at',
                'payment_id',
                'grace_period_days',
                'late_fee',
                'waived_fee',
            ]);
        });
    }
};
