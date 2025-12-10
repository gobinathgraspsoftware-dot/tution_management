<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentReminderService;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send
                            {--type= : Specific reminder type (first, second, final, overdue)}
                            {--force : Send reminders even if disabled in settings}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled payment reminders via WhatsApp/Email';

    protected $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(PaymentReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting payment reminder process...');
        $this->newLine();

        // Check if reminders are enabled
        if (!$this->option('force') && !Setting::get('payment_reminder_enabled', true)) {
            $this->warn('Payment reminders are disabled in settings. Use --force to override.');
            return Command::SUCCESS;
        }

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No reminders will actually be sent.');
            $this->newLine();
        }

        try {
            // Get today's date info
            $today = now();
            $dayOfMonth = $today->day;

            $this->info("Date: {$today->format('d M Y')} (Day {$dayOfMonth} of month)");
            $this->newLine();

            // Check if today is a reminder day (10th, 18th, or 24th)
            $reminderDays = [10 => 'first', 18 => 'second', 24 => 'final'];

            if (isset($reminderDays[$dayOfMonth])) {
                $type = $reminderDays[$dayOfMonth];
                $this->info("📅 Today is reminder day for: " . strtoupper($type) . " reminders");

                // Schedule new reminders for this month
                $this->info('Scheduling reminders for current month...');
                $scheduleResults = $this->reminderService->scheduleMonthlyReminders($today);

                $this->table(
                    ['Action', 'Count'],
                    [
                        ['Scheduled', $scheduleResults['scheduled']],
                        ['Skipped', $scheduleResults['skipped']],
                        ['Errors', count($scheduleResults['errors'])],
                    ]
                );
                $this->newLine();
            }

            // Send due reminders
            $this->info('Sending due reminders...');

            if ($isDryRun) {
                $dueCount = \App\Models\PaymentReminder::dueToSend()
                    ->whereHas('invoice', function($q) {
                        $q->unpaid();
                    })
                    ->count();

                $this->info("Would send {$dueCount} reminders.");
            } else {
                $results = $this->reminderService->sendDueReminders();

                $this->table(
                    ['Action', 'Count'],
                    [
                        ['Sent', $results['sent']],
                        ['Failed', $results['failed']],
                    ]
                );

                if (!empty($results['errors'])) {
                    $this->newLine();
                    $this->error('Errors:');
                    foreach ($results['errors'] as $error) {
                        $this->line("  - Reminder #{$error['reminder_id']}: {$error['error']}");
                    }
                }
            }

            // Send overdue reminders (if enabled and not recently sent)
            if (Setting::get('auto_overdue_reminder', true)) {
                $this->newLine();
                $this->info('Processing overdue reminders...');

                if ($isDryRun) {
                    $overdueCount = \App\Models\Invoice::overdue()
                        ->whereDoesntHave('reminders', function($q) {
                            $q->where('reminder_type', 'overdue')
                              ->where('sent_at', '>', now()->subDays(7));
                        })
                        ->count();

                    $this->info("Would send {$overdueCount} overdue reminders.");
                } else {
                    $overdueResults = $this->reminderService->sendOverdueReminders();

                    $this->table(
                        ['Action', 'Count'],
                        [
                            ['Sent', $overdueResults['sent']],
                            ['Skipped', $overdueResults['skipped']],
                        ]
                    );
                }
            }

            // Retry failed reminders
            $this->newLine();
            $this->info('Retrying failed reminders...');

            if ($isDryRun) {
                $failedCount = \App\Models\PaymentReminder::needsRetry()->count();
                $this->info("Would retry {$failedCount} failed reminders.");
            } else {
                $retryResults = $this->reminderService->retryFailedReminders();
                $this->info("Retried {$retryResults['retried']} reminders.");
            }

            $this->newLine();
            $this->info('✅ Payment reminder process completed successfully.');

            Log::info('Payment reminder command completed', [
                'dry_run' => $isDryRun,
                'date' => $today->toDateString(),
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing reminders: ' . $e->getMessage());

            Log::error('Payment reminder command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
