<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\LowAttendanceAlert;
use App\Models\Notification;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display parent attendance dashboard with all children
     */
    public function index()
    {
        $parent = auth()->user()->parent;
        
        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        // Get all children
        $children = Student::with(['user', 'enrollments.class.subject'])
            ->where('parent_id', $parent->id)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        // Get attendance stats for each child
        $childrenStats = [];
        $recentAttendance = [];
        $totalPresent = 0;
        $totalAbsent = 0;

        foreach ($children as $child) {
            $stats = $this->attendanceService->getStudentMonthlyStats($child->id);
            $childrenStats[$child->id] = $stats;
            $totalPresent += $stats['present'];
            $totalAbsent += $stats['absent'];
            
            // Get recent attendance for each child
            $recentAttendance[$child->id] = $this->attendanceService->getRecentAttendance($child->id, 7);
        }

        // Calculate overall attendance percentage
        $overallAttendance = $this->attendanceService->getParentOverallAttendance($parent->id);

        // Get low attendance alerts
        $lowAttendanceAlerts = $this->attendanceService->getParentLowAttendanceAlerts($parent->id);

        // Get notification history (attendance related)
        $notificationHistory = Notification::where('notifiable_id', auth()->id())
            ->where('notifiable_type', 'App\Models\User')
            ->where('type', 'like', '%attendance%')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('parent.attendance.index', compact(
            'children',
            'childrenStats',
            'recentAttendance',
            'overallAttendance',
            'totalPresent',
            'totalAbsent',
            'lowAttendanceAlerts',
            'notificationHistory'
        ));
    }

    /**
     * Display detailed attendance for a specific child
     */
    public function childAttendance(Request $request, Student $student)
    {
        $parent = auth()->user()->parent;
        
        if (!$parent) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        // Verify this student belongs to this parent
        if ($student->parent_id !== $parent->id) {
            abort(403, 'Unauthorized access to student data.');
        }

        // Load student relationships
        $child = $student->load(['user', 'enrollments.class.subject']);

        // Get filter parameters
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);
        $classId = $request->input('class_id');

        // Get monthly statistics
        $monthlyStats = $this->attendanceService->getStudentMonthlyStats(
            $child->id,
            $selectedMonth,
            $selectedYear
        );

        // Get class-wise statistics
        $classwiseStats = $this->attendanceService->getStudentClasswiseStats(
            $child->id,
            $selectedMonth,
            $selectedYear
        );

        // Get calendar data
        $calendarData = $this->attendanceService->getStudentAttendanceCalendar(
            $child->id,
            $selectedMonth,
            $selectedYear
        );

        // Get detailed records
        $query = StudentAttendance::where('student_id', $child->id)
            ->whereHas('classSession', function($q) use ($selectedMonth, $selectedYear, $classId) {
                $q->whereMonth('session_date', $selectedMonth)
                  ->whereYear('session_date', $selectedYear);
                
                if ($classId) {
                    $q->where('class_id', $classId);
                }
            })
            ->with(['classSession.class.subject'])
            ->orderBy('created_at', 'desc');

        $attendanceRecords = $query->get();

        // Get overall attendance percentage
        $overallPercentage = $this->attendanceService->getOverallAttendancePercentage($child->id);

        return view('parent.attendance.child', compact(
            'child',
            'selectedMonth',
            'selectedYear',
            'monthlyStats',
            'classwiseStats',
            'calendarData',
            'attendanceRecords',
            'overallPercentage'
        ));
    }
}
