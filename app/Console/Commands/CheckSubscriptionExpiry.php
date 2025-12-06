<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SubscriptionService;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiry
                            {--days=7 : Number of days ahead to check}
                            {--send-notifications : Send notifications for expiring subscriptions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring and expired subscriptions and send alerts';

    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = (int) $this->option('days');
        $sendNotifications = $this->option('send-notifications');

        $this->info("Checking subscription expiry (next {$daysAhead} days)...");

        try {
            // Get summary
            $summary = $this->subscriptionService->getSubscriptionSummary();

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Active Enrollments', $summary['total_active']],
                    ['Expiring Today', $summary['expiring_today']],
                    ['Expiring This Week', $summary['expiring_this_week']],
                    ['Expiring This Month', $summary['expiring_this_month']],
                    ['Expired (Not Renewed)', $summary['expired_not_renewed']],
                    ['Suspended', $summary['suspended']],
                    ['Cancelled', $summary['cancelled']],
                ]
            );

            // Get expiring enrollments
            $expiring = $this->subscriptionService->getExpiringEnrollments($daysAhead);

            if ($expiring->count() > 0) {
                $this->newLine();
                $this->info("Enrollments expiring in next {$daysAhead} days:");

                $tableData = $expiring->map(function($item) {
                    return [
                        $item['student_name'],
                        $item['student_code'],
                        $item['package_name'],
                        $item['end_date']->format('Y-m-d'),
                        $item['days_until_expiry'],
                        $item['urgency'],
                    ];
                })->toArray();

                $this->table(
                    ['Student', 'Code', 'Package', 'Expiry Date', 'Days Left', 'Urgency'],
                    $tableData
                );
            } else {
                $this->info("No enrollments expiring in the next {$daysAhead} days.");
            }

            // Process alerts if requested
            if ($sendNotifications) {
                $this->newLine();
                $this->info("Processing expiry alerts...");

                $results = $this->subscriptionService->processExpiryAlerts();

                $this->info("Expiry alerts processed:");
                $this->info("  - 7-day warnings: {$results['expiring_7_days']}");
                $this->info("  - 3-day critical: {$results['expiring_3_days']}");
                $this->info("  - Newly expired: {$results['newly_expired']}");
                $this->info("  - Notifications sent: {$results['notifications_sent']}");

                if (!empty($results['errors'])) {
                    $this->error("Errors:");
                    foreach ($results['errors'] as $error) {
                        $this->error("  - {$error['enrollment_id']}: {$error['error']}");
                    }
                }
            }

            // Mark expired enrollments
            $markedExpired = $this->subscriptionService->markExpiredEnrollments();
            if ($markedExpired > 0) {
                $this->warn("Marked {$markedExpired} enrollments as expired.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error checking subscriptions: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
