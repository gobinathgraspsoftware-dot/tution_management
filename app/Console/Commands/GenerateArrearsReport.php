<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ArrearsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateArrearsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arrears:report
                            {--email= : Email address to send report to}
                            {--format=csv : Output format (csv, json)}
                            {--output= : Output file path}
                            {--days-overdue= : Filter by minimum days overdue}
                            {--min-amount= : Filter by minimum amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate arrears report and optionally email or save to file';

    protected $arrearsService;

    /**
     * Create a new command instance.
     */
    public function __construct(ArrearsService $arrearsService)
    {
        parent::__construct();
        $this->arrearsService = $arrearsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating arrears report...');
        $this->newLine();

        try {
            // Build filters
            $filters = [];

            if ($this->option('days-overdue')) {
                $filters['days_overdue_min'] = (int) $this->option('days-overdue');
            }

            if ($this->option('min-amount')) {
                $filters['amount_min'] = (float) $this->option('min-amount');
            }

            // Get report data
            $reportData = $this->arrearsService->exportArrearsReport($filters);

            // Display summary
            $this->info('ARREARS REPORT SUMMARY');
            $this->info('Generated: ' . $reportData['generated_at']);
            $this->newLine();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Arrears', 'RM ' . number_format($reportData['summary']['total_arrears'], 2)],
                    ['Total Invoices', $reportData['summary']['total_invoices']],
                    ['Total Students', $reportData['summary']['total_students']],
                    ['Overdue Count', $reportData['summary']['overdue_count']],
                ]
            );

            $this->newLine();
            $this->info('ARREARS BY AGE');

            $ageData = [];
            foreach ($reportData['summary']['by_age'] as $age => $data) {
                $ageData[] = [
                    $age . ' days',
                    $data['count'] . ' invoices',
                    'RM ' . number_format($data['amount'], 2),
                ];
            }

            $this->table(['Age Range', 'Count', 'Amount'], $ageData);

            // Handle output
            $format = $this->option('format');
            $outputPath = $this->option('output');

            if ($outputPath || $this->option('email')) {
                $filename = 'arrears_report_' . date('Y-m-d_His');

                if ($format === 'json') {
                    $content = json_encode($reportData, JSON_PRETTY_PRINT);
                    $filename .= '.json';
                } else {
                    $content = $this->generateCsv($reportData);
                    $filename .= '.csv';
                }

                if ($outputPath) {
                    // Save to file
                    $fullPath = $outputPath . '/' . $filename;
                    Storage::put($fullPath, $content);
                    $this->info("Report saved to: {$fullPath}");
                }

                if ($this->option('email')) {
                    // Send email with attachment
                    $this->sendEmailReport($this->option('email'), $reportData, $content, $filename);
                    $this->info("Report emailed to: " . $this->option('email'));
                }
            }

            // Show recent critical arrears
            if (count($reportData['data']) > 0) {
                $this->newLine();
                $this->info('TOP 10 ARREARS (by days overdue):');

                $topArrears = collect($reportData['data'])
                    ->sortByDesc('Days Overdue')
                    ->take(10)
                    ->map(function($row) {
                        return [
                            $row['Invoice #'],
                            $row['Student'],
                            $row['Balance'],
                            $row['Days Overdue'] . ' days',
                            $row['Status'],
                        ];
                    })
                    ->values()
                    ->toArray();

                $this->table(
                    ['Invoice #', 'Student', 'Balance', 'Days Overdue', 'Status'],
                    $topArrears
                );
            }

            $this->newLine();
            $this->info('✅ Arrears report generated successfully.');

            Log::info('Arrears report generated', [
                'total_arrears' => $reportData['summary']['total_arrears'],
                'total_invoices' => $reportData['summary']['total_invoices'],
                'filters' => $filters,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error generating report: ' . $e->getMessage());

            Log::error('Arrears report command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Generate CSV content
     */
    protected function generateCsv(array $reportData): string
    {
        $output = fopen('php://temp', 'r+');

        // Summary section
        fputcsv($output, ['ARREARS REPORT']);
        fputcsv($output, ['Generated:', $reportData['generated_at']]);
        fputcsv($output, []);
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Total Arrears:', 'RM ' . number_format($reportData['summary']['total_arrears'], 2)]);
        fputcsv($output, ['Total Invoices:', $reportData['summary']['total_invoices']]);
        fputcsv($output, ['Total Students:', $reportData['summary']['total_students']]);
        fputcsv($output, ['Overdue Count:', $reportData['summary']['overdue_count']]);
        fputcsv($output, []);

        // Arrears by age
        fputcsv($output, ['ARREARS BY AGE']);
        foreach ($reportData['summary']['by_age'] as $age => $data) {
            fputcsv($output, [$age . ' days', $data['count'] . ' invoices', 'RM ' . number_format($data['amount'], 2)]);
        }
        fputcsv($output, []);

        // Detail section
        if (!empty($reportData['data'])) {
            fputcsv($output, ['DETAIL RECORDS']);
            fputcsv($output, array_keys($reportData['data'][0]));

            foreach ($reportData['data'] as $row) {
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    /**
     * Send email with report attachment
     */
    protected function sendEmailReport(string $email, array $reportData, string $content, string $filename): void
    {
        // Use Laravel's mail system
        \Mail::raw(
            "Please find attached the arrears report generated on {$reportData['generated_at']}.\n\n" .
            "Summary:\n" .
            "- Total Arrears: RM " . number_format($reportData['summary']['total_arrears'], 2) . "\n" .
            "- Total Invoices: {$reportData['summary']['total_invoices']}\n" .
            "- Total Students: {$reportData['summary']['total_students']}\n" .
            "- Overdue Count: {$reportData['summary']['overdue_count']}\n",
            function ($message) use ($email, $content, $filename) {
                $message->to($email)
                    ->subject('Arrears Report - ' . date('d M Y'))
                    ->attachData($content, $filename, [
                        'mime' => str_ends_with($filename, '.json')
                            ? 'application/json'
                            : 'text/csv',
                    ]);
            }
        );
    }
}
