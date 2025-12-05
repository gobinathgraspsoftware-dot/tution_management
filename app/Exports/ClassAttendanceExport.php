<?php

namespace App\Exports;

use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\StudentAttendance;
use Illuminate\Support\Collection;

class ClassAttendanceExport
{
    protected int $classId;
    protected string $dateFrom;
    protected string $dateTo;

    public function __construct(int $classId, string $dateFrom, string $dateTo)
    {
        $this->classId = $classId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * Get the data collection
     */
    public function collection(): Collection
    {
        return StudentAttendance::with(['student.user', 'classSession', 'markedBy'])
            ->whereHas('classSession', function($q) {
                $q->where('class_id', $this->classId)
                  ->whereBetween('session_date', [$this->dateFrom, $this->dateTo]);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get headings for export
     */
    public function headings(): array
    {
        return [
            'Date',
            'Session',
            'Student Name',
            'Student ID',
            'Status',
            'Check-in Time',
            'Remarks',
            'Marked By',
            'Parent Notified',
        ];
    }

    /**
     * Map data for export
     */
    public function map($record): array
    {
        return [
            $record->classSession->session_date->format('d/m/Y'),
            $record->classSession->topic ?? 'Session ' . $record->classSession->id,
            $record->student->user->name ?? 'N/A',
            $record->student->student_id ?? 'N/A',
            ucfirst($record->status),
            $record->check_in_time ? $record->check_in_time->format('H:i') : '-',
            $record->remarks ?? '-',
            $record->markedBy->name ?? 'System',
            $record->parent_notified ? 'Yes' : 'No',
        ];
    }

    /**
     * Download as CSV
     */
    public function download(string $filename)
    {
        $class = ClassModel::with(['subject', 'teacher.user'])->find($this->classId);
        $records = $this->collection();

        // Get student summary
        $studentSummary = $this->getStudentSummary();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($class, $records, $studentSummary) {
            $file = fopen('php://output', 'w');

            // Add class info header
            fputcsv($file, ['Class Attendance Report']);
            fputcsv($file, ['Class Name:', $class->name ?? 'N/A']);
            fputcsv($file, ['Class Code:', $class->code ?? 'N/A']);
            fputcsv($file, ['Subject:', $class->subject->name ?? 'N/A']);
            fputcsv($file, ['Teacher:', $class->teacher->user->name ?? 'N/A']);
            fputcsv($file, ['Date Range:', $this->dateFrom . ' to ' . $this->dateTo]);
            fputcsv($file, ['Generated:', now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []);

            // Overall Summary
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            $late = $records->where('status', 'late')->count();
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            fputcsv($file, ['OVERALL SUMMARY']);
            fputcsv($file, ['Total Attendance Records:', $total]);
            fputcsv($file, ['Present:', $present]);
            fputcsv($file, ['Absent:', $absent]);
            fputcsv($file, ['Late:', $late]);
            fputcsv($file, ['Overall Attendance %:', $percentage . '%']);
            fputcsv($file, []);

            // Student-wise Summary
            fputcsv($file, ['STUDENT-WISE SUMMARY']);
            fputcsv($file, ['Student Name', 'Student ID', 'Total', 'Present', 'Absent', 'Late', 'Attendance %']);

            foreach ($studentSummary as $summary) {
                fputcsv($file, [
                    $summary['name'],
                    $summary['student_id'],
                    $summary['total'],
                    $summary['present'],
                    $summary['absent'],
                    $summary['late'],
                    $summary['percentage'] . '%',
                ]);
            }

            fputcsv($file, []);

            // Detailed Records
            fputcsv($file, ['DETAILED ATTENDANCE RECORDS']);
            fputcsv($file, $this->headings());

            foreach ($records as $record) {
                fputcsv($file, $this->map($record));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get student-wise summary
     */
    protected function getStudentSummary(): array
    {
        $class = ClassModel::with('enrollments.student.user')->find($this->classId);
        $sessions = ClassSession::where('class_id', $this->classId)
            ->whereBetween('session_date', [$this->dateFrom, $this->dateTo])
            ->pluck('id');

        return $class->enrollments->map(function($enrollment) use ($sessions) {
            $attendance = StudentAttendance::where('student_id', $enrollment->student_id)
                ->whereIn('class_session_id', $sessions)
                ->get();

            $total = $attendance->count();
            $present = $attendance->where('status', 'present')->count();

            return [
                'name' => $enrollment->student->user->name ?? 'N/A',
                'student_id' => $enrollment->student->student_id ?? 'N/A',
                'total' => $total,
                'present' => $present,
                'absent' => $attendance->where('status', 'absent')->count(),
                'late' => $attendance->where('status', 'late')->count(),
                'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            ];
        })->sortByDesc('percentage')->values()->toArray();
    }
}
