<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ExamService
{
    /**
     * Create new exam.
     */
    public function createExam(array $data)
    {
        return Exam::create($data);
    }

    /**
     * Update exam.
     */
    public function updateExam(Exam $exam, array $data)
    {
        $exam->update($data);
        return $exam->fresh();
    }

    /**
     * Bulk store exam results.
     */
    public function bulkStoreResults(Exam $exam, array $results)
    {
        $savedResults = [];

        foreach ($results as $resultData) {
            if (!isset($resultData['marks_obtained']) || $resultData['marks_obtained'] === null) {
                continue;
            }

            $percentage = ($resultData['marks_obtained'] / $exam->max_marks) * 100;
            $grade = $this->calculateGrade($percentage);

            $result = ExamResult::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $resultData['student_id'],
                ],
                [
                    'marks_obtained' => $resultData['marks_obtained'],
                    'percentage' => round($percentage, 2),
                    'grade' => $grade,
                    'remarks' => $resultData['remarks'] ?? null,
                ]
            );

            $savedResults[] = $result;
        }

        return $savedResults;
    }

    /**
     * Calculate grade based on percentage.
     */
    public function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 40) return 'D';
        return 'F';
    }

    /**
     * Calculate grades for all results.
     */
    public function calculateGradesAndRanks(Exam $exam)
    {
        $this->calculateGrades($exam);
        $this->calculateRanks($exam);
    }

    /**
     * Calculate grades for exam.
     */
    public function calculateGrades(Exam $exam)
    {
        $results = $exam->results()->whereNotNull('marks_obtained')->get();

        foreach ($results as $result) {
            $percentage = ($result->marks_obtained / $exam->max_marks) * 100;
            $grade = $this->calculateGrade($percentage);

            $result->update([
                'percentage' => round($percentage, 2),
                'grade' => $grade,
            ]);
        }
    }

    /**
     * Calculate ranks for exam.
     */
    public function calculateRanks(Exam $exam)
    {
        $results = $exam->results()
            ->whereNotNull('marks_obtained')
            ->orderBy('marks_obtained', 'desc')
            ->get();

        $rank = 1;
        $previousMarks = null;
        $sameRankCount = 0;

        foreach ($results as $result) {
            if ($previousMarks !== null && $result->marks_obtained < $previousMarks) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            $result->update(['rank' => $rank]);
            $previousMarks = $result->marks_obtained;
        }
    }

    /**
     * Get exam statistics.
     */
    public function getExamStatistics(Exam $exam)
    {
        $results = $exam->results()->whereNotNull('marks_obtained');

        $stats = [
            'total_students' => $exam->class->enrollments()->count(),
            'results_entered' => $results->count(),
            'average_marks' => round($results->avg('marks_obtained'), 2),
            'highest_marks' => $results->max('marks_obtained'),
            'lowest_marks' => $results->min('marks_obtained'),
            'average_percentage' => round($results->avg('percentage'), 2),
            'pass_count' => $results->where('marks_obtained', '>=', $exam->passing_marks)->count(),
            'fail_count' => $results->where('marks_obtained', '<', $exam->passing_marks)->count(),
            'grade_distribution' => $this->getGradeDistribution($exam),
            'top_performers' => $this->getTopPerformers($exam, 5),
        ];

        $stats['pass_percentage'] = $stats['results_entered'] > 0
            ? round(($stats['pass_count'] / $stats['results_entered']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Get grade distribution.
     */
    protected function getGradeDistribution(Exam $exam)
    {
        $grades = ['A+', 'A', 'B+', 'B', 'C', 'D', 'F'];
        $distribution = [];

        foreach ($grades as $grade) {
            $distribution[$grade] = $exam->results()
                ->where('grade', $grade)
                ->count();
        }

        return $distribution;
    }

    /**
     * Get top performers.
     */
    protected function getTopPerformers(Exam $exam, $limit = 5)
    {
        return $exam->results()
            ->with('student.user')
            ->whereNotNull('marks_obtained')
            ->orderBy('marks_obtained', 'desc')
            ->take($limit)
            ->get()
            ->map(function($result) {
                return [
                    'student_name' => $result->student->user->name,
                    'student_id' => $result->student->student_id,
                    'marks' => $result->marks_obtained,
                    'percentage' => $result->percentage,
                    'grade' => $result->grade,
                    'rank' => $result->rank,
                ];
            });
    }

    /**
     * Export results to CSV.
     */
    public function exportResultsToCsv(Exam $exam)
    {
        $filename = 'exam_results_' . str_replace(' ', '_', $exam->name) . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($exam) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Student ID',
                'Student Name',
                'Marks Obtained',
                'Maximum Marks',
                'Percentage',
                'Grade',
                'Rank',
                'Status',
                'Remarks'
            ]);

            // Data rows
            $results = $exam->results()
                ->with('student.user')
                ->orderBy('rank')
                ->get();

            foreach ($results as $result) {
                $status = $result->marks_obtained >= $exam->passing_marks ? 'Pass' : 'Fail';

                fputcsv($file, [
                    $result->student->student_id,
                    $result->student->user->name,
                    $result->marks_obtained ?? 'N/A',
                    $exam->max_marks,
                    $result->percentage ? $result->percentage . '%' : 'N/A',
                    $result->grade ?? 'N/A',
                    $result->rank ?? 'N/A',
                    $status,
                    $result->remarks ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate result card PDF.
     */
    public function generateResultCardPdf(ExamResult $result)
    {
        // Implementation would use a PDF library like DomPDF
        // For now, return view for printing
        $result->load(['exam.class.subject', 'exam.subject', 'student.user', 'student.parent.user']);

        return view('admin.exam-results.result-card-pdf', compact('result'));
    }

    /**
     * Get exam analytics.
     */
    public function getExamAnalytics(Exam $exam)
    {
        $stats = $this->getExamStatistics($exam);

        return [
            'overview' => $stats,
            'grade_chart' => $this->getGradeChartData($stats['grade_distribution']),
            'performance_trend' => $this->getPerformanceTrend($exam),
        ];
    }

    /**
     * Get grade chart data.
     */
    protected function getGradeChartData($distribution)
    {
        return [
            'labels' => array_keys($distribution),
            'data' => array_values($distribution),
        ];
    }

    /**
     * Get performance trend.
     */
    protected function getPerformanceTrend(Exam $exam)
    {
        // Group students by percentage ranges
        $ranges = [
            '90-100' => 0,
            '80-89' => 0,
            '70-79' => 0,
            '60-69' => 0,
            '50-59' => 0,
            '40-49' => 0,
            '0-39' => 0,
        ];

        $results = $exam->results()->whereNotNull('percentage')->get();

        foreach ($results as $result) {
            $percentage = $result->percentage;

            if ($percentage >= 90) $ranges['90-100']++;
            elseif ($percentage >= 80) $ranges['80-89']++;
            elseif ($percentage >= 70) $ranges['70-79']++;
            elseif ($percentage >= 60) $ranges['60-69']++;
            elseif ($percentage >= 50) $ranges['50-59']++;
            elseif ($percentage >= 40) $ranges['40-49']++;
            else $ranges['0-39']++;
        }

        return $ranges;
    }
}
