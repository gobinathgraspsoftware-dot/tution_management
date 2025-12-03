<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamRequest;
use App\Models\Exam;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\ActivityLog;
use App\Services\ExamService;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
        $this->middleware('permission:view-exams')->only(['index', 'show']);
        $this->middleware('permission:create-exams')->only(['create', 'store']);
        $this->middleware('permission:edit-exams')->only(['edit', 'update']);
        $this->middleware('permission:delete-exams')->only('destroy');
    }

    /**
     * Display exams listing.
     */
    public function index(Request $request)
    {
        $query = Exam::with(['class.subject', 'subject'])->latest('exam_date');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('class', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('subject', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('exam_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('exam_date', '<=', $request->date_to);
        }

        $exams = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total' => Exam::count(),
            'scheduled' => Exam::scheduled()->count(),
            'completed' => Exam::completed()->count(),
            'upcoming' => Exam::upcoming()->count(),
        ];

        // Get classes and subjects for filters
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('admin.exams.index', compact('exams', 'stats', 'classes', 'subjects'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('admin.exams.create', compact('classes', 'subjects'));
    }

    /**
     * Store new exam.
     */
    public function store(ExamRequest $request)
    {
        try {
            $exam = $this->examService->createExam($request->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create',
                'model_type' => 'Exam',
                'model_id' => $exam->id,
                'description' => "Created exam: {$exam->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create exam: ' . $e->getMessage());
        }
    }

    /**
     * Display exam details.
     */
    public function show(Exam $exam)
    {
        $exam->load(['class.subject', 'subject', 'results.student.user']);

        $stats = [
            'total_students' => $exam->class->enrollments()->count(),
            'results_entered' => $exam->results()->whereNotNull('marks_obtained')->count(),
            'published_results' => $exam->results()->where('is_published', true)->count(),
            'average_marks' => $exam->results()->avg('marks_obtained'),
            'pass_count' => $exam->results()->where('marks_obtained', '>=', $exam->passing_marks)->count(),
        ];

        return view('admin.exams.show', compact('exam', 'stats'));
    }

    /**
     * Show edit form.
     */
    public function edit(Exam $exam)
    {
        $classes = ClassModel::active()->with('subject')->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('admin.exams.edit', compact('exam', 'classes', 'subjects'));
    }

    /**
     * Update exam.
     */
    public function update(ExamRequest $request, Exam $exam)
    {
        try {
            $exam = $this->examService->updateExam($exam, $request->validated());

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'Exam',
                'model_id' => $exam->id,
                'description' => "Updated exam: {$exam->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update exam: ' . $e->getMessage());
        }
    }

    /**
     * Delete exam.
     */
    public function destroy(Exam $exam)
    {
        try {
            // Check if results exist
            if ($exam->results()->count() > 0) {
                return back()->with('error', 'Cannot delete exam with existing results!');
            }

            $name = $exam->name;
            $exam->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'Exam',
                'model_id' => $exam->id,
                'description' => "Deleted exam: {$name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete exam: ' . $e->getMessage());
        }
    }

    /**
     * Update exam status.
     */
    public function updateStatus(Request $request, Exam $exam)
    {
        $request->validate([
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        try {
            $exam->update(['status' => $request->status]);

            return back()->with('success', 'Exam status updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Get students for exam.
     */
    public function getStudents(Exam $exam)
    {
        $students = $exam->class->enrollments()
            ->with('student.user')
            ->whereHas('student', function($q) {
                $q->approved();
            })
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->student->id,
                    'name' => $enrollment->student->user->name,
                    'student_id' => $enrollment->student->student_id,
                ];
            });

        return response()->json($students);
    }

    /**
     * Duplicate exam.
     */
    public function duplicate(Exam $exam)
    {
        try {
            $newExam = $exam->replicate();
            $newExam->name = $exam->name . ' (Copy)';
            $newExam->status = 'scheduled';
            $newExam->save();

            return redirect()->route('admin.exams.edit', $newExam)
                ->with('success', 'Exam duplicated successfully! Please update the details.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to duplicate exam: ' . $e->getMessage());
        }
    }
}
