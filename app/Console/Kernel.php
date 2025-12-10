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

        // Send payment reminders on the 10th of each month at 9:00 AM
        $schedule->command('reminders:send-payment --type=initial')
            ->monthlyOn(10, '09:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/payment-reminders.log'))
            ->onSuccess(function () {
                \Log::channel('reminders')->info('Initial payment reminders sent successfully on 10th');
            })
            ->onFailure(function () {
                \Log::channel('reminders')->error('Failed to send initial payment reminders on 10th');
            });

        // Send follow-up reminders on the 18th of each month at 9:00 AM
        $schedule->command('reminders:send-payment --type=followup')
            ->monthlyOn(18, '09:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/payment-reminders.log'))
            ->onSuccess(function () {
                \Log::channel('reminders')->info('Follow-up payment reminders sent successfully on 18th');
            })
            ->onFailure(function () {
                \Log::channel('reminders')->error('Failed to send follow-up payment reminders on 18th');
            });

        // Send final reminders on the 24th of each month at 9:00 AM
        $schedule->command('reminders:send-payment --type=final')
            ->monthlyOn(24, '09:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/payment-reminders.log'))
            ->onSuccess(function () {
                \Log::channel('reminders')->info('Final payment reminders sent successfully on 24th');
            })
            ->onFailure(function () {
                \Log::channel('reminders')->error('Failed to send final payment reminders on 24th');
            });

        // =====================================================================
        // INSTALLMENT REMINDER SCHEDULES
        // Send installment due reminders 3 days before due date
        // =====================================================================

        // Daily check for upcoming installments (sends reminders 3 days before due)
        $schedule->command('reminders:send-installment')
            ->dailyAt('08:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/installment-reminders.log'));

        // =====================================================================
        // ARREARS UPDATE SCHEDULES
        // Daily update of overdue statuses
        // =====================================================================

        // Update overdue invoices and installments daily at midnight
        $schedule->command('arrears:update-status')
            ->dailyAt('00:05')
            ->timezone('Asia/Kuala_Lumpur')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/arrears-update.log'));

        // Send critical arrears alerts to admin at 8 AM
        $schedule->command('arrears:send-critical-alerts')
            ->dailyAt('08:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/arrears-alerts.log'));

        // =====================================================================
        // CLEANUP AND MAINTENANCE SCHEDULES
        // =====================================================================

        // Clean up old reminder logs (older than 90 days)
        $schedule->command('reminders:cleanup --days=90')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->runInBackground();

        // Generate weekly arrears summary report
        $schedule->command('arrears:generate-weekly-report')
            ->weekly()
            ->mondays()
            ->at('07:00')
            ->timezone('Asia/Kuala_Lumpur')
            ->runInBackground()
            ->emailOutputOnFailure(config('mail.admin_email'));

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
