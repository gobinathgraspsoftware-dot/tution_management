<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds indexes to improve performance for session-less attendance queries
     * and ensures efficient retrieval of attendance records by class and date.
     */
    public function up(): void
    {
        // Add composite index for efficient class + date queries
        Schema::table('class_sessions', function (Blueprint $table) {
            // Check if index doesn't exist before adding
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('class_sessions');

            if (!array_key_exists('idx_class_sessions_class_date', $indexesFound)) {
                $table->index(['class_id', 'session_date'], 'idx_class_sessions_class_date');
            }
        });

        // Add index for attendance retrieval by student and date range
        Schema::table('student_attendance', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesFound = $sm->listTableIndexes('student_attendance');

            if (!array_key_exists('idx_student_attendance_student', $indexesFound)) {
                $table->index(['student_id', 'created_at'], 'idx_student_attendance_student');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_class_sessions_class_date');
        });

        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropIndex('idx_student_attendance_student');
        });
    }
};
