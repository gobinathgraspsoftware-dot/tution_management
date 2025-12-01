<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;

class ProcessEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'email:process
                            {--limit=50 : Maximum emails to process}
                            {--test= : Send test email to specified address}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending emails in queue';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Test email
        if ($testEmail = $this->option('test')) {
            return $this->sendTestEmail($testEmail);
        }

        if (!config('notification.email.enabled')) {
            $this->error('Email service is disabled.');
            return Command::FAILURE;
        }

        $this->info('Processing Email queue...');
        $this->newLine();

        // Get queue stats before processing
        $statsBefore = $this->emailService->getQueueStats();
        $this->info("Queue before processing:");
        $this->info("  Pending: {$statsBefore['pending']}");

        // Process queue
        $limit = (int) $this->option('limit');
        $results = $this->emailService->processQueue($limit);

        $this->newLine();
        $this->info('Processing complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $results['processed']],
                ['Successful', $results['success']],
                ['Failed', $results['failed']],
            ]
        );

        // Get queue stats after processing
        $statsAfter = $this->emailService->getQueueStats();
        $this->newLine();
        $this->info("Queue after processing:");
        $this->info("  Pending: {$statsAfter['pending']}");
        $this->info("  Sent Today: {$statsAfter['sent']}");
        $this->info("  Failed Today: {$statsAfter['failed']}");

        return Command::SUCCESS;
    }

    /**
     * Send test email
     */
    protected function sendTestEmail(string $email): int
    {
        $this->info("Sending test email to: {$email}");

        $result = $this->emailService->testConnection($email);

        if ($result['success']) {
            $this->info('✓ Test email sent successfully!');
            return Command::SUCCESS;
        }

        $this->error('✗ Failed to send test email');
        $this->error('  Error: ' . ($result['error'] ?? 'Unknown'));
        return Command::FAILURE;
    }
}
