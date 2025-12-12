<?php

namespace App\Exports;

use App\Services\TeacherPerformanceService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class TeacherPerformanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    protected $filters;
    protected $performanceService;

    public function __construct($filters, TeacherPerformanceService $performanceService)
    {
        $this->filters = $filters;
        $this->performanceService = $performanceService;
    }

    /**
     * Get the data collection for export
     */
    public function collection()
    {
        $startDate = $this->filters['start_date'];
        $endDate = $this->filters['end_date'];
        $employmentType = $this->filters['employment_type'] ?? null;

        $data = $this->performanceService->getComparativeData($startDate, $endDate);

        // Filter by employment type if specified
        if ($employmentType) {
            $data = array_filter($data, function($item) use ($employmentType) {
                return $item['employment_type'] === $employmentType;
            });
        }

        // Transform to array collection
        $collection = [];
        $rank = 1;

        foreach ($data as $item) {
            $collection[] = [
                'rank' => $rank++,
                'teacher_name' => $item['teacher_name'],
                'employment_type' => ucwords(str_replace('_', ' ', $item['employment_type'])),
                'classes_conducted' => $item['classes'],
                'total_hours' => number_format($item['hours'], 2),
                'materials_uploaded' => $item['materials'],
                'average_rating' => number_format($item['rating'], 2),
                'total_reviews' => $item['reviews'],
                'attendance_rate' => number_format($item['attendance'], 2) . '%',
                'punctuality_rate' => number_format($item['punctuality'], 2) . '%',
                'performance_score' => number_format($item['score'], 2),
                'grade' => $this->getPerformanceGrade($item['score']),
            ];
        }

        return collect($collection);
    }

    /**
     * Define headings
     */
    public function headings(): array
    {
        return [
            'Rank',
            'Teacher Name',
            'Employment Type',
            'Classes Conducted',
            'Total Hours',
            'Materials Uploaded',
            'Avg Rating',
            'Reviews',
            'Attendance %',
            'Punctuality %',
            'Score',
            'Grade',
        ];
    }

    /**
     * Apply styles
     */
    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A5568'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center align numeric columns
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D:L')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-fit row height
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,  // Rank
            'B' => 25, // Teacher Name
            'C' => 18, // Employment Type
            'D' => 18, // Classes
            'E' => 15, // Hours
            'F' => 18, // Materials
            'G' => 12, // Rating
            'H' => 10, // Reviews
            'I' => 15, // Attendance
            'J' => 15, // Punctuality
            'K' => 12, // Score
            'L' => 10, // Grade
        ];
    }

    /**
     * Set sheet title
     */
    public function title(): string
    {
        return 'Teacher Performance';
    }

    /**
     * Get performance grade based on score
     */
    protected function getPerformanceGrade($score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 85) return 'A';
        if ($score >= 80) return 'A-';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'B-';
        if ($score >= 60) return 'C+';
        if ($score >= 55) return 'C';
        if ($score >= 50) return 'C-';
        return 'D';
    }
}
