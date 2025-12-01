<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process notification queues every minute
        $schedule->command('notifications:process --channel=all --limit=50')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/notifications.log'));

        // Retry failed notifications every 15 minutes
        $schedule->command('notifications:retry --channel=all --limit=30')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        // Process WhatsApp queue specifically (if needed separately)
        // $schedule->command('whatsapp:process --limit=30')
        //     ->everyMinute()
        //     ->withoutOverlapping();

        // Process Email queue specifically (if needed separately)
        // $schedule->command('email:process --limit=30')
        //     ->everyMinute()
        //     ->withoutOverlapping();

        // Clean old notification logs (keep 30 days)
        $schedule->command('model:prune', [
            '--model' => 'App\\Models\\NotificationLog',
        ])->daily();

        // Clean old queue items (weekly)
        $schedule->call(function () {
            // Clean delivered WhatsApp messages older than 7 days
            \App\Models\WhatsappQueue::where('status', 'delivered')
                ->where('delivered_at', '<', now()->subDays(7))
                ->delete();

            // Clean sent emails older than 7 days
            \App\Models\EmailQueue::where('status', 'sent')
                ->where('sent_at', '<', now()->subDays(7))
                ->delete();

            // Clean failed messages older than 30 days
            \App\Models\WhatsappQueue::where('status', 'failed')
                ->where('failed_at', '<', now()->subDays(30))
                ->delete();

            \App\Models\EmailQueue::where('status', 'failed')
                ->where('failed_at', '<', now()->subDays(30))
                ->delete();
        })->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
