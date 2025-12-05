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
        Schema::create('low_attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->decimal('attendance_percentage', 5, 2);
            $table->decimal('threshold', 5, 2)->default(75.00);
            $table->text('alert_message')->nullable();
            $table->enum('status', ['pending', 'sent', 'acknowledged', 'escalated'])->default('pending');
            $table->foreignId('notified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('notified_at')->nullable();
            $table->text('parent_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['student_id', 'class_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('low_attendance_alerts');
    }
};
