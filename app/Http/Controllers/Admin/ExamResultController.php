<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamResultRequest;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ActivityLog;
use App\Services\ExamService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamResultController extends Controller
{
    protected $examService;
    protected $notificationService;

    public function __construct(ExamService $examService, NotificationService $notificationService)
    {
        $this->examService = $examService;
        $this->notificationService = $notificationService;
        $this->middleware('permission:view-exam-results')->only(['index', 'show']);
        $this->middleware('permission:create-exam-results')->only(['create', 'store', 'bulkStore']);
        $this->middleware('permission:edit-exam-results')->only(['edit', 'update']);
        $this->middleware('permission:delete-exam-results')->only('destroy');
        $this->middleware('permission:publish-exam-results')->only('publish');
        $this->middleware('permission:generate-result-cards')->only(['resultCard', 'downloadResultCard']);
    }

    /**
     * Display results for an exam.
     */
    public function index(Exam $exam)
    {
        $exam->load(['class.subject', 'subject', 'results.student.user']);

        $students = $exam->class->enrollments()
            ->with('student.user')
            ->whereHas('student', function($q) {
                $q->approved();
            })
            ->get();

        $stats = [
            'total_students' => $students->count(),
            'results_entered' => $exam->results()->whereNotNull('marks_obtained')->count(),
            'published_results' => $exam->results()->where('is_published', true)->count(),
            'average_marks' => $exam->results()->avg('marks_obtained'),
            'highest_marks' => $exam->results()->max('marks_obtained'),
            'lowest_marks' => $exam->results()->min('marks_obtained'),
            'pass_count' => $exam->results()->where('marks_obtained', '>=', $exam->passing_marks)->count(),
            'fail_count' => $exam->results()->where('marks_obtained', '<', $exam->passing_marks)->count(),
        ];

        return view('admin.exam-results.index', compact('exam', 'students', 'stats'));
    }

    /**
     * Show bulk entry form.
     */
    public function create(Exam $exam)
    {
        $students = $exam->class->enrollments()
            ->with(['student.user', 'student.results' => function($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            }])
            ->whereHas('student', function($q) {
                $q->approved();
            })
            ->get();

        return view('admin.exam-results.create', compact('exam', 'students'));
    }

    /**
     * Store bulk results.
     */
    public function bulkStore(Request $request, Exam $exam)
    {
        $request->validate([
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.marks_obtained' => 'nullable|numeric|min:0|max:' . $exam->max_marks,
            'results.*.remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $results = $this->examService->bulkStoreResults($exam, $request->results);

            // Calculate grades and ranks
            $this->examService->calculateGradesAndRanks($exam);

            DB::commit();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_create',
                'model_type' => 'ExamResult',
                'description' => "Entered results for exam: {$exam->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.exam-results.index', $exam)
                ->with('success', 'Results saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save results: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for single result.
     */
    public function edit(ExamResult $result)
    {
        $result->load(['exam', 'student.user']);
        return view('admin.exam-results.edit', compact('result'));
    }

    /**
     * Update single result.
     */
    public function update(ExamResultRequest $request, ExamResult $result)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Calculate percentage and grade
            $data['percentage'] = ($data['marks_obtained'] / $result->exam->max_marks) * 100;
            $data['grade'] = $this->examService->calculateGrade($data['percentage']);

            $result->update($data);

            // Recalculate ranks for all students
            $this->examService->calculateRanks($result->exam);

            DB::commit();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model_type' => 'ExamResult',
                'model_id' => $result->id,
                'description' => "Updated result for {$result->student->user->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.exam-results.index', $result->exam)
                ->with('success', 'Result updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update result: ' . $e->getMessage());
        }
    }

    /**
     * Delete result.
     */
    public function destroy(ExamResult $result)
    {
        try {
            $exam = $result->exam;
            $result->delete();

            // Recalculate ranks
            $this->examService->calculateRanks($exam);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model_type' => 'ExamResult',
                'model_id' => $result->id,
                'description' => "Deleted result for {$result->student->user->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Result deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete result: ' . $e->getMessage());
        }
    }

    /**
     * Publish results and notify parents.
     */
    public function publish(Exam $exam)
    {
        try {
            DB::beginTransaction();

            $results = $exam->results()->whereNotNull('marks_obtained')->get();

            if ($results->isEmpty()) {
                return back()->with('error', 'No results to publish!');
            }

            // Mark all results as published
            $exam->results()->update([
                'is_published' => true,
                'published_at' => now(),
            ]);

            // Send notifications to parents
            foreach ($results as $result) {
                $student = $result->student;
                if ($student->parent && $student->parent->user) {
                    $this->notificationService->sendNotification(
                        $student->parent->user,
                        'exam_result',
                        [
                            'exam_name' => $exam->name,
                            'student_name' => $student->user->name,
                            'marks_obtained' => $result->marks_obtained,
                            'max_marks' => $exam->max_marks,
                            'percentage' => $result->percentage,
                            'grade' => $result->grade,
                            'rank' => $result->rank,
                        ]
                    );
                }
            }

            DB::commit();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'publish',
                'model_type' => 'ExamResult',
                'description' => "Published results for exam: {$exam->name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Results published and parents notified!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to publish results: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish results.
     */
    public function unpublish(Exam $exam)
    {
        try {
            $exam->results()->update([
                'is_published' => false,
                'published_at' => null,
            ]);

            return back()->with('success', 'Results unpublished successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to unpublish results: ' . $e->getMessage());
        }
    }

    /**
     * View result card.
     */
    public function resultCard(ExamResult $result)
    {
        $result->load(['exam.class.subject', 'exam.subject', 'student.user', 'student.parent.user']);

        return view('admin.exam-results.result-card', compact('result'));
    }

    /**
     * Download result card as PDF.
     */
    public function downloadResultCard(ExamResult $result)
    {
        $result->load(['exam.class.subject', 'exam.subject', 'student.user', 'student.parent.user']);

        return $this->examService->generateResultCardPdf($result);
    }

    /**
     * Export results to CSV.
     */
    public function export(Exam $exam)
    {
        return $this->examService->exportResultsToCsv($exam);
    }

    /**
     * View exam statistics.
     */
    public function statistics(Exam $exam)
    {
        $stats = $this->examService->getExamStatistics($exam);

        return view('admin.exam-results.statistics', compact('exam', 'stats'));
    }

    /**
     * Auto-calculate result (AJAX).
     */
    public function autoCalculate(Request $request)
    {
        $request->validate([
            'marks_obtained' => 'required|numeric',
            'max_marks' => 'required|numeric|gt:0',
        ]);

        $percentage = ($request->marks_obtained / $request->max_marks) * 100;
        $grade = $this->examService->calculateGrade($percentage);

        return response()->json([
            'percentage' => round($percentage, 2),
            'grade' => $grade,
        ]);
    }
}
