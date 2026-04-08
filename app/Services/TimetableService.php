<?php

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\ClassModel;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
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
            ->whereHas('class')
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

        $enrolledClassIds = Enrollment::where('student_id', $studentId)
            ->where('status', 'active')
            ->pluck('class_id');

        $schedules = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->whereIn('class_id', $enrolledClassIds)
            ->whereHas('class')
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
            ->whereHas('class')
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $this->formatTimetable($schedules, $view, $date);
    }

    /**
     * Get filtered timetable — supports class AND/OR teacher AND/OR grade combined filtering.
     *
     * @param  int|null    $classId
     * @param  int|null    $teacherId
     * @param  string      $view      daily|weekly|monthly
     * @param  mixed       $date
     * @param  int|null    $gradeLevelId   CHANGED: was $gradeLevel (string) → now FK ID
     * @return array
     */
    public function getFilteredTimetable($classId = null, $teacherId = null, $view = 'weekly', $date = null, $gradeLevelId = null)
    {
        // If no filters at all, return everything
        if (!$classId && !$teacherId && !$gradeLevelId) {
            return $this->getAllClassesTimetable($view, $date);
        }

        // If only single simple filter (no grade), delegate to existing methods
        if ($classId && !$teacherId && !$gradeLevelId) {
            return $this->getClassTimetable($classId, $view, $date);
        }
        if ($teacherId && !$classId && !$gradeLevelId) {
            return $this->getTeacherTimetable($teacherId, $view, $date);
        }

        // Combined filters — build query with all conditions
        $date = $date ? Carbon::parse($date) : now();

        $query = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->whereHas('class')
            ->where('is_active', true);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($teacherId) {
            $query->whereHas('class', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId)
                  ->where('status', 'active');
            });
        }

        // CHANGED: was ->where('grade_level', $gradeLevel) → now uses grade_level_id FK
        if ($gradeLevelId) {
            $query->whereHas('class', function ($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            });
        }

        $schedules = $query->orderBy('day_of_week')
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
     * FIX: Times formatted as strings, location/meeting_link from class relation.
     */
    protected function formatDailyView($schedules, $date)
    {
        $dayOfWeek = strtolower($date->format('l'));

        $todaySchedules = $schedules->filter(function($schedule) use ($dayOfWeek) {
            return $schedule->day_of_week === $dayOfWeek;
        })->filter(function($schedule) {
            return $schedule->class && $schedule->class->subject;
        })->map(function($schedule) {
            return [
                'class_name'   => $schedule->class->name,
                'subject'      => $schedule->class->subject->name,
                'teacher'      => $schedule->class->teacher->user->name ?? 'N/A',
                'teacher_name' => $schedule->class->teacher->user->name ?? 'N/A',
                'start_time'   => $this->formatTime($schedule->start_time),
                'end_time'     => $this->formatTime($schedule->end_time),
                'location'     => $schedule->class->location,
                'meeting_link' => $schedule->class->meeting_link,
                'type'         => $schedule->class->type,
                'color'        => $this->getSubjectColor($schedule->class->subject_id),
            ];
        });

        return [
            'date'      => $date->format('Y-m-d'),
            'day'       => $date->format('l'),
            'schedules' => $todaySchedules->values(),
        ];
    }

    /**
     * Format weekly view.
     * FIX: key renamed to 'teacher_name', times as plain strings, location from class.
     */
    protected function formatWeeklyView($schedules, $date)
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek   = $date->copy()->endOfWeek();

        $weekDays  = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $timetable = [];

        foreach ($weekDays as $day) {
            $daySchedules = $schedules->filter(function($schedule) use ($day) {
                return $schedule->day_of_week === $day;
            })->filter(function($schedule) {
                return $schedule->class && $schedule->class->subject;
            })->map(function($schedule) {
                return [
                    'class_name'   => $schedule->class->name,
                    'subject'      => $schedule->class->subject->name,
                    'teacher_name' => $schedule->class->teacher->user->name ?? 'N/A',
                    'start_time'   => $this->formatTime($schedule->start_time),
                    'end_time'     => $this->formatTime($schedule->end_time),
                    'location'     => $schedule->class->location,
                    'meeting_link' => $schedule->class->meeting_link,
                    'type'         => $schedule->class->type,
                    'color'        => $this->getSubjectColor($schedule->class->subject_id),
                ];
            });

            $timetable[$day] = $daySchedules->values();
        }

        return [
            'week_number' => $date->weekOfYear,
            'start_date'  => $startOfWeek->format('Y-m-d'),
            'end_date'    => $endOfWeek->format('Y-m-d'),
            'timetable'   => $timetable,
        ];
    }

    /**
     * Format monthly view.
     */
    protected function formatMonthlyView($schedules, $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();
        $monthSchedules = [];

        for ($d = $startOfMonth->copy(); $d <= $endOfMonth; $d->addDay()) {
            $dayOfWeek = strtolower($d->format('l'));
            $dateKey   = $d->format('Y-m-d');

            $daySchedules = $schedules->filter(function($schedule) use ($dayOfWeek) {
                return $schedule->day_of_week === $dayOfWeek;
            })->filter(function($schedule) {
                return $schedule->class && $schedule->class->subject;
            })->map(function($schedule) {
                return [
                    'class_name'   => $schedule->class->name,
                    'subject'      => $schedule->class->subject->name,
                    'teacher_name' => $schedule->class->teacher->user->name ?? 'N/A',
                    'start_time'   => $this->formatTime($schedule->start_time),
                    'end_time'     => $this->formatTime($schedule->end_time),
                    'location'     => $schedule->class->location,
                    'type'         => $schedule->class->type,
                    'color'        => $this->getSubjectColor($schedule->class->subject_id),
                ];
            });

            if ($daySchedules->isNotEmpty()) {
                $monthSchedules[$dateKey] = $daySchedules->values();
            }
        }

        return [
            'month'      => $date->format('F Y'),
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date'   => $endOfMonth->format('Y-m-d'),
            'schedules'  => $monthSchedules,
        ];
    }

    /**
     * Safely format time values to "HH:mm" strings.
     * Handles Carbon objects, DateTime, and plain strings.
     */
    protected function formatTime($time)
    {
        if ($time instanceof \Carbon\Carbon || $time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }

        // Already a string like "08:00:00" or "08:00"
        if (is_string($time)) {
            return date('H:i', strtotime($time));
        }

        return (string) $time;
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
     * Export timetable to PDF using DomPDF.
     */
    public function exportToPdf($timetableData, $view, $filename)
    {
        $date = now()->format('Y-m-d');

        // Determine orientation: landscape for weekly, portrait for daily
        $orientation = ($view === 'weekly') ? 'landscape' : 'portrait';

        $pdf = Pdf::loadView('admin.timetable.pdf', [
            'timetableData' => $timetableData,
            'view'          => $view,
            'date'          => $date,
            'generatedAt'   => now()->format('F j, Y h:i A'),
        ]);

        $pdf->setPaper('A4', $orientation);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export timetable to CSV.
     */
    public function exportToCsv($timetableData, $view, $filename)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($timetableData, $view) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Day', 'Class', 'Subject', 'Teacher', 'Start Time', 'End Time', 'Type', 'Location']);

            if ($view === 'weekly' && isset($timetableData['timetable'])) {
                foreach ($timetableData['timetable'] as $day => $schedules) {
                    foreach ($schedules as $schedule) {
                        fputcsv($file, [
                            ucfirst($day),
                            $schedule['class_name'],
                            $schedule['subject'],
                            $schedule['teacher_name'],
                            $schedule['start_time'],
                            $schedule['end_time'],
                            $schedule['type'],
                            $schedule['location'] ?? '-',
                        ]);
                    }
                }
            } elseif ($view === 'daily' && isset($timetableData['schedules'])) {
                foreach ($timetableData['schedules'] as $schedule) {
                    fputcsv($file, [
                        $timetableData['day'] ?? '',
                        $schedule['class_name'],
                        $schedule['subject'],
                        $schedule['teacher_name'] ?? $schedule['teacher'] ?? 'N/A',
                        $schedule['start_time'],
                        $schedule['end_time'],
                        $schedule['type'],
                        $schedule['location'] ?? '-',
                    ]);
                }
            } elseif ($view === 'monthly' && isset($timetableData['schedules'])) {
                foreach ($timetableData['schedules'] as $dateKey => $schedules) {
                    foreach ($schedules as $schedule) {
                        fputcsv($file, [
                            $dateKey,
                            $schedule['class_name'],
                            $schedule['subject'],
                            $schedule['teacher_name'] ?? 'N/A',
                            $schedule['start_time'],
                            $schedule['end_time'],
                            $schedule['type'],
                            $schedule['location'] ?? '-',
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
