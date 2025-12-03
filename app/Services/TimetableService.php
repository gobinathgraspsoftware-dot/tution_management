<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\ClassModel;
use App\Models\Enrollment;
use Carbon\Carbon;

class TimetableService
{
    /**
     * Get all classes timetable.
     */
    public function getAllClassesTimetable($view = 'weekly', $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        $schedules = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->formatTimetable($schedules, $view, $date);
    }

    /**
     * Get teacher's timetable.
     */
    public function getTeacherTimetable($teacherId, $view = 'weekly', $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        $schedules = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->whereHas('class', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId)
                  ->where('status', 'active');
            })
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->formatTimetable($schedules, $view, $date);
    }

    /**
     * Get student's timetable.
     */
    public function getStudentTimetable($studentId, $view = 'weekly', $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        // Get enrolled classes
        $enrolledClassIds = Enrollment::where('student_id', $studentId)
            ->where('status', 'active')
            ->pluck('class_id');

        $schedules = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->whereIn('class_id', $enrolledClassIds)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->formatTimetable($schedules, $view, $date);
    }

    /**
     * Get class timetable.
     */
    public function getClassTimetable($classId, $view = 'weekly', $date = null)
    {
        $date = $date ? Carbon::parse($date) : now();

        $schedules = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->formatTimetable($schedules, $view, $date);
    }

    /**
     * Format timetable based on view type.
     */
    protected function formatTimetable($schedules, $view, $date)
    {
        switch ($view) {
            case 'daily':
                return $this->formatDailyView($schedules, $date);
            case 'weekly':
                return $this->formatWeeklyView($schedules, $date);
            case 'monthly':
                return $this->formatMonthlyView($schedules, $date);
            default:
                return $this->formatWeeklyView($schedules, $date);
        }
    }

    /**
     * Format daily view.
     */
    protected function formatDailyView($schedules, $date)
    {
        $dayOfWeek = strtolower($date->format('l'));

        $todaySchedules = $schedules->filter(function($schedule) use ($dayOfWeek) {
            return $schedule->day_of_week === $dayOfWeek;
        })->map(function($schedule) use ($date) {
            return [
                'class_name' => $schedule->class->name,
                'subject' => $schedule->class->subject->name,
                'teacher' => $schedule->class->teacher->user->name ?? 'N/A',
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'location' => $schedule->location,
                'meeting_link' => $schedule->meeting_link,
                'type' => $schedule->class->type,
                'color' => $this->getSubjectColor($schedule->class->subject_id),
            ];
        });

        return [
            'date' => $date->format('Y-m-d'),
            'day' => $date->format('l'),
            'schedules' => $todaySchedules->values(),
        ];
    }

    /**
     * Format weekly view.
     */
    protected function formatWeeklyView($schedules, $date)
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timetable = [];

        foreach ($weekDays as $day) {
            $daySchedules = $schedules->filter(function($schedule) use ($day) {
                return $schedule->day_of_week === $day;
            })->map(function($schedule) {
                return [
                    'class_name' => $schedule->class->name,
                    'subject' => $schedule->class->subject->name,
                    'teacher' => $schedule->class->teacher->user->name ?? 'N/A',
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'location' => $schedule->location,
                    'meeting_link' => $schedule->meeting_link,
                    'type' => $schedule->class->type,
                    'color' => $this->getSubjectColor($schedule->class->subject_id),
                ];
            });

            $timetable[$day] = $daySchedules->values();
        }

        return [
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date' => $endOfWeek->format('Y-m-d'),
            'week_number' => $date->weekOfYear,
            'timetable' => $timetable,
        ];
    }

    /**
     * Format monthly view.
     */
    protected function formatMonthlyView($schedules, $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $monthSchedules = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $dayOfWeek = strtolower($current->format('l'));

            $daySchedules = $schedules->filter(function($schedule) use ($dayOfWeek) {
                return $schedule->day_of_week === $dayOfWeek;
            })->map(function($schedule) {
                return [
                    'class_name' => $schedule->class->name,
                    'subject' => $schedule->class->subject->name,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'type' => $schedule->class->type,
                    'color' => $this->getSubjectColor($schedule->class->subject_id),
                ];
            });

            $monthSchedules[$current->format('Y-m-d')] = $daySchedules->values();
            $current->addDay();
        }

        return [
            'month' => $date->format('F Y'),
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d'),
            'schedules' => $monthSchedules,
        ];
    }

    /**
     * Get color for subject (for visual differentiation).
     */
    protected function getSubjectColor($subjectId)
    {
        $colors = [
            '#6366f1', // indigo
            '#8b5cf6', // violet
            '#ec4899', // pink
            '#f59e0b', // amber
            '#10b981', // emerald
            '#3b82f6', // blue
            '#ef4444', // red
            '#14b8a6', // teal
        ];

        return $colors[$subjectId % count($colors)];
    }

    /**
     * Export timetable to PDF.
     */
    public function exportToPdf($timetableData, $view, $filename)
    {
        // Implementation would use a PDF library like DomPDF or TCPDF
        // For now, return a simple response
        return response()->json([
            'message' => 'PDF export feature - to be implemented with DomPDF',
            'data' => $timetableData
        ]);
    }

    /**
     * Export timetable to CSV.
     */
    public function exportToCsv($timetableData, $view, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($timetableData, $view) {
            $file = fopen('php://output', 'w');

            if ($view === 'weekly') {
                fputcsv($file, ['Day', 'Class', 'Subject', 'Teacher', 'Start Time', 'End Time', 'Location', 'Type']);

                foreach ($timetableData['timetable'] as $day => $schedules) {
                    foreach ($schedules as $schedule) {
                        fputcsv($file, [
                            ucfirst($day),
                            $schedule['class_name'],
                            $schedule['subject'],
                            $schedule['teacher'],
                            $schedule['start_time'],
                            $schedule['end_time'],
                            $schedule['location'] ?? 'N/A',
                            ucfirst($schedule['type']),
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
