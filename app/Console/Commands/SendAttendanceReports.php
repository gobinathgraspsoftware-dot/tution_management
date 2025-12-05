<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceReportService;
use App\Services\NotificationService;
use App\Models\Student;
use App\Models\ParentModel;

class SendAttendanceReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:send-reports
                            {--type=weekly : Report type (weekly, monthly)}
                            {--channel=email : Notification channel (email, whatsapp, all)}
                            {--student= : Send for specific student ID}
                            {--dry-run : Preview without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send attendance reports to parents';

    protected AttendanceReportService $reportService;
    protected NotificationService $notificationService;

    /**
     * Execute the console command.
     */
    public function handle(AttendanceReportService $reportService, NotificationService $notificationService)
    {
        $this->reportService = $reportService;
        $this->notificationService = $notificationService;

        $type = $this->option('type');
        $channel = $this->option('channel');
        $studentId = $this->option('student');
        $dryRun = $this->option('dry-run');

        $this->info("Sending {$type} attendance reports via {$channel}...");

        // Determine date range
        $dateRange = $this->getDateRange($type);

        $this->info("Date range: {$dateRange['from']} to {$dateRange['to']}");

        // Get students to report
        $query = Student::with(['user', 'parent.user', 'enrollments.class'])
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->whereHas('parent.user', fn($q) => $q->where('status', 'active'));

        if ($studentId) {
            $query->where('id', $studentId);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            $this->warn('No active students with parents found.');
            return Command::SUCCESS;
        }

        $this->info("Processing {$students->count()} students...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No reports will be sent.');
        }

        $successCount = 0;
        $failCount = 0;

        $progressBar = $this->output->createProgressBar($students->count());
        $progressBar->start();

        foreach ($students as $student) {
            try {
                $reportData = $this->reportService->generateStudentReport(
                    $student->id,
                    $dateRange['from'],
                    $dateRange['to']
                );

                if ($reportData['summary']['total_sessions'] == 0) {
                    $progressBar->advance();
                    continue;
                }

                $data = [
                    'student_name' => $student->user->name,
                    'parent_name' => $student->parent->user->name,
                    'date_from' => $dateRange['from'],
                    'date_to' => $dateRange['to'],
                    'report_type' => ucfirst($type),
                    'total_sessions' => $reportData['summary']['total_sessions'],
                    'present_count' => $reportData['summary']['present'],
                    'absent_count' => $reportData['summary']['absent'],
                    'late_count' => $reportData['summary']['late'],
                    'attendance_percentage' => $reportData['summary']['percentage'],
                ];

                if (!$dryRun) {
                    $channels = $channel === 'all' ? ['email', 'whatsapp'] : [$channel];

                    $this->notificationService->send(
                        $student->parent->user,
                        'attendance_report',
                        $data,
                        $channels
                    );
                }

                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $this->newLine();
                $this->error("Failed for {$student->user->name}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Reports processed: {$successCount} successful, {$failCount} failed.");

        return Command::SUCCESS;
    }

    /**
     * Get date range based on report type
     */
    protected function getDateRange(string $type): array
    {
        return match($type) {
            'weekly' => [
                'from' => now()->subWeek()->startOfWeek()->format('Y-m-d'),
                'to' => now()->subWeek()->endOfWeek()->format('Y-m-d'),
            ],
            'monthly' => [
                'from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'to' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            default => [
                'from' => now()->subWeek()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
        };
    }
}
