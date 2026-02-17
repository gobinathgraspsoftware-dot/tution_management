<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Exam;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Display student's exam results.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        // Build query
        $query = ExamResult::where('student_id', $student->id)
            ->with(['exam.class.subject', 'exam.class.teacher.user']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        // Filter by exam type
        if ($request->filled('exam_type')) {
            $query->whereHas('exam', function ($q) use ($request) {
                $q->where('exam_type', $request->exam_type);
            });
        }

        // Get results
        $examResults = $query->latest()->paginate(15)->withQueryString();

        // Get classes for filter
        $classes = $student->enrollments()
            ->where('status', 'active')
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id');

        // Get subjects for filter
        $subjects = $classes->pluck('subject')->unique('id');

        // Calculate statistics
        $stats = $this->calculateStats($student);

        return view('student.results.index', compact(
            'examResults',
            'classes',
            'subjects',
            'stats',
            'student'
        ));
    }

    /**
     * Show specific exam result details.
     */
    public function show(ExamResult $result)
    {
        $user = auth()->user();
        $student = $user->student;

        // Verify result belongs to this student
        if ($result->student_id !== $student->id) {
            abort(403, 'Unauthorized access to this result.');
        }

        $result->load([
            'exam.class.subject',
            'exam.class.teacher.user',
            'student.user'
        ]);

        // Get class average for this exam
        $classAverage = ExamResult::where('exam_id', $result->exam_id)
            ->avg('marks_obtained');

        return view('student.results.show', compact('result', 'classAverage', 'student'));
    }

    /**
     * Calculate result statistics.
     */
    private function calculateStats($student)
    {
        $results = ExamResult::where('student_id', $student->id)->get();

        $total = $results->count();
        $passed = $results->where('grade', '!=', 'F')->count();
        $failed = $results->where('grade', 'F')->count();
        $avgMarks = $total > 0 ? round($results->avg('marks_obtained'), 1) : 0;
        $avgPercentage = $total > 0 ? round($results->avg('percentage'), 1) : 0;

        // Grade distribution
        $gradeDistribution = $results->groupBy('grade')->map(function ($group) {
            return $group->count();
        });

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
            'avg_marks' => $avgMarks,
            'avg_percentage' => $avgPercentage,
            'grade_distribution' => $gradeDistribution,
        ];
    }
}
