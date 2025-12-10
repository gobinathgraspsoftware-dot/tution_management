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
        Schema::table('payment_reminders', function (Blueprint $table) {
            // Add timestamps if not exists
            if (!Schema::hasColumn('payment_reminders', 'created_at')) {
                $table->timestamps();
            }

            // Add student_id for direct reference
            if (!Schema::hasColumn('payment_reminders', 'student_id')) {
                $table->foreignId('student_id')->nullable()->after('invoice_id')
                    ->constrained('students')->nullOnDelete();
            }

            // Add installment reference
            if (!Schema::hasColumn('payment_reminders', 'installment_id')) {
                $table->foreignId('installment_id')->nullable()->after('student_id')
                    ->constrained('installments')->nullOnDelete();
            }

            // Add reminder day configuration
            if (!Schema::hasColumn('payment_reminders', 'reminder_day')) {
                $table->unsignedTinyInteger('reminder_day')->nullable()->after('reminder_type');
            }

            // Recipient contact info
            if (!Schema::hasColumn('payment_reminders', 'recipient_phone')) {
                $table->string('recipient_phone', 20)->nullable()->after('channel');
            }
            if (!Schema::hasColumn('payment_reminders', 'recipient_email')) {
                $table->string('recipient_email')->nullable()->after('recipient_phone');
            }

            // Message content
            if (!Schema::hasColumn('payment_reminders', 'message_content')) {
                $table->text('message_content')->nullable()->after('recipient_email');
            }

            // Retry mechanism
            if (!Schema::hasColumn('payment_reminders', 'attempts')) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('status');
            }
            if (!Schema::hasColumn('payment_reminders', 'max_attempts')) {
                $table->unsignedTinyInteger('max_attempts')->default(3)->after('attempts');
            }
            if (!Schema::hasColumn('payment_reminders', 'next_retry_at')) {
                $table->timestamp('next_retry_at')->nullable()->after('max_attempts');
            }

            // Error tracking
            if (!Schema::hasColumn('payment_reminders', 'error_message')) {
                $table->text('error_message')->nullable()->after('response');
            }

            // Created by tracking
            if (!Schema::hasColumn('payment_reminders', 'created_by')) {
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
        });

        // Add indexes for better performance
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->index(['status', 'scheduled_date'], 'reminders_status_scheduled_index');
            $table->index(['invoice_id', 'reminder_type'], 'reminders_invoice_type_index');
            $table->index(['channel', 'status'], 'reminders_channel_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->dropIndex('reminders_status_scheduled_index');
            $table->dropIndex('reminders_invoice_type_index');
            $table->dropIndex('reminders_channel_status_index');

            $table->dropColumn([
                'student_id',
                'installment_id',
                'reminder_day',
                'recipient_phone',
                'recipient_email',
                'message_content',
                'attempts',
                'max_attempts',
                'next_retry_at',
                'error_message',
                'created_by',
            ]);
        });
    }
};
