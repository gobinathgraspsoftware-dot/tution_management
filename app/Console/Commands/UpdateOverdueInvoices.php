<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Installment;
use App\Services\InvoiceService;
use App\Services\InstallmentService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-overdue
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of overdue invoices and installments';

    protected $invoiceService;
    protected $installmentService;

    /**
     * Create a new command instance.
     */
    public function __construct(
        InvoiceService $invoiceService,
        InstallmentService $installmentService
    ) {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->installmentService = $installmentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue invoices and installments...');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $today = Carbon::today();

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made.');
            $this->newLine();
        }

        try {
            // Find pending invoices that are past due date
            $overdueInvoices = Invoice::where('status', 'pending')
                ->where('due_date', '<', $today)
                ->get();

            $this->info("Found {$overdueInvoices->count()} invoices to mark as overdue.");

            if ($overdueInvoices->isNotEmpty()) {
                if ($isDryRun) {
                    $this->table(
                        ['Invoice #', 'Student', 'Due Date', 'Amount', 'Balance'],
                        $overdueInvoices->map(function($invoice) {
                            return [
                                $invoice->invoice_number,
                                $invoice->student?->user?->name ?? 'N/A',
                                $invoice->due_date->format('d M Y'),
                                'RM ' . number_format($invoice->total_amount, 2),
                                'RM ' . number_format($invoice->balance, 2),
                            ];
                        })
                    );
                } else {
                    $updatedInvoices = Invoice::where('status', 'pending')
                        ->where('due_date', '<', $today)
                        ->update(['status' => 'overdue']);

                    $this->info("✓ Updated {$updatedInvoices} invoices to overdue status.");
                }
            }

            $this->newLine();

            // Find pending/partial installments that are past due date
            $overdueInstallments = Installment::whereIn('status', ['pending', 'partial'])
                ->where('due_date', '<', $today)
                ->get();

            $this->info("Found {$overdueInstallments->count()} installments to mark as overdue.");

            if ($overdueInstallments->isNotEmpty()) {
                if ($isDryRun) {
                    $this->table(
                        ['Invoice #', 'Installment #', 'Due Date', 'Amount', 'Balance'],
                        $overdueInstallments->map(function($inst) {
                            return [
                                $inst->invoice?->invoice_number ?? 'N/A',
                                $inst->installment_number,
                                $inst->due_date->format('d M Y'),
                                'RM ' . number_format($inst->amount, 2),
                                'RM ' . number_format($inst->balance, 2),
                            ];
                        })
                    );
                } else {
                    $updatedInstallments = Installment::whereIn('status', ['pending', 'partial'])
                        ->where('due_date', '<', $today)
                        ->update(['status' => 'overdue']);

                    $this->info("✓ Updated {$updatedInstallments} installments to overdue status.");
                }
            }

            $this->newLine();

            // Summary statistics
            $this->info('Current Status Summary:');
            $this->table(
                ['Metric', 'Count', 'Amount'],
                [
                    ['Overdue Invoices', Invoice::overdue()->count(), 'RM ' . number_format(Invoice::overdue()->sum(\DB::raw('total_amount - paid_amount')), 2)],
                    ['Overdue Installments', Installment::overdue()->count(), 'RM ' . number_format(Installment::overdue()->sum(\DB::raw('amount - paid_amount')), 2)],
                    ['Pending Invoices', Invoice::pending()->count(), 'RM ' . number_format(Invoice::pending()->sum(\DB::raw('total_amount - paid_amount')), 2)],
                ]
            );

            $this->newLine();
            $this->info('✅ Overdue update process completed.');

            Log::info('Overdue invoices update completed', [
                'dry_run' => $isDryRun,
                'overdue_invoices_found' => $overdueInvoices->count(),
                'overdue_installments_found' => $overdueInstallments->count(),
                'date' => $today->toDateString(),
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error updating overdue status: ' . $e->getMessage());

            Log::error('Overdue update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
