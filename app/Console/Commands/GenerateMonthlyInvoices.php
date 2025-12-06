<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvoiceService;
use Carbon\Carbon;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate
                            {--month= : The month to generate invoices for (Y-m format)}
                            {--dry-run : Run without actually creating invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active enrollments';

    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month')
            ? Carbon::parse($this->option('month'))
            : Carbon::now();

        $isDryRun = $this->option('dry-run');

        $this->info("Generating invoices for {$month->format('F Y')}...");

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No invoices will be created');
        }

        try {
            if ($isDryRun) {
                // Just show what would be generated
                $enrollments = \App\Models\Enrollment::active()
                    ->with(['student.user', 'package'])
                    ->whereHas('student', function($q) {
                        $q->where('status', 'approved');
                    })
                    ->get();

                $wouldGenerate = 0;
                $wouldSkip = 0;

                foreach ($enrollments as $enrollment) {
                    if ($this->invoiceService->shouldGenerateInvoice($enrollment, $month)) {
                        $this->line("Would generate invoice for: {$enrollment->student->user->name} - {$enrollment->package->name}");
                        $wouldGenerate++;
                    } else {
                        $wouldSkip++;
                    }
                }

                $this->info("\nDry run complete:");
                $this->info("Would generate: {$wouldGenerate} invoices");
                $this->info("Would skip: {$wouldSkip} (already exist or not due)");

            } else {
                $results = $this->invoiceService->generateMonthlyInvoices($month);

                $this->info("\nInvoice generation complete:");
                $this->info("Generated: {$results['generated']}");
                $this->info("Skipped: {$results['skipped']}");

                if ($results['failed'] > 0) {
                    $this->error("Failed: {$results['failed']}");
                    foreach ($results['errors'] as $error) {
                        $this->error("  - Enrollment #{$error['enrollment_id']}: {$error['error']}");
                    }
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error generating invoices: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
