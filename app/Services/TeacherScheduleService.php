<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\ClassModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherScheduleService
{
    /**
     * Get teacher's schedule based on view type.
     */
    public function getTeacherSchedule(int $teacherId, string $view, Carbon $date): array
    {
        switch ($view) {
            case 'daily':
                return $this->getDailySchedule($teacherId, $date);
            case 'monthly':
                return $this->getMonthlySchedule($teacherId, $date->month, $date->year);
            case 'weekly':
            default:
                return $this->getWeeklySchedule($teacherId, $date->startOfWeek());
        }
    }

    /**
     * Get daily schedule.
     */
    public function getDailySchedule(int $teacherId, Carbon $date): array
    {
        $dayOfWeek = strtolower($date->format('l'));

        $schedules = ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->active();
        })
        ->where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->with(['class.subject', 'class.enrollments'])
        ->orderBy('start_time')
        ->get();

        // Get actual sessions for this date
        $sessions = ClassSession::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->whereDate('session_date', $date)
        ->with(['class.subject', 'attendance'])
        ->orderBy('start_time')
        ->get();

        return [
            'date' => $date->format('Y-m-d'),
            'day_name' => $date->format('l'),
            'schedules' => $schedules,
            'sessions' => $sessions,
        ];
    }

    /**
     * Get weekly schedule.
     */
    public function getWeeklySchedule(int $teacherId, Carbon $startDate): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $weekSchedule = [];

        $schedules = ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->active();
        })
        ->where('is_active', true)
        ->with(['class.subject'])
        ->orderBy('start_time')
        ->get();

        foreach ($days as $index => $day) {
            $currentDate = $startDate->copy()->addDays($index);

            $weekSchedule[$day] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => ucfirst($day),
                'is_today' => $currentDate->isToday(),
                'schedules' => $schedules->where('day_of_week', $day)->values(),
            ];
        }

        return [
            'week_start' => $startDate->format('Y-m-d'),
            'week_end' => $startDate->copy()->endOfWeek()->format('Y-m-d'),
            'schedule' => $weekSchedule,
        ];
    }

    /**
     * Get monthly schedule.
     */
    public function getMonthlySchedule(int $teacherId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all schedules
        $schedules = ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->active();
        })
        ->where('is_active', true)
        ->with(['class.subject'])
        ->get();

        // Get actual sessions
        $sessions = ClassSession::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->whereBetween('session_date', [$startDate, $endDate])
        ->with(['class.subject'])
        ->get();

        // Build calendar data
        $calendarData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayOfWeek = strtolower($currentDate->format('l'));
            $dateKey = $currentDate->format('Y-m-d');

            $daySchedules = $schedules->where('day_of_week', $dayOfWeek)->values();
            $daySessions = $sessions->where('session_date', $dateKey)->values();

            $calendarData[$dateKey] = [
                'date' => $dateKey,
                'day' => $currentDate->day,
                'day_name' => $currentDate->format('D'),
                'is_today' => $currentDate->isToday(),
                'is_weekend' => $currentDate->isWeekend(),
                'schedules' => $daySchedules,
                'sessions' => $daySessions,
                'has_classes' => $daySchedules->count() > 0,
            ];

            $currentDate->addDay();
        }

        return [
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            'calendar' => $calendarData,
            'total_scheduled_classes' => $schedules->count(),
        ];
    }

    /**
     * Get today's sessions.
     */
    public function getTodaySessions(int $teacherId): Collection
    {
        $dayOfWeek = strtolower(now()->format('l'));

        return ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->active();
        })
        ->where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->with(['class.subject', 'class.enrollments'])
        ->orderBy('start_time')
        ->get();
    }

    /**
     * Get schedule statistics.
     */
    public function getScheduleStatistics(int $teacherId): array
    {
        $schedules = ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->active();
        })
        ->where('is_active', true)
        ->get();

        // Calculate total hours per week
        $totalMinutes = $schedules->sum(function ($schedule) {
            $start = Carbon::parse($schedule->start_time);
            $end = Carbon::parse($schedule->end_time);
            return $end->diffInMinutes($start);
        });

        $totalHours = round($totalMinutes / 60, 1);

        // Classes per day
        $classesPerDay = $schedules->groupBy('day_of_week')
            ->map(fn($group) => $group->count());

        // Busiest day
        $busiestDay = $classesPerDay->sortDesc()->keys()->first();

        return [
            'total_weekly_classes' => $schedules->count(),
            'total_weekly_hours' => $totalHours,
            'classes_per_day' => $classesPerDay,
            'busiest_day' => ucfirst($busiestDay ?? 'N/A'),
            'average_classes_per_day' => $schedules->count() > 0
                ? round($schedules->count() / 7, 1)
                : 0,
        ];
    }

    /**
     * Check for schedule conflicts.
     */
    public function checkScheduleConflict(int $teacherId, string $dayOfWeek, string $startTime, string $endTime, ?int $excludeScheduleId = null): Collection
    {
        $query = ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })
        ->where('day_of_week', $dayOfWeek)
        ->where('is_active', true)
        ->where(function ($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function ($q2) use ($startTime, $endTime) {
                  $q2->where('start_time', '<=', $startTime)
                     ->where('end_time', '>=', $endTime);
              });
        });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->with('class')->get();
    }

    /**
     * Export schedule to CSV.
     */
    public function exportToCsv(array $scheduleData, Teacher $teacher, string $view, Carbon $date)
    {
        $filename = 'schedule_' . $teacher->teacher_id . '_' . $date->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($scheduleData, $view) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Day', 'Date', 'Time', 'Class', 'Subject', 'Location']);

            if ($view === 'weekly' && isset($scheduleData['schedule'])) {
                foreach ($scheduleData['schedule'] as $day => $data) {
                    foreach ($data['schedules'] as $schedule) {
                        fputcsv($file, [
                            ucfirst($day),
                            $data['date'],
                            $schedule->start_time . ' - ' . $schedule->end_time,
                            $schedule->class->name,
                            $schedule->class->subject->name ?? 'N/A',
                            $schedule->class->location ?? 'Online',
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export schedule to PDF.
     */
    public function exportToPdf(array $scheduleData, Teacher $teacher, string $view, Carbon $date)
    {
        // For PDF export, you would typically use a package like DomPDF or TCPDF
        // This is a simplified version that returns HTML for printing

        $html = view('teacher.schedule.print', compact('scheduleData', 'teacher', 'view', 'date'))->render();

        return response($html)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Generate iCal feed for calendar sync.
     */
    public function generateICalFeed(int $teacherId, Carbon $startDate, Carbon $endDate): string
    {
        $schedules = ClassSchedule::whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->active();
        })
        ->where('is_active', true)
        ->with('class.subject')
        ->get();

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Tuition Management System//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayOfWeek = strtolower($currentDate->format('l'));
            $daySchedules = $schedules->where('day_of_week', $dayOfWeek);

            foreach ($daySchedules as $schedule) {
                $uid = md5($schedule->id . $currentDate->format('Y-m-d'));
                $startDateTime = $currentDate->format('Ymd') . 'T' . str_replace(':', '', $schedule->start_time) . '00';
                $endDateTime = $currentDate->format('Ymd') . 'T' . str_replace(':', '', $schedule->end_time) . '00';

                $ical .= "BEGIN:VEVENT\r\n";
                $ical .= "UID:{$uid}\r\n";
                $ical .= "DTSTART:{$startDateTime}\r\n";
                $ical .= "DTEND:{$endDateTime}\r\n";
                $ical .= "SUMMARY:{$schedule->class->name}\r\n";
                $ical .= "DESCRIPTION:Subject: {$schedule->class->subject->name}\r\n";
                $ical .= "LOCATION:{$schedule->class->location}\r\n";
                $ical .= "END:VEVENT\r\n";
            }

            $currentDate->addDay();
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }
}
