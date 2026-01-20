<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ResultController extends Controller
{
    /**
     * Display a listing of all results for teacher's classes.
     */
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $query = ExamResult::whereHas('exam.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['exam.class', 'exam.subject', 'student.user']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by exam
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        // Filter by pass/fail
        if ($request->filled('status')) {
            if ($request->status === 'pass') {
                $query->whereHas('exam', function ($q) {
                    $q->whereColumn('exam_results.marks_obtained', '>=', 'exams.passing_marks');
                });
            } elseif ($request->status === 'fail') {
                $query->whereHas('exam', function ($q) {
                    $q->whereColumn('exam_results.marks_obtained', '<', 'exams.passing_marks');
                });
            }
        }

        $results = $query->latest()->paginate(20)->withQueryString();

        // Get teacher's classes for filter
        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        // Get exams for filter
        $exams = Exam::whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->orderBy('exam_date', 'desc')
            ->get();

        // Calculate statistics
        $allResults = ExamResult::whereHas('exam.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->get();

        $stats = [
            'total_results' => $allResults->count(),
            'average_score' => round($allResults->avg('percentage'), 1),
            'pass_rate' => $allResults->count() > 0 
                ? round(($allResults->where('grade', '!=', 'F')->count() / $allResults->count()) * 100, 1)
                : 0,
            'top_performers' => $allResults->where('grade', 'A+')->count(),
        ];

        return view('teacher.results.index', compact('results', 'classes', 'exams', 'stats'));
    }

    /**
     * View results for a specific exam.
     */
    public function examResults(Exam $exam)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $exam->load(['class', 'subject']);

        $results = ExamResult::where('exam_id', $exam->id)
            ->with('student.user')
            ->orderBy('marks_obtained', 'desc')
            ->get();

        // Get enrolled students for comparison
        $enrolledStudents = Enrollment::where('class_id', $exam->class_id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Students without results
        $resultStudentIds = $results->pluck('student_id')->toArray();
        $studentsWithoutResults = $enrolledStudents->whereNotIn('id', $resultStudentIds);

        // Calculate statistics
        $stats = [
            'total_students' => $enrolledStudents->count(),
            'results_entered' => $results->count(),
            'pending' => $studentsWithoutResults->count(),
            'highest' => $results->max('marks_obtained') ?? 0,
            'lowest' => $results->min('marks_obtained') ?? 0,
            'average' => round($results->avg('marks_obtained'), 1),
            'pass_count' => $results->where('marks_obtained', '>=', $exam->passing_marks)->count(),
            'fail_count' => $results->where('marks_obtained', '<', $exam->passing_marks)->count(),
        ];

        // Grade distribution
        $gradeDistribution = $results->groupBy('grade')->map->count();

        return view('teacher.results.exam', compact('exam', 'results', 'studentsWithoutResults', 'stats', 'gradeDistribution'));
    }

    /**
     * View results for a specific student across teacher's classes.
     */
    public function studentResults(Student $student)
    {
        $teacher = Auth::user()->teacher;

        // Verify student is enrolled in one of teacher's classes
        $isEnrolled = $student->enrollments()
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'Unauthorized access.');
        }

        $student->load('user');

        $results = ExamResult::where('student_id', $student->id)
            ->whereHas('exam.class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with(['exam.class', 'exam.subject'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group results by class
        $resultsByClass = $results->groupBy('exam.class_id');

        // Calculate overall statistics
        $stats = [
            'total_exams' => $results->count(),
            'average_percentage' => round($results->avg('percentage'), 1),
            'highest_percentage' => $results->max('percentage') ?? 0,
            'lowest_percentage' => $results->min('percentage') ?? 0,
            'grade_distribution' => $results->groupBy('grade')->map->count(),
        ];

        // Get classes student is enrolled in (taught by this teacher)
        $enrolledClasses = $student->enrollments()
            ->whereHas('class', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with('class.subject')
            ->get();

        return view('teacher.results.student', compact('student', 'results', 'resultsByClass', 'stats', 'enrolledClasses'));
    }

    /**
     * View a specific result.
     */
    public function show(ExamResult $result)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($result->exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $result->load(['exam.class', 'exam.subject', 'student.user']);

        // Get ranking among all students in this exam
        $allResults = ExamResult::where('exam_id', $result->exam_id)
            ->orderBy('marks_obtained', 'desc')
            ->get();

        $rank = $allResults->search(function ($item) use ($result) {
            return $item->id === $result->id;
        }) + 1;

        $totalStudents = $allResults->count();

        // Get student's other results in same class
        $otherResults = ExamResult::where('student_id', $result->student_id)
            ->where('id', '!=', $result->id)
            ->whereHas('exam', function ($q) use ($result) {
                $q->where('class_id', $result->exam->class_id);
            })
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('teacher.results.show', compact('result', 'rank', 'totalStudents', 'otherResults'));
    }

    /**
     * Edit a specific result.
     */
    public function edit(ExamResult $result)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($result->exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $result->load(['exam.class', 'exam.subject', 'student.user']);

        return view('teacher.results.edit', compact('result'));
    }

    /**
     * Update a specific result.
     */
    public function update(Request $request, ExamResult $result)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($result->exam->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'marks_obtained' => 'required|numeric|min:0|max:' . $result->exam->total_marks,
            'remarks' => 'nullable|string|max:500',
        ]);

        // Calculate new percentage and grade
        $percentage = ($request->marks_obtained / $result->exam->total_marks) * 100;
        $grade = $this->calculateGrade($percentage);

        $result->update([
            'marks_obtained' => $request->marks_obtained,
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'remarks' => $request->remarks,
            'entered_by' => Auth::id(),
        ]);

        return redirect()->route('teacher.results.show', $result)
            ->with('success', 'Result updated successfully.');
    }

    /**
     * Generate class report.
     */
    public function classReport(ClassModel $class, Request $request)
    {
        $teacher = Auth::user()->teacher;

        // Verify teacher owns this class
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $class->load('subject');

        // Get all exams for this class
        $exams = Exam::where('class_id', $class->id)
            ->with('results.student.user')
            ->orderBy('exam_date', 'desc')
            ->get();

        // Get all enrolled students
        $enrolledStudents = Enrollment::where('class_id', $class->id)
            ->where('status', 'active')
            ->with('student.user')
            ->get()
            ->pluck('student');

        // Build student performance matrix
        $studentPerformance = [];
        foreach ($enrolledStudents as $student) {
            $studentResults = ExamResult::where('student_id', $student->id)
                ->whereHas('exam', function ($q) use ($class) {
                    $q->where('class_id', $class->id);
                })
                ->get();

            $studentPerformance[$student->id] = [
                'student' => $student,
                'exams_taken' => $studentResults->count(),
                'total_exams' => $exams->count(),
                'average_percentage' => round($studentResults->avg('percentage'), 1),
                'highest_percentage' => $studentResults->max('percentage') ?? 0,
                'lowest_percentage' => $studentResults->min('percentage') ?? 0,
                'results' => $studentResults->keyBy('exam_id'),
            ];
        }

        // Sort by average percentage
        uasort($studentPerformance, function ($a, $b) {
            return $b['average_percentage'] <=> $a['average_percentage'];
        });

        // Class statistics
        $allResults = ExamResult::whereHas('exam', function ($q) use ($class) {
                $q->where('class_id', $class->id);
            })->get();

        $stats = [
            'total_exams' => $exams->count(),
            'total_students' => $enrolledStudents->count(),
            'class_average' => round($allResults->avg('percentage'), 1),
            'highest_average' => collect($studentPerformance)->max('average_percentage'),
            'lowest_average' => collect($studentPerformance)->min('average_percentage'),
        ];

        return view('teacher.results.class-report', compact('class', 'exams', 'studentPerformance', 'stats'));
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
