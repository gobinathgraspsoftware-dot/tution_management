<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\TimetableService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    /**
     * Display student's timetable/schedule.
     */
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        $view = $request->get('view', 'weekly'); // daily, weekly, monthly
        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        // Get student's timetable
        $timetableData = $this->timetableService->getStudentTimetable($student->id, $view, $date);

        // Get student's enrolled classes for reference (WITHOUT schedule relationship)
        $enrolledClasses = $student->enrollments()
            ->with(['class.subject', 'class.teacher.user'])
            ->where('status', 'active')
            ->get()
            ->pluck('class')
            ->filter();

        // Calculate statistics
        $stats = $this->calculateScheduleStats($timetableData, $view);

        return view('student.schedule.index', compact('timetableData', 'view', 'date', 'enrolledClasses', 'stats'));
    }

    /**
     * Export student schedule.
     */
    public function export(Request $request)
    {
        $student = auth()->user()->student;
        $view = $request->get('view', 'weekly');
        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');
        $format = $request->get('format', 'pdf'); // pdf or csv

        $timetableData = $this->timetableService->getStudentTimetable($student->id, $view, $date);
        $filename = 'my_schedule_' . $date;

        if ($format === 'pdf') {
            return $this->timetableService->exportToPdf($timetableData, $view, $filename);
        } else {
            return $this->timetableService->exportToCsv($timetableData, $view, $filename);
        }
    }

    /**
     * Print student schedule.
     */
    public function print(Request $request)
    {
        $student = auth()->user()->student;
        $view = $request->get('view', 'weekly');
        $date = $request->filled('date') ? $request->date : now()->format('Y-m-d');

        $timetableData = $this->timetableService->getStudentTimetable($student->id, $view, $date);

        return view('student.schedule.print', compact('timetableData', 'view', 'date'));
    }

    /**
     * Get schedule for specific date (AJAX).
     */
    public function getScheduleByDate(Request $request)
    {
        try {
            $student = auth()->user()->student;
            $view = $request->get('view', 'weekly');
            $date = $request->date ?? now()->format('Y-m-d');

            $timetableData = $this->timetableService->getStudentTimetable($student->id, $view, $date);

            return response()->json([
                'success' => true,
                'data' => $timetableData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate schedule statistics.
     */
    private function calculateScheduleStats($timetableData, $view)
    {
        $stats = [
            'total_classes' => 0,
            'total_hours' => 0,
            'classes_by_day' => [],
            'upcoming_class' => null,
        ];

        if (empty($timetableData)) {
            return $stats;
        }

        // Calculate based on view type
        if ($view === 'daily' && isset($timetableData['classes'])) {
            $stats['total_classes'] = count($timetableData['classes']);

            // Find next upcoming class
            $now = now();
            foreach ($timetableData['classes'] as $class) {
                $classTime = \Carbon\Carbon::parse($class['date'] . ' ' . $class['start_time']);
                if ($classTime->isFuture() && is_null($stats['upcoming_class'])) {
                    $stats['upcoming_class'] = $class;
                }
            }
        }
        elseif ($view === 'weekly' && isset($timetableData['days'])) {
            foreach ($timetableData['days'] as $day => $classes) {
                $dayCount = count($classes);
                $stats['total_classes'] += $dayCount;
                $stats['classes_by_day'][$day] = $dayCount;

                // Calculate hours for each class
                foreach ($classes as $class) {
                    if (isset($class['start_time']) && isset($class['end_time'])) {
                        $start = \Carbon\Carbon::parse($class['start_time']);
                        $end = \Carbon\Carbon::parse($class['end_time']);
                        $stats['total_hours'] += $end->diffInHours($start);
                    }
                }
            }

            // Find next upcoming class
            $now = now();
            $currentWeekStart = $timetableData['week_start'] ?? now()->startOfWeek();

            foreach ($timetableData['days'] as $dayName => $classes) {
                $dayIndex = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 0];
                $targetDate = $currentWeekStart->copy()->addDays($dayIndex[$dayName] ?? 0);

                foreach ($classes as $class) {
                    $classTime = $targetDate->copy()->setTimeFromTimeString($class['start_time']);
                    if ($classTime->isFuture() && is_null($stats['upcoming_class'])) {
                        $class['full_date'] = $classTime;
                        $stats['upcoming_class'] = $class;
                        break 2;
                    }
                }
            }
        }
        elseif ($view === 'monthly' && isset($timetableData['days'])) {
            foreach ($timetableData['days'] as $day => $classes) {
                $stats['total_classes'] += count($classes);
            }
        }

        return $stats;
    }

    /**
     * Show today's classes.
     */
    public function today()
    {
        $student = auth()->user()->student;
        $today = now()->format('Y-m-d');

        $timetableData = $this->timetableService->getStudentTimetable($student->id, 'daily', $today);

        return view('student.schedule.today', compact('timetableData'));
    }

    /**
     * Get iCalendar export for calendar sync.
     */
    public function icalendar()
    {
        $student = auth()->user()->student;

        // Get all student's classes (WITHOUT schedule relationship)
        $enrolledClasses = $student->enrollments()
            ->with(['class.subject', 'class.teacher.user'])
            ->where('status', 'active')
            ->get()
            ->pluck('class');

        // Generate iCalendar file
        $ical = $this->timetableService->generateICalendar($enrolledClasses, 'student', $student->id);

        return response($ical)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="my_schedule.ics"');
    }
}
