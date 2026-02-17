<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display student's enrolled classes.
     */
    public function index()
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Get active enrollments with class details
        $enrollments = $student->enrollments()
            ->where('status', 'active')
            ->with(['class.teacher.user', 'class.subject', 'package'])
            ->latest()
            ->get();

        // Group by class for display
        $classes = $enrollments->pluck('class')->unique('id');

        return view('student.classes.index', compact('classes', 'enrollments', 'student'));
    }

    /**
     * Show specific class details.
     */
    public function show($id)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Verify student is enrolled in this class
        $enrollment = $student->enrollments()
            ->where('class_id', $id)
            ->with(['class.teacher.user', 'class.subject', 'package'])
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }

        $class = $enrollment->class;

        // Get classmates
        $classmates = $class->enrollments()
            ->where('status', 'active')
            ->where('student_id', '!=', $student->id)
            ->with('student.user')
            ->get();

        // Get recent sessions
        $recentSessions = $class->sessions()
            ->with('attendances')
            ->latest('session_date')
            ->take(10)
            ->get();

        return view('student.classes.show', compact('class', 'enrollment', 'classmates', 'recentSessions', 'student'));
    }
}
