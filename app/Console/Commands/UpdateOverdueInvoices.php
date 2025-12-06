<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvoiceService;
use App\Services\PaymentCycleService;

class UpdateOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-overdue
                            {--show-details : Show details of overdue invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invoice status to overdue for past due invoices';

    protected $invoiceService;
    protected $paymentCycleService;

    public function __construct(
        InvoiceService $invoiceService,
        PaymentCycleService $paymentCycleService
    ) {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->paymentCycleService = $paymentCycleService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for overdue invoices...");

        try {
            // Update overdue status
            $count = $this->invoiceService->updateOverdueInvoices();

            if ($count > 0) {
                $this->warn("Marked {$count} invoices as overdue.");
            } else {
                $this->info("No new overdue invoices found.");
            }

            // Show details if requested
            if ($this->option('show-details')) {
                $this->newLine();
                $this->info("Current Overdue Invoices:");

                $overdueInvoices = $this->paymentCycleService->getOverduePaymentCycles();

                if ($overdueInvoices->count() > 0) {
                    $tableData = $overdueInvoices->map(function($item) {
                        return [
                            $item['invoice_number'],
                            $item['student_name'],
                            $item['student_code'],
                            'RM ' . number_format($item['balance'], 2),
                            $item['due_date']->format('Y-m-d'),
                            $item['days_overdue'] . ' days',
                            $item['reminder_count'],
                        ];
                    })->toArray();

                    $this->table(
                        ['Invoice #', 'Student', 'Code', 'Balance', 'Due Date', 'Days Overdue', 'Reminders'],
                        $tableData
                    );

                    $this->newLine();
                    $totalOverdue = $overdueInvoices->sum('balance');
                    $this->info("Total overdue amount: RM " . number_format($totalOverdue, 2));
                } else {
                    $this->info("No overdue invoices found.");
                }

                // Show students with payment issues
                $this->newLine();
                $this->info("Students with Multiple Overdue Invoices:");

                $studentsWithIssues = $this->paymentCycleService->getStudentsWithPaymentIssues();

                if ($studentsWithIssues->count() > 0) {
                    $tableData = $studentsWithIssues->map(function($item) {
                        return [
                            $item['student_name'],
                            $item['student_code'],
                            $item['overdue_count'],
                            'RM ' . number_format($item['total_overdue'], 2),
                            $item['oldest_days'] . ' days',
                            $item['parent_phone'],
                        ];
                    })->toArray();

                    $this->table(
                        ['Student', 'Code', 'Overdue Count', 'Total', 'Oldest', 'Parent Phone'],
                        $tableData
                    );
                } else {
                    $this->info("No students with multiple overdue invoices.");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error updating overdue invoices: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
