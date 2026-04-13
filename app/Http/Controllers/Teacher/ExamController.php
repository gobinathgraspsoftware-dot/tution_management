<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\GradeLevel;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamController extends Controller
{
    /**
     * Display a listing of exams for teacher's classes.
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $query = Exam::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['class.gradeLevel', 'subject', 'results']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('exam_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('exam_date', '<=', $request->to_date);
        }

        $exams = $query->orderBy('exam_date', 'desc')->paginate(15)->withQueryString();

        // Get teacher's classes for filter
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with('gradeLevel')
            ->get();

        // Get subjects for filter
        $subjects = Subject::whereHas('classes', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->get();

        // Calculate statistics
        $stats = [
            'total_exams' => Exam::whereHas('class', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })->count(),
            'upcoming_exams' => Exam::whereHas('class', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })->where('exam_date', '>=', Carbon::today())->count(),
            'completed_exams' => Exam::whereHas('class', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })->where('status', 'completed')->count(),
            'pending_results' => Exam::whereHas('class', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })->where('status', 'completed')->whereDoesntHave('results')->count(),
        ];

        return view('teacher.exams.index', compact('exams', 'classes', 'subjects', 'stats'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        $teacher = Auth::user()->teacher;

        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['subject', 'gradeLevel'])
            ->get();

        $subjects = Subject::whereHas('classes', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->get();

        $gradeLevels = GradeLevel::ordered()->get();

        return view('teacher.exams.create', compact('classes', 'subjects', 'gradeLevels'));
    }

    /**
     * Store a newly created exam.
     */
    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'max_marks' => 'required|numeric|min:1|max:1000',
            'passing_marks' => 'required|numeric|min:0|lte:max_marks',
            'description' => 'nullable|string|max:1000',
        ]);

        // Verify teacher owns the class
        $class = ClassModel::findOrFail($request->class_id);
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $exam = Exam::create([
            'name' => $request->name,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'exam_date' => $request->exam_date,
            'start_time' => $request->start_time,
            'duration_minutes' => $request->duration_minutes,
            'max_marks' => $request->max_marks,
            'passing_marks' => $request->passing_marks,
            'description' => $request->description,
            'status' => 'scheduled',
        ]);

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Exam created successfully.');
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $exam->load(['class.gradeLevel', 'subject', 'results.student.user']);

        // Get enrolled students
        $enrolledStudents = Enrollment::where('class_id', $exam->class_id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Calculate statistics
        $results = $exam->results;
        $stats = [
            'total_students' => $enrolledStudents->count(),
            'results_entered' => $results->count(),
            'pending_results' => $enrolledStudents->count() - $results->count(),
            'highest_score' => $results->max('marks_obtained') ?? 0,
            'lowest_score' => $results->min('marks_obtained') ?? 0,
            'average_score' => $results->avg('marks_obtained') ?? 0,
            'pass_count' => $results->where('marks_obtained', '>=', $exam->passing_marks)->count(),
            'fail_count' => $results->where('marks_obtained', '<', $exam->passing_marks)->count(),
        ];

        return view('teacher.exams.show', compact('exam', 'enrolledStudents', 'stats'));
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        // Cannot edit if results have been entered
        if ($exam->results()->count() > 0) {
            return redirect()->route('teacher.exams.show', $exam)
                ->with('error', 'Cannot edit exam after results have been entered.');
        }

        $exam->load(['class.gradeLevel']);

        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['subject', 'gradeLevel'])
            ->get();

        $subjects = Subject::whereHas('classes', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->get();

        $gradeLevels = GradeLevel::ordered()->get();

        return view('teacher.exams.edit', compact('exam', 'classes', 'subjects', 'gradeLevels'));
    }

    /**
     * Update the specified exam.
     */
    public function update(Request $request, Exam $exam)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        // Cannot edit if results have been entered
        if ($exam->results()->count() > 0) {
            return redirect()->route('teacher.exams.show', $exam)
                ->with('error', 'Cannot edit exam after results have been entered.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'max_marks' => 'required|numeric|min:1|max:1000',
            'passing_marks' => 'required|numeric|min:0|lte:max_marks',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        // Verify teacher owns the new class too
        $class = ClassModel::findOrFail($request->class_id);
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $exam->update($request->only([
            'name', 'class_id', 'subject_id', 'exam_date', 'start_time',
            'duration_minutes', 'max_marks', 'passing_marks', 'description', 'status'
        ]));

        return redirect()->route('teacher.exams.show', $exam)
            ->with('success', 'Exam updated successfully.');
    }

    /**
     * Show form to enter results for an exam.
     */
    public function enterResults(Exam $exam)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $exam->load(['class', 'subject', 'results']);

        // Get enrolled students
        $enrolledStudents = Enrollment::where('class_id', $exam->class_id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Get existing results
        $existingResults = $exam->results->keyBy('student_id');

        return view('teacher.exams.enter-results', compact('exam', 'enrolledStudents', 'existingResults'));
    }

    /**
     * Store exam results.
     */
    public function storeResults(Request $request, Exam $exam)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.marks_obtained' => 'required|numeric|min:0|max:' . $exam->max_marks,
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->results as $result) {
                // Calculate grade
                $percentage = ($result['marks_obtained'] / $exam->max_marks) * 100;
                $grade = $this->calculateGrade($percentage);

                ExamResult::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $result['student_id'],
                    ],
                    [
                        'marks_obtained' => $result['marks_obtained'],
                        'percentage' => round($percentage, 2),
                        'grade' => $grade,
                        'remarks' => $result['remarks'] ?? null,
                    ]
                );
            }

            // Update exam status
            $exam->update(['status' => 'completed']);

            DB::commit();

            return redirect()->route('teacher.exams.show', $exam)
                ->with('success', 'Results saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save results: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get grade levels mapped to a subject.
     *
     * Subject stores grade_levels as JSON array of grade_level IDs.
     * Returns only grade levels the teacher has classes for.
     */
    public function getGradeLevelsBySubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = Auth::user()->teacher;
        $subject = Subject::findOrFail($request->subject_id);
        $gradeLevelIds = $subject->grade_levels ?? [];

        // Further filter to only grade levels where teacher has active classes
        $teacherGradeLevelIds = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->where('subject_id', $subject->id)
            ->whereIn('grade_level_id', $gradeLevelIds)
            ->pluck('grade_level_id')
            ->unique()
            ->toArray();

        $gradeLevels = GradeLevel::whereIn('id', $teacherGradeLevelIds)
            ->ordered()
            ->get(['id', 'name']);

        return response()->json($gradeLevels);
    }

    /**
     * AJAX: Get teacher's active classes filtered by subject and/or grade level.
     */
    public function getClassesByFilters(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $query = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->with(['subject', 'gradeLevel']);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->subject_id);
        }

        if ($request->filled('grade_level_id')) {
            $query->where('grade_level_id', (int) $request->grade_level_id);
        }

        $classes = $query->orderBy('name')->get()->map(function ($class) {
            return [
                'id'              => $class->id,
                'name'            => $class->name,
                'subject_id'      => $class->subject_id,
                'grade_level_id'  => $class->grade_level_id,
                'grade_level_name'=> $class->gradeLevel?->name ?? 'N/A',
                'subject_name'    => $class->subject?->name ?? 'N/A',
            ];
        });

        return response()->json($classes);
    }

    /**
     * Calculate grade based on percentage.
     */
    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        if ($percentage >= 40) return 'E';
        return 'F';
    }
}
