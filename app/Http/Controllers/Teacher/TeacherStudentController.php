<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\StudentAttendance;
use App\Models\ExamResult;
use Illuminate\Http\Request;

class TeacherStudentController extends Controller
{
    /**
     * Display students in teacher's classes.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        // Get all students enrolled in teacher's classes
        $query = Student::whereHas('enrollments', function ($q) use ($teacher) {
            $q->whereHas('class', function ($classQuery) use ($teacher) {
                $classQuery->where('teacher_id', $teacher->id);
            })->where('status', 'active');
        })->with(['user', 'parent.user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('student_id', 'like', "%{$search}%");
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        // Get teacher's classes for filter
        $classes = $teacher->classes()->active()->get();

        return view('teacher.students.index', compact('students', 'classes'));
    }

    /**
     * Display student details.
     */
    public function show(Student $student)
    {
        $teacher = auth()->user()->teacher;

        // Verify student is enrolled in one of teacher's classes
        $isEnrolled = $student->enrollments()
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'You are not authorized to view this student.');
        }

        $student->load(['user', 'parent.user']);

        // Get enrollments in teacher's classes
        $enrollments = $student->enrollments()
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['class.subject', 'package'])
            ->get();

        // Get attendance records for teacher's classes
        $attendanceRecords = StudentAttendance::where('student_id', $student->id)
            ->whereHas('classSession.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with('classSession.class')
            ->latest()
            ->take(20)
            ->get();

        // Calculate attendance statistics
        $attendanceStats = $this->calculateAttendanceStats($student, $teacher);

        // Get exam results for teacher's classes
        $examResults = ExamResult::where('student_id', $student->id)
            ->whereHas('exam.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['exam.class', 'exam.subject'])
            ->latest()
            ->take(10)
            ->get();

        return view('teacher.students.show', compact(
            'student',
            'enrollments',
            'attendanceRecords',
            'attendanceStats',
            'examResults'
        ));
    }

    /**
     * Calculate attendance statistics for a student.
     */
    private function calculateAttendanceStats(Student $student, $teacher)
    {
        $records = StudentAttendance::where('student_id', $student->id)
            ->whereHas('classSession.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->get();

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];
    }

    /**
     * View student attendance history.
     */
    public function attendance(Student $student, Request $request)
    {
        $teacher = auth()->user()->teacher;

        // Verify student is enrolled in one of teacher's classes
        $isEnrolled = $student->enrollments()
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Unauthorized access.');
        }

        $query = StudentAttendance::where('student_id', $student->id)
            ->whereHas('classSession.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with('classSession.class');

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('classSession', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereHas('classSession', function ($q) use ($request) {
                $q->whereDate('session_date', '>=', $request->from_date);
            });
        }
        if ($request->filled('to_date')) {
            $query->whereHas('classSession', function ($q) use ($request) {
                $q->whereDate('session_date', '<=', $request->to_date);
            });
        }

        $attendanceRecords = $query->latest()->paginate(20)->withQueryString();

        // Get teacher's classes for filter
        $classes = $teacher->classes()->active()->get();

        return view('teacher.students.attendance', compact('student', 'attendanceRecords', 'classes'));
    }

    /**
     * View student results.
     */
    public function results(Student $student)
    {
        $teacher = auth()->user()->teacher;

        // Verify student is enrolled in one of teacher's classes
        $isEnrolled = $student->enrollments()
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Unauthorized access.');
        }

        $examResults = ExamResult::where('student_id', $student->id)
            ->whereHas('exam.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['exam.class', 'exam.subject'])
            ->latest()
            ->paginate(15);

        return view('teacher.students.results', compact('student', 'examResults'));
    }
}
