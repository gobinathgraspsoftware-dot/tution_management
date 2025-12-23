<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:clear-expired 
                            {--hours=24 : Clear OTPs older than this many hours}
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired password reset OTPs from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $force = $this->option('force');

        $this->info("Clearing OTPs older than {$hours} hours...");

        // Count expired OTPs
        $count = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours($hours))
            ->count();

        if ($count === 0) {
            $this->info('No expired OTPs found.');
            return self::SUCCESS;
        }

        $this->warn("Found {$count} expired OTP(s)");

        // Ask for confirmation unless force flag is set
        if (!$force && !$this->confirm('Do you want to delete these OTPs?', true)) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        // Delete expired OTPs
        $deleted = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours($hours))
            ->delete();

        $this->info("Successfully deleted {$deleted} expired OTP(s)");

        // Log the cleanup activity
        activity()
            ->withProperties([
                'deleted_count' => $deleted,
                'hours' => $hours,
            ])
            ->log("Cleared {$deleted} expired password reset OTPs");

        return self::SUCCESS;
    }
}
