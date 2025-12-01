<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsappService;
use App\Services\EmailService;
use App\Services\SmsService;

class ProcessNotificationQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:process
                            {--channel=all : Channel to process (whatsapp, email, sms, all)}
                            {--limit=50 : Maximum messages to process per channel}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending notification queues';

    protected $whatsappService;
    protected $emailService;
    protected $smsService;

    public function __construct(
        WhatsappService $whatsappService,
        EmailService $emailService,
        SmsService $smsService
    ) {
        parent::__construct();
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $channel = $this->option('channel');
        $limit = (int) $this->option('limit');

        $this->info('Starting notification queue processing...');
        $this->newLine();

        $totalResults = [
            'processed' => 0,
            'success' => 0,
            'failed' => 0,
        ];

        // Process WhatsApp
        if ($channel === 'all' || $channel === 'whatsapp') {
            $this->processWhatsapp($limit, $totalResults);
        }

        // Process Email
        if ($channel === 'all' || $channel === 'email') {
            $this->processEmail($limit, $totalResults);
        }

        // Process SMS
        if ($channel === 'all' || $channel === 'sms') {
            $this->processSms($limit, $totalResults);
        }

        $this->newLine();
        $this->info('Processing complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Processed', $totalResults['processed']],
                ['Successful', $totalResults['success']],
                ['Failed', $totalResults['failed']],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Process WhatsApp queue
     */
    protected function processWhatsapp(int $limit, array &$totalResults): void
    {
        $this->info('Processing WhatsApp queue...');

        if (!config('notification.whatsapp.enabled')) {
            $this->warn('WhatsApp is disabled. Skipping.');
            return;
        }

        $results = $this->whatsappService->processQueue($limit);

        $this->info("  Processed: {$results['processed']}");
        $this->info("  Success: {$results['success']}");
        $this->info("  Failed: {$results['failed']}");

        $totalResults['processed'] += $results['processed'];
        $totalResults['success'] += $results['success'];
        $totalResults['failed'] += $results['failed'];
    }

    /**
     * Process Email queue
     */
    protected function processEmail(int $limit, array &$totalResults): void
    {
        $this->info('Processing Email queue...');

        if (!config('notification.email.enabled')) {
            $this->warn('Email is disabled. Skipping.');
            return;
        }

        $results = $this->emailService->processQueue($limit);

        $this->info("  Processed: {$results['processed']}");
        $this->info("  Success: {$results['success']}");
        $this->info("  Failed: {$results['failed']}");

        $totalResults['processed'] += $results['processed'];
        $totalResults['success'] += $results['success'];
        $totalResults['failed'] += $results['failed'];
    }

    /**
     * Process SMS queue
     */
    protected function processSms(int $limit, array &$totalResults): void
    {
        $this->info('Processing SMS queue...');

        if (!config('notification.sms.enabled')) {
            $this->warn('SMS is disabled. Skipping.');
            return;
        }

        $results = $this->smsService->processPending($limit);

        $this->info("  Processed: {$results['processed']}");
        $this->info("  Success: {$results['success']}");
        $this->info("  Failed: {$results['failed']}");

        $totalResults['processed'] += $results['processed'];
        $totalResults['success'] += $results['success'];
        $totalResults['failed'] += $results['failed'];
    }
}
