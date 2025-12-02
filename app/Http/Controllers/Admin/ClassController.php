<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Teacher;
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
        $query = ClassModel::with(['subject', 'teacher.user', 'schedules']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('grade_level', 'like', "%{$search}%")
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

        // Filter by grade level
        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->grade_level);
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

        // Get subjects and teachers for filters
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.classes.index', compact('classes', 'stats', 'subjects', 'teachers'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::active()->with('user')->get();

        return view('admin.classes.create', compact('subjects', 'teachers'));
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
                'description' => "Created class: {$class->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.classes.index')
                ->with('success', 'Class created successfully!');
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

        return view('admin.classes.edit', compact('class', 'subjects', 'teachers'));
    }

    /**
     * Update the specified class.
     */
    public function update(ClassRequest $request, ClassModel $class)
    {
        try {
            $class = $this->classService->updateClass($class, $request->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Updated class: {$class->name}",
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
     * Remove the specified class.
     */
    public function destroy(ClassModel $class)
    {
        try {
            // Check if class has active enrollments
            if ($class->enrollments()->count() > 0) {
                return back()->with('error', 'Cannot delete class with active enrollments. Please transfer or cancel enrollments first.');
            }

            $className = $class->name;
            $class->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'ClassModel',
                'model_id' => $class->id,
                'description' => "Deleted class: {$className}",
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
     * Export classes to CSV.
     */
    public function export(Request $request)
    {
        $classes = ClassModel::with(['subject', 'teacher.user', 'schedules'])
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
            fputcsv($file, ['Code', 'Name', 'Subject', 'Teacher', 'Type', 'Grade Level', 'Capacity', 'Enrolled', 'Status', 'Location', 'Meeting Link']);

            foreach ($classes as $class) {
                fputcsv($file, [
                    $class->code,
                    $class->name,
                    $class->subject->name ?? 'N/A',
                    $class->teacher->user->name ?? 'N/A',
                    ucfirst($class->type),
                    $class->grade_level ?? 'N/A',
                    $class->capacity,
                    $class->current_enrollment,
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
