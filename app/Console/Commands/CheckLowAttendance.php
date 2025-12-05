<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceReportService;
use App\Services\NotificationService;
use App\Models\LowAttendanceAlert;
use App\Models\ClassAttendanceSummary;

class CheckLowAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-low
                            {--threshold=75 : Attendance percentage threshold}
                            {--notify : Send notifications to parents}
                            {--dry-run : Preview without creating alerts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for students with low attendance and create alerts';

    protected AttendanceReportService $reportService;
    protected NotificationService $notificationService;

    /**
     * Execute the console command.
     */
    public function handle(AttendanceReportService $reportService, NotificationService $notificationService)
    {
        $this->reportService = $reportService;
        $this->notificationService = $notificationService;

        $threshold = (float) $this->option('threshold');
        $notify = $this->option('notify');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for students with attendance below {$threshold}%...");

        $lowAttendance = ClassAttendanceSummary::with(['student.user', 'student.parent.user', 'class.subject'])
            ->where('attendance_percentage', '<', $threshold)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->get();

        if ($lowAttendance->isEmpty()) {
            $this->info('No students found with low attendance.');
            return Command::SUCCESS;
        }

        $this->info("Found {$lowAttendance->count()} students with low attendance.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No alerts will be created.');
        }

        $table = [];
        $alertsCreated = 0;
        $notificationsSent = 0;

        foreach ($lowAttendance as $summary) {
            // Check if alert already exists for this month
            $existingAlert = LowAttendanceAlert::where('student_id', $summary->student_id)
                ->where('class_id', $summary->class_id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->exists();

            if ($existingAlert) {
                continue;
            }

            $table[] = [
                $summary->student->user->name ?? 'N/A',
                $summary->student->student_id ?? 'N/A',
                $summary->class->name ?? 'N/A',
                $summary->attendance_percentage . '%',
            ];

            if (!$dryRun) {
                // Create alert
                $alert = LowAttendanceAlert::create([
                    'student_id' => $summary->student_id,
                    'class_id' => $summary->class_id,
                    'attendance_percentage' => $summary->attendance_percentage,
                    'threshold' => $threshold,
                    'status' => $notify ? 'sent' : 'pending',
                    'notified_at' => $notify ? now() : null,
                ]);

                $alertsCreated++;

                // Send notification if enabled
                if ($notify && $summary->student->parent && $summary->student->parent->user) {
                    $data = [
                        'student_name' => $summary->student->user->name,
                        'class_name' => $summary->class->name,
                        'subject_name' => $summary->class->subject->name ?? 'N/A',
                        'attendance_percentage' => $summary->attendance_percentage,
                        'threshold' => $threshold,
                    ];

                    try {
                        $this->notificationService->send(
                            $summary->student->parent->user,
                            'low_attendance_alert',
                            $data,
                            ['whatsapp', 'email']
                        );
                        $notificationsSent++;
                    } catch (\Exception $e) {
                        $this->error("Failed to notify parent for {$summary->student->user->name}: {$e->getMessage()}");
                    }
                }
            }
        }

        if (!empty($table)) {
            $this->table(['Student Name', 'Student ID', 'Class', 'Attendance %'], $table);
        }

        $this->info("Alerts created: {$alertsCreated}");

        if ($notify) {
            $this->info("Notifications sent: {$notificationsSent}");
        }

        return Command::SUCCESS;
    }
}
