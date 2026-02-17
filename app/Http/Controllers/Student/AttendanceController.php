<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display student's attendance records.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Build query
        $query = StudentAttendance::where('student_id', $student->id)
            ->with(['classSession.class.subject', 'classSession.class.teacher.user']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('classSession', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->subMonths(3);

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now();

        $query->whereHas('classSession', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('session_date', [$dateFrom, $dateTo]);
        });

        // Get attendance records
        $attendanceRecords = $query->latest()->paginate(20)->withQueryString();

        // Get classes for filter
        $classes = $student->enrollments()
            ->where('status', 'active')
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id');

        // Calculate statistics
        $stats = $this->calculateStats($student, $dateFrom, $dateTo);

        return view('student.attendance.index', compact(
            'attendanceRecords',
            'classes',
            'stats',
            'dateFrom',
            'dateTo',
            'student'
        ));
    }

    /**
     * Calculate attendance statistics.
     */
    private function calculateStats($student, $dateFrom, $dateTo)
    {
        $records = StudentAttendance::where('student_id', $student->id)
            ->whereHas('classSession', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('session_date', [$dateFrom, $dateTo]);
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
}
