<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;

class ProcessWhatsappQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'whatsapp:process
                            {--limit=50 : Maximum messages to process}
                            {--status : Check WhatsApp connection status}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending WhatsApp messages in queue';

    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check status only
        if ($this->option('status')) {
            return $this->checkStatus();
        }

        if (!config('notification.whatsapp.enabled')) {
            $this->error('WhatsApp service is disabled.');
            return Command::FAILURE;
        }

        $this->info('Processing WhatsApp queue...');
        $this->newLine();

        // Check connection first
        $status = $this->whatsappService->checkStatus();
        if (!$status['connected']) {
            $this->error('WhatsApp is not connected: ' . ($status['error'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info('WhatsApp connected. Phone: ' . ($status['phone'] ?? 'Unknown'));
        $this->newLine();

        // Get queue stats before processing
        $statsBefore = $this->whatsappService->getQueueStats();
        $this->info("Queue before processing:");
        $this->info("  Pending: {$statsBefore['pending']}");

        // Process queue
        $limit = (int) $this->option('limit');
        $results = $this->whatsappService->processQueue($limit);

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
        $statsAfter = $this->whatsappService->getQueueStats();
        $this->newLine();
        $this->info("Queue after processing:");
        $this->info("  Pending: {$statsAfter['pending']}");
        $this->info("  Sent Today: {$statsAfter['sent']}");
        $this->info("  Failed Today: {$statsAfter['failed']}");

        return Command::SUCCESS;
    }

    /**
     * Check WhatsApp connection status
     */
    protected function checkStatus(): int
    {
        $this->info('Checking WhatsApp connection status...');
        $this->newLine();

        $status = $this->whatsappService->checkStatus();

        if ($status['connected']) {
            $this->info('✓ WhatsApp is connected');
            $this->info("  Phone: {$status['phone']}");
        } else {
            $this->error('✗ WhatsApp is not connected');
            $this->error("  Error: " . ($status['error'] ?? 'Unknown'));
        }

        $this->newLine();

        // Show queue stats
        $stats = $this->whatsappService->getQueueStats();
        $this->info('Queue Statistics:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Pending', $stats['pending']],
                ['Sent (Today)', $stats['sent']],
                ['Failed (Today)', $stats['failed']],
                ['Delivered (Today)', $stats['delivered']],
            ]
        );

        return $status['connected'] ? Command::SUCCESS : Command::FAILURE;
    }
}
