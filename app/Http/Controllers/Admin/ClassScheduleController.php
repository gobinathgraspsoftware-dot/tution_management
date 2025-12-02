<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassScheduleRequest;
use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Services\ClassService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    protected $classService;

    public function __construct(ClassService $classService)
    {
        $this->classService = $classService;
    }

    /**
     * Show schedule management for a class.
     */
    public function index(ClassModel $class)
    {
        $class->load(['subject', 'teacher.user', 'schedules' => fn($q) => $q->orderBy('day_of_week')->orderBy('start_time')]);

        return view('admin.classes.schedule', compact('class'));
    }

    /**
     * Store a new schedule for a class.
     */
    public function store(ClassScheduleRequest $request, ClassModel $class)
    {
        try {
            // Check for conflicts
            $conflicts = $this->classService->checkScheduleConflict(
                $class->teacher_id,
                $request->day_of_week,
                $request->start_time,
                $request->end_time
            );

            if ($conflicts->isNotEmpty()) {
                return back()->withInput()->with('error', 'Schedule conflict detected! Teacher has another class at this time.');
            }

            $schedule = $class->schedules()->create($request->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'ClassSchedule',
                'model_id' => $schedule->id,
                'description' => "Added schedule for class: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Schedule added successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to add schedule: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified schedule.
     */
    public function update(ClassScheduleRequest $request, ClassModel $class, ClassSchedule $schedule)
    {
        try {
            // Check for conflicts (excluding current schedule)
            $conflicts = $this->classService->checkScheduleConflict(
                $class->teacher_id,
                $request->day_of_week,
                $request->start_time,
                $request->end_time,
                $schedule->id
            );

            if ($conflicts->isNotEmpty()) {
                return back()->withInput()->with('error', 'Schedule conflict detected! Teacher has another class at this time.');
            }

            $schedule->update($request->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ClassSchedule',
                'model_id' => $schedule->id,
                'description' => "Updated schedule for class: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Schedule updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(ClassModel $class, ClassSchedule $schedule)
    {
        try {
            $schedule->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'ClassSchedule',
                'model_id' => $schedule->id,
                'description' => "Deleted schedule for class: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Schedule deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    /**
     * Toggle schedule active status.
     */
    public function toggleStatus(ClassModel $class, ClassSchedule $schedule)
    {
        try {
            $schedule->update(['is_active' => !$schedule->is_active]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ClassSchedule',
                'model_id' => $schedule->id,
                'description' => "Toggled schedule status for class: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Schedule status updated!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Show weekly timetable view.
     */
    public function timetable(Request $request)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Get all active schedules with class and teacher information
        $query = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->where('is_active', true)
            ->whereHas('class', fn($q) => $q->active());

        // Filter by teacher if requested
        if ($request->filled('teacher_id')) {
            $query->whereHas('class', fn($q) => $q->where('teacher_id', $request->teacher_id));
        }

        // Filter by subject if requested
        if ($request->filled('subject_id')) {
            $query->whereHas('class', fn($q) => $q->where('subject_id', $request->subject_id));
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->get();

        // Organize schedules by day
        $timetable = [];
        foreach ($days as $day) {
            $timetable[$day] = $schedules->where('day_of_week', $day)->values();
        }

        // Get filter options
        $teachers = \App\Models\Teacher::active()->with('user')->get();
        $subjects = \App\Models\Subject::active()->get();

        return view('admin.classes.timetable', compact('timetable', 'days', 'teachers', 'subjects'));
    }

    /**
     * Get schedule data for AJAX requests.
     */
    public function getSchedule(ClassModel $class)
    {
        $schedules = $class->schedules()
            ->active()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'schedules' => $schedules
        ]);
    }
}
