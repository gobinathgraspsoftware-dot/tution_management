<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\Teacher;
use App\Services\TeacherScheduleService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TeacherScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(TeacherScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display teacher's schedule.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        // Check if teacher exists
        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $view = $request->get('view', 'weekly'); // daily, weekly, monthly
        
        // IMPORTANT: Create a fresh Carbon instance to avoid mutation issues
        $date = $request->filled('date') 
            ? Carbon::parse($request->date) 
            : Carbon::now();

        // Get schedule data based on view type
        // Pass a copy of the date to prevent mutation
        $scheduleData = $this->scheduleService->getTeacherSchedule($teacher->id, $view, $date->copy());

        // Get upcoming sessions for today
        $todaySessions = $this->scheduleService->getTodaySessions($teacher->id);

        // Get schedule statistics
        $stats = $this->scheduleService->getScheduleStatistics($teacher->id);

        return view('teacher.schedule.index', compact(
            'teacher',
            'scheduleData',
            'todaySessions',
            'stats',
            'view',
            'date'
        ));
    }

    /**
     * Get weekly schedule (AJAX).
     */
    public function weeklySchedule(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfWeek()
            : Carbon::now()->startOfWeek();

        $scheduleData = $this->scheduleService->getWeeklySchedule($teacher->id, $startDate->copy());

        return response()->json([
            'success' => true,
            'data' => $scheduleData,
            'week_start' => $startDate->format('Y-m-d'),
            'week_end' => $startDate->copy()->endOfWeek()->format('Y-m-d'),
        ]);
    }

    /**
     * Get monthly schedule (AJAX).
     */
    public function monthlySchedule(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $scheduleData = $this->scheduleService->getMonthlySchedule($teacher->id, (int)$month, (int)$year);

        return response()->json([
            'success' => true,
            'data' => $scheduleData,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Get daily schedule details.
     */
    public function dailySchedule(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $date = $request->filled('date') 
            ? Carbon::parse($request->date) 
            : Carbon::now();

        $sessions = $this->scheduleService->getDailySchedule($teacher->id, $date->copy());

        return view('teacher.schedule.daily', compact('teacher', 'sessions', 'date'));
    }

    /**
     * View session details.
     */
    public function sessionDetails(ClassSession $session)
    {
        $teacher = auth()->user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        // Ensure teacher owns this class
        if ($session->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $session->load(['class.subject', 'attendance.student.user']);

        return view('teacher.schedule.session-details', compact('session'));
    }

    /**
     * Export schedule to PDF/CSV.
     */
    public function export(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $format = $request->get('format', 'pdf');
        $view = $request->get('view', 'weekly');
        $date = $request->filled('date') 
            ? Carbon::parse($request->date) 
            : Carbon::now();

        // Get schedule data - pass a copy to avoid mutation
        $scheduleData = $this->scheduleService->getTeacherSchedule($teacher->id, $view, $date->copy());

        if ($format === 'csv') {
            return $this->scheduleService->exportToCsv($scheduleData, $teacher, $view, $date->copy());
        }

        return $this->scheduleService->exportToPdf($scheduleData, $teacher, $view, $date->copy());
    }

    /**
     * Sync schedule to calendar (iCal format).
     */
    public function syncCalendar(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : Carbon::now()->endOfMonth();

        $icalContent = $this->scheduleService->generateICalFeed(
            $teacher->id, 
            $startDate->copy(), 
            $endDate->copy()
        );

        return response($icalContent)
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename="teaching_schedule.ics"');
    }
}
