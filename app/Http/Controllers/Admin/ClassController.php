<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\GradeLevel;          // ← ADDED: for dynamic grade levels
use App\Services\ClassService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    protected $classService;

    public function __construct(ClassService $classService)
    {
        $this->classService = $classService;
    }

    /**
     * Display a listing of classes.
     */
    public function index(Request $request)
    {
        $query = ClassModel::with(['subject', 'teacher.user', 'gradeLevel', 'schedules']); // ← ADDED 'gradeLevel' eager load

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  // CHANGED: search by grade level name via relationship instead of varchar
                  ->orWhereHas('gradeLevel', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('teacher.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // CHANGED: Filter by grade level ID instead of varchar string
        if ($request->filled('grade_level')) {
            $query->where('grade_level_id', $request->grade_level);
        }

        $classes = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => ClassModel::count(),
            'active' => ClassModel::active()->count(),
            'online' => ClassModel::online()->count(),
            'offline' => ClassModel::offline()->count(),
            'full' => ClassModel::full()->count(),
            'available_seats' => ClassModel::active()->sum(DB::raw('capacity - current_enrollment')),
        ];

        // Get subjects, teachers, and grade levels for filters
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();
        $gradeLevels = GradeLevel::ordered()->get();   // ← ADDED

        return view('admin.classes.index', compact('classes', 'stats', 'subjects', 'teachers', 'gradeLevels'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();
        $gradeLevels = GradeLevel::ordered()->get();   // ← ADDED

        return view('admin.classes.create', compact('subjects', 'teachers', 'gradeLevels'));
    }

    /**
     * Store a newly created class.
     */
    public function store(ClassRequest $request)
    {
        try {
            $class = $this->classService->createClass($request->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Created class: {$class->name} (Code: {$class->code}) with price RM " . number_format($class->price, 2),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.classes.index')
                ->with('success', "Class created successfully! Auto-generated code: {$class->code}");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create class: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified class.
     */
    public function show(ClassModel $class)
    {
        $class->load([
            'subject',
            'teacher.user',
            'gradeLevel',          // ← ADDED
            'schedules' => fn($q) => $q->active()->orderBy('day_of_week')->orderBy('start_time'),
            'enrollments.student.user',
            'sessions' => fn($q) => $q->upcoming()->take(10)
        ]);

        $stats = [
            'total_students' => $class->enrollments()->count(),
            'attendance_rate' => $this->classService->calculateAttendanceRate($class->id),
            'sessions_completed' => $class->sessions()->completed()->count(),
            'sessions_upcoming' => $class->sessions()->upcoming()->count(),
        ];

        return view('admin.classes.show', compact('class', 'stats'));
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassModel $class)
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();
        $gradeLevels = GradeLevel::ordered()->get();   // ← ADDED

        return view('admin.classes.edit', compact('class', 'subjects', 'teachers', 'gradeLevels'));
    }

    /**
     * Update the specified class.
     */
    public function update(ClassRequest $request, ClassModel $class)
    {
        try {
            $oldPrice = $class->price;
            $class = $this->classService->updateClass($class, $request->validated());

            // Build description with price change info
            $description = "Updated class: {$class->name}";
            if ($oldPrice != $class->price) {
                $oldPriceStr = 'RM ' . number_format($oldPrice, 2);
                $newPriceStr = 'RM ' . number_format($class->price, 2);
                $description .= " (Price changed from {$oldPriceStr} to {$newPriceStr})";
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.classes.index')
                ->with('success', 'Class updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update class: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified class (soft-delete).
     *
     * FIXED: Before soft-deleting, the class code is released by renaming it
     * (e.g., SEJ001 → SEJ001_del_1707465600). This prevents the
     * "Duplicate entry for key 'classes_code_unique'" error when a new class
     * is created with the same subject prefix.
     */
    public function destroy(ClassModel $class)
    {
        try {
            // Check if class has active enrollments
            if ($class->enrollments()->count() > 0) {
                return back()->with('error', 'Cannot delete class with active enrollments. Please transfer or cancel enrollments first.');
            }

            $className = $class->name;
            $classCode = $class->code;

            // Release the class code BEFORE soft-deleting
            $this->classService->releaseClassCode($class);

            // Now soft-delete the class
            $class->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Deleted class: {$className} (Code: {$classCode})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.classes.index')
                ->with('success', 'Class deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete class: ' . $e->getMessage());
        }
    }

    /**
     * Toggle class status.
     */
    public function toggleStatus(ClassModel $class)
    {
        try {
            $newStatus = $class->status === 'active' ? 'inactive' : 'active';
            $class->update(['status' => $newStatus]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Changed class status to {$newStatus}: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', "Class status changed to {$newStatus}!");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to change status: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Preview auto-generated class code based on selected subject.
     */
    public function generateCode(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        try {
            $code = $this->classService->previewClassCode($request->subject_id);
            $subject = Subject::find($request->subject_id);

            return response()->json([
                'success' => true,
                'code' => $code,
                'subject_name' => $subject->name ?? '',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate code preview.',
            ], 500);
        }
    }

    /**
     * Display timetable view.
     */
    public function timetable(Request $request)
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $query = \App\Models\ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->whereHas('class', function($q) {
                $q->where('status', 'active');
            })
            ->where('is_active', true);

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->whereHas('class', function($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->whereHas('class', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        $schedules = $query->get();

        // Group by day
        $timetable = [];
        foreach ($days as $day) {
            $timetable[$day] = $schedules->filter(function($schedule) use ($day) {
                return $schedule->day_of_week === $day;
            });
        }

        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.classes.timetable', compact('timetable', 'days', 'subjects', 'teachers'));
    }

    /**
     * Export classes to CSV.
     */
    public function export(Request $request)
    {
        // CHANGED: eager load 'gradeLevel' for export
        $classes = ClassModel::with(['subject', 'teacher.user', 'gradeLevel', 'schedules'])
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->get();

        $filename = 'classes_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($classes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Code',
                'Name',
                'Subject',
                'Teacher',
                'Type',
                'Grade Level',
                'Capacity',
                'Enrolled',
                'Price (RM)',
                'Status',
                'Location',
                'Meeting Link'
            ]);

            foreach ($classes as $class) {
                fputcsv($file, [
                    $class->code,
                    $class->name,
                    $class->subject->name ?? 'N/A',
                    $class->teacher->user->name ?? 'N/A',
                    ucfirst($class->type),
                    $class->gradeLevel->name ?? 'N/A',   // CHANGED: from $class->grade_level
                    $class->capacity,
                    $class->current_enrollment,
                    number_format($class->price, 2),
                    ucfirst($class->status),
                    $class->location ?? 'N/A',
                    $class->meeting_link ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
