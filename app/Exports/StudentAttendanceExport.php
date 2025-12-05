<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\Support\Collection;

class StudentAttendanceExport
{
    protected int $studentId;
    protected string $dateFrom;
    protected string $dateTo;
    protected ?int $classId;

    public function __construct(int $studentId, string $dateFrom, string $dateTo, ?int $classId = null)
    {
        $this->studentId = $studentId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->classId = $classId;
    }

    /**
     * Get the data collection
     */
    public function collection(): Collection
    {
        $query = StudentAttendance::with(['classSession.class.subject', 'markedBy'])
            ->where('student_id', $this->studentId)
            ->whereHas('classSession', function($q) {
                $q->whereBetween('session_date', [$this->dateFrom, $this->dateTo]);
            });

        if ($this->classId) {
            $query->whereHas('classSession', fn($q) => $q->where('class_id', $this->classId));
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get headings for export
     */
    public function headings(): array
    {
        return [
            'Date',
            'Class',
            'Subject',
            'Session Time',
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
            $record->classSession->class->name ?? 'N/A',
            $record->classSession->class->subject->name ?? 'N/A',
            $record->classSession->start_time->format('H:i') . ' - ' . $record->classSession->end_time->format('H:i'),
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
        $student = Student::with('user')->find($this->studentId);
        $records = $this->collection();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($student, $records) {
            $file = fopen('php://output', 'w');

            // Add student info header
            fputcsv($file, ['Student Attendance Report']);
            fputcsv($file, ['Student Name:', $student->user->name ?? 'N/A']);
            fputcsv($file, ['Student ID:', $student->student_id ?? 'N/A']);
            fputcsv($file, ['Date Range:', $this->dateFrom . ' to ' . $this->dateTo]);
            fputcsv($file, ['Generated:', now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []);

            // Summary
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            $late = $records->where('status', 'late')->count();
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Sessions:', $total]);
            fputcsv($file, ['Present:', $present]);
            fputcsv($file, ['Absent:', $absent]);
            fputcsv($file, ['Late:', $late]);
            fputcsv($file, ['Attendance %:', $percentage . '%']);
            fputcsv($file, []);

            // Add column headers
            fputcsv($file, $this->headings());

            // Add data rows
            foreach ($records as $record) {
                fputcsv($file, $this->map($record));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
