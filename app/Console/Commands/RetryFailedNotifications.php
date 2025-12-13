<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WhatsAppService;
use App\Services\EmailService;
use App\Models\WhatsappQueue;
use App\Models\EmailQueue;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:retry
                            {--channel=all : Channel to retry (whatsapp, email, all)}
                            {--limit=50 : Maximum messages to retry}
                            {--force : Force retry even if max attempts exceeded}';

    /**
     * The console command description.
     */
    protected $description = 'Retry failed notification messages';

    protected $whatsappService;
    protected $emailService;

    public function __construct(
        WhatsAppService $whatsappService,
        EmailService $emailService
    ) {
        parent::__construct();
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $channel = $this->option('channel');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $this->info('Retrying failed notifications...');
        $this->newLine();

        $totalRetried = 0;

        // Retry WhatsApp
        if ($channel === 'all' || $channel === 'whatsapp') {
            $count = $this->retryWhatsapp($limit, $force);
            $this->info("WhatsApp: {$count} messages queued for retry");
            $totalRetried += $count;
        }

        // Retry Email
        if ($channel === 'all' || $channel === 'email') {
            $count = $this->retryEmail($limit, $force);
            $this->info("Email: {$count} messages queued for retry");
            $totalRetried += $count;
        }

        $this->newLine();
        $this->info("Total: {$totalRetried} messages queued for retry");

        if ($totalRetried > 0) {
            $this->newLine();
            $this->info('Run "php artisan notifications:process" to process the retry queue.');
        }

        return Command::SUCCESS;
    }

    /**
     * Retry failed WhatsApp messages
     */
    protected function retryWhatsapp(int $limit, bool $force): int
    {
        $query = WhatsappQueue::where('status', 'failed');

        if (!$force) {
            $maxAttempts = config('notification.whatsapp.max_attempts', 3);
            $query->where('attempts', '<', $maxAttempts + 2);
        }

        return $query->limit($limit)->update([
            'status' => 'pending',
            'error_message' => null,
            'failed_at' => null,
        ]);
    }

    /**
     * Retry failed email messages
     */
    protected function retryEmail(int $limit, bool $force): int
    {
        $query = EmailQueue::where('status', 'failed');

        if (!$force) {
            $maxAttempts = config('notification.email.max_attempts', 3);
            $query->where('attempts', '<', $maxAttempts + 2);
        }

        return $query->limit($limit)->update([
            'status' => 'pending',
            'error_message' => null,
            'failed_at' => null,
        ]);
    }
}
