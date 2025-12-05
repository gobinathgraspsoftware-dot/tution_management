<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceReportRequest;
use App\Services\AttendanceReportService;
use App\Services\NotificationService;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\LowAttendanceAlert;
use App\Models\StudentAttendance;
use App\Models\ClassAttendanceSummary;
use App\Exports\StudentAttendanceExport;
use App\Exports\ClassAttendanceExport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    protected $reportService;
    protected $notificationService;

    public function __construct(
        AttendanceReportService $reportService,
        NotificationService $notificationService
    ) {
        $this->reportService = $reportService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display attendance reports dashboard
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'month');

        $stats = $this->reportService->getDashboardStats($period);
        $lowAttendanceStudents = $this->reportService->getLowAttendanceStudents(75, 10);
        $recentAlerts = LowAttendanceAlert::with(['student.user', 'class'])
            ->latest()
            ->limit(5)
            ->get();

        $attendanceTrends = $this->reportService->getAttendanceTrends($period);
        $classComparison = $this->reportService->getClassAttendanceComparison();

        return view('admin.attendance.reports.index', compact(
            'stats',
            'lowAttendanceStudents',
            'recentAlerts',
            'attendanceTrends',
            'classComparison',
            'period'
        ));
    }

    /**
     * Generate student attendance report
     */
    public function studentReport(Request $request)
    {
        $students = Student::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        $classes = ClassModel::where('status', 'active')->get();

        $selectedStudent = null;
        $reportData = null;

        if ($request->filled('student_id')) {
            $selectedStudent = Student::with(['user', 'parent.user', 'enrollments.class.subject'])
                ->findOrFail($request->student_id);

            $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));
            $classId = $request->input('class_id');

            $reportData = $this->reportService->generateStudentReport(
                $selectedStudent->id,
                $dateFrom,
                $dateTo,
                $classId
            );
        }

        return view('admin.attendance.reports.student', compact(
            'students',
            'classes',
            'selectedStudent',
            'reportData'
        ));
    }

    /**
     * Generate class attendance report
     */
    public function classReport(Request $request)
    {
        $classes = ClassModel::with(['subject', 'teacher.user'])
            ->where('status', 'active')
            ->get();

        $selectedClass = null;
        $reportData = null;

        if ($request->filled('class_id')) {
            $selectedClass = ClassModel::with(['subject', 'teacher.user', 'enrollments.student.user'])
                ->findOrFail($request->class_id);

            $dateFrom = $request->input('date_from', now()->subMonth()->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));

            $reportData = $this->reportService->generateClassReport(
                $selectedClass->id,
                $dateFrom,
                $dateTo
            );
        }

        return view('admin.attendance.reports.class', compact(
            'classes',
            'selectedClass',
            'reportData'
        ));
    }

    /**
     * Display low attendance alerts
     */
    public function lowAttendance(Request $request)
    {
        $threshold = $request->input('threshold', 75);
        $classId = $request->input('class_id');

        $query = $this->reportService->getLowAttendanceQuery($threshold);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $lowAttendanceStudents = $query->paginate(20);

        $classes = ClassModel::where('status', 'active')->get();

        $alerts = LowAttendanceAlert::with(['student.user', 'class', 'notifiedBy'])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->latest()
            ->paginate(15);

        return view('admin.attendance.reports.low-attendance', compact(
            'lowAttendanceStudents',
            'alerts',
            'classes',
            'threshold',
            'classId'
        ));
    }

    /**
     * Send low attendance alert to parent
     */
    public function sendAlert(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'message' => 'nullable|string|max:500',
        ]);

        $student = Student::with(['user', 'parent.user'])->findOrFail($request->student_id);
        $class = ClassModel::with('subject')->findOrFail($request->class_id);

        // Get attendance percentage
        $summary = ClassAttendanceSummary::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->first();

        $percentage = $summary->attendance_percentage ?? 0;

        // Create alert record
        $alert = LowAttendanceAlert::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'attendance_percentage' => $percentage,
            'threshold' => 75,
            'alert_message' => $request->message ?? "Your child's attendance is below the required threshold.",
            'notified_by' => auth()->id(),
            'notified_at' => now(),
            'status' => 'sent',
        ]);

        // Send notification to parent
        if ($student->parent && $student->parent->user) {
            $data = [
                'student_name' => $student->user->name,
                'class_name' => $class->name,
                'subject_name' => $class->subject->name ?? 'N/A',
                'attendance_percentage' => $percentage,
                'threshold' => 75,
                'message' => $request->message,
            ];

            $this->notificationService->send(
                $student->parent->user,
                'low_attendance_alert',
                $data,
                ['whatsapp', 'email']
            );
        }

        return back()->with('success', 'Low attendance alert sent to parent successfully.');
    }

    /**
     * Send bulk alerts
     */
    public function sendBulkAlerts(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $sentCount = 0;
        $class = ClassModel::with('subject')->findOrFail($request->class_id);

        foreach ($request->student_ids as $studentId) {
            $student = Student::with(['user', 'parent.user'])->find($studentId);

            if (!$student || !$student->parent) continue;

            $summary = ClassAttendanceSummary::where('student_id', $studentId)
                ->where('class_id', $class->id)
                ->where('month', now()->month)
                ->where('year', now()->year)
                ->first();

            $percentage = $summary->attendance_percentage ?? 0;

            // Create alert
            LowAttendanceAlert::create([
                'student_id' => $studentId,
                'class_id' => $class->id,
                'attendance_percentage' => $percentage,
                'threshold' => 75,
                'notified_by' => auth()->id(),
                'notified_at' => now(),
                'status' => 'sent',
            ]);

            // Send notification
            $data = [
                'student_name' => $student->user->name,
                'class_name' => $class->name,
                'subject_name' => $class->subject->name ?? 'N/A',
                'attendance_percentage' => $percentage,
                'threshold' => 75,
            ];

            $this->notificationService->send(
                $student->parent->user,
                'low_attendance_alert',
                $data,
                ['whatsapp', 'email']
            );

            $sentCount++;
        }

        return back()->with('success', "Low attendance alerts sent to {$sentCount} parents.");
    }

    /**
     * Display attendance history
     */
    public function history(Request $request)
    {
        $query = StudentAttendance::with([
            'student.user',
            'classSession.class.subject',
            'markedBy'
        ]);

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('classSession', fn($q) => $q->where('class_id', $request->class_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('classSession', fn($q) =>
                $q->whereDate('session_date', '>=', $request->date_from)
            );
        }

        if ($request->filled('date_to')) {
            $query->whereHas('classSession', fn($q) =>
                $q->whereDate('session_date', '<=', $request->date_to)
            );
        }

        $records = $query->latest('created_at')->paginate(30);

        $students = Student::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->get();

        $classes = ClassModel::where('status', 'active')->get();

        return view('admin.attendance.reports.history', compact(
            'records',
            'students',
            'classes'
        ));
    }

    /**
     * Export student attendance report
     */
    public function exportStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:csv,xlsx,pdf',
        ]);

        $student = Student::with('user')->findOrFail($request->student_id);
        $filename = "attendance_{$student->student_id}_{$request->date_from}_{$request->date_to}";

        $export = new StudentAttendanceExport(
            $request->student_id,
            $request->date_from,
            $request->date_to,
            $request->class_id
        );

        if ($request->format === 'pdf') {
            return $this->exportPdf($student, $request->date_from, $request->date_to, $request->class_id);
        }

        $extension = $request->format === 'xlsx' ? 'xlsx' : 'csv';

        return $export->download("{$filename}.{$extension}");
    }

    /**
     * Export class attendance report
     */
    public function exportClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:csv,xlsx,pdf',
        ]);

        $class = ClassModel::findOrFail($request->class_id);
        $filename = "class_attendance_{$class->code}_{$request->date_from}_{$request->date_to}";

        $export = new ClassAttendanceExport(
            $request->class_id,
            $request->date_from,
            $request->date_to
        );

        $extension = $request->format === 'xlsx' ? 'xlsx' : 'csv';

        return $export->download("{$filename}.{$extension}");
    }

    /**
     * Export to PDF
     */
    protected function exportPdf($student, $dateFrom, $dateTo, $classId = null)
    {
        $reportData = $this->reportService->generateStudentReport(
            $student->id,
            $dateFrom,
            $dateTo,
            $classId
        );

        // Generate PDF using simple HTML response (no external PDF library)
        $html = view('admin.attendance.reports.pdf.student', compact(
            'student',
            'reportData',
            'dateFrom',
            'dateTo'
        ))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=attendance_{$student->student_id}.html");
    }

    /**
     * Email report to parent
     */
    public function emailToParent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $student = Student::with(['user', 'parent.user'])->findOrFail($request->student_id);

        if (!$student->parent || !$student->parent->user->email) {
            return back()->with('error', 'Parent email not found.');
        }

        $reportData = $this->reportService->generateStudentReport(
            $student->id,
            $request->date_from,
            $request->date_to
        );

        $data = [
            'student_name' => $student->user->name,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'total_sessions' => $reportData['summary']['total_sessions'],
            'present_count' => $reportData['summary']['present'],
            'absent_count' => $reportData['summary']['absent'],
            'attendance_percentage' => $reportData['summary']['percentage'],
            'parent_name' => $student->parent->user->name,
        ];

        $this->notificationService->send(
            $student->parent->user,
            'attendance_report',
            $data,
            ['email']
        );

        return back()->with('success', 'Attendance report emailed to parent successfully.');
    }

    /**
     * Resend attendance notification
     */
    public function resendNotification($attendanceId)
    {
        $attendance = StudentAttendance::with([
            'student.user',
            'student.parent.user',
            'classSession.class.subject'
        ])->findOrFail($attendanceId);

        if (!$attendance->student->parent) {
            return back()->with('error', 'Parent not found for this student.');
        }

        $session = $attendance->classSession;
        $data = [
            'student_name' => $attendance->student->user->name,
            'attendance_status' => ucfirst($attendance->status),
            'attendance_date' => $session->session_date->format('d/m/Y'),
            'class_name' => $session->class->name,
            'subject_name' => $session->class->subject->name ?? 'N/A',
            'time' => $session->start_time->format('H:i'),
        ];

        $this->notificationService->send(
            $attendance->student->parent->user,
            'attendance',
            $data,
            ['whatsapp']
        );

        $attendance->update([
            'parent_notified' => true,
            'notified_at' => now(),
        ]);

        return back()->with('success', 'Notification resent successfully.');
    }
}
