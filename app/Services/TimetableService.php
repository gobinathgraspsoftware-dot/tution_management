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
     * Get filtered timetable — supports class AND/OR teacher combined filtering.
     *
     * @param  int|null  $classId
     * @param  int|null  $teacherId
     * @param  string    $view      daily|weekly|monthly
     * @param  mixed     $date
     * @return array
     */
    public function getFilteredTimetable($classId = null, $teacherId = null, $view = 'weekly', $date = null)
    {
        // If no filters at all, return everything
        if (!$classId && !$teacherId) {
            return $this->getAllClassesTimetable($view, $date);
        }

        // If only one filter, delegate to existing methods
        if ($classId && !$teacherId) {
            return $this->getClassTimetable($classId, $view, $date);
        }
        if ($teacherId && !$classId) {
            return $this->getTeacherTimetable($teacherId, $view, $date);
        }

        // Both filters active — combined AND logic
        $date = $date ? Carbon::parse($date) : now();

        $schedules = ClassSchedule::with(['class.subject', 'class.teacher.user'])
            ->where('class_id', $classId)
            ->whereHas('class', function ($q) use ($teacherId) {
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
            'start_date'  => $startOfWeek->format('Y-m-d'),
            'end_date'    => $endOfWeek->format('Y-m-d'),
            'week_number' => $date->weekOfYear,
            'timetable'   => $timetable,
        ];
    }

    /**
     * Format monthly view.
     * FIX: Times as plain strings, location from class.
     */
    protected function formatMonthlyView($schedules, $date)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth   = $date->copy()->endOfMonth();

        $monthSchedules = [];
        $current = $startOfMonth->copy();

        while ($current <= $endOfMonth) {
            $dayOfWeek = strtolower($current->format('l'));

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
                    'type'         => $schedule->class->type,
                    'color'        => $this->getSubjectColor($schedule->class->subject_id),
                ];
            });

            $monthSchedules[$current->format('Y-m-d')] = $daySchedules->values();
            $current->addDay();
        }

        return [
            'month'      => $date->format('F Y'),
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date'   => $endOfMonth->format('Y-m-d'),
            'schedules'  => $monthSchedules,
        ];
    }

    /**
     * Safely format a time value to H:i string.
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
     * Export timetable to PDF.
     */
    public function exportToPdf($timetableData, $view, $filename)
    {
        return response()->json([
            'message' => 'PDF export feature - to be implemented with DomPDF',
            'data'    => $timetableData,
        ]);
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
                            $schedule['location'] ?? '',
                        ]);
                    }
                }
            } elseif ($view === 'daily' && isset($timetableData['schedules'])) {
                foreach ($timetableData['schedules'] as $schedule) {
                    fputcsv($file, [
                        $timetableData['day'],
                        $schedule['class_name'],
                        $schedule['subject'],
                        $schedule['teacher'] ?? $schedule['teacher_name'] ?? 'N/A',
                        $schedule['start_time'],
                        $schedule['end_time'],
                        $schedule['type'],
                        $schedule['location'] ?? '',
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
                            '',
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate iCalendar export.
     */
    public function generateICalendar($classes, $type, $id)
    {
        $ical  = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Arena Matriks Edu Group//Timetable//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        foreach ($classes as $class) {
            if (!$class || !$class->subject) continue;

            $schedules = $class->schedules()->where('is_active', true)->get();
            foreach ($schedules as $schedule) {
                $ical .= "BEGIN:VEVENT\r\n";
                $ical .= "SUMMARY:" . $class->name . " - " . $class->subject->name . "\r\n";
                $ical .= "DESCRIPTION:Teacher: " . ($class->teacher->user->name ?? 'N/A') . "\r\n";
                if ($class->location) {
                    $ical .= "LOCATION:" . $class->location . "\r\n";
                }
                $ical .= "RRULE:FREQ=WEEKLY;BYDAY=" . strtoupper(substr($schedule->day_of_week, 0, 2)) . "\r\n";
                $ical .= "END:VEVENT\r\n";
            }
        }

        $ical .= "END:VCALENDAR\r\n";
        return $ical;
    }
}
