<?php

namespace App\Console\Commands;

use App\Services\StockAlertService;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock
                            {--notify : Send notifications to admins}
                            {--summary : Display summary only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock items and optionally send notifications';

    protected $stockAlertService;

    /**
     * Create a new command instance.
     */
    public function __construct(StockAlertService $stockAlertService)
    {
        parent::__construct();
        $this->stockAlertService = $stockAlertService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking inventory stock levels...');
        $this->newLine();

        $stockSummary = $this->stockAlertService->getStockSummary();

        // Display summary
        $this->info('=== Stock Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Items', $stockSummary['total_items']],
                ['Active Items', $stockSummary['active_items']],
                ['Healthy Stock', $stockSummary['healthy_stock']],
                ['Low Stock Items', $stockSummary['low_stock_items']],
                ['Out of Stock Items', $stockSummary['out_of_stock_items']],
                ['Critical Items', $stockSummary['critical_items']],
            ]
        );

        if ($this->option('summary')) {
            return 0;
        }

        // Display low stock items
        $lowStockItems = $this->stockAlertService->getLowStockItems();
        if ($lowStockItems->isNotEmpty()) {
            $this->newLine();
            $this->warn('=== Low Stock Items ===');
            $this->table(
                ['ID', 'Name', 'SKU', 'Category', 'Current Stock', 'Reorder Level'],
                $lowStockItems->map(function ($item) {
                    return [
                        $item->id,
                        $item->name,
                        $item->sku,
                        $item->category->name ?? 'N/A',
                        $item->current_stock,
                        $item->reorder_level,
                    ];
                })->toArray()
            );
        }

        // Display out of stock items
        $outOfStockItems = $this->stockAlertService->getOutOfStockItems();
        if ($outOfStockItems->isNotEmpty()) {
            $this->newLine();
            $this->error('=== Out of Stock Items ===');
            $this->table(
                ['ID', 'Name', 'SKU', 'Category'],
                $outOfStockItems->map(function ($item) {
                    return [
                        $item->id,
                        $item->name,
                        $item->sku,
                        $item->category->name ?? 'N/A',
                    ];
                })->toArray()
            );
        }

        // Send notifications if requested
        if ($this->option('notify')) {
            $this->newLine();
            $this->info('Sending notifications...');

            $results = $this->stockAlertService->checkAndSendAlerts();

            $this->info("Notifications sent: {$results['notifications_sent']}");
        }

        // Display reorder suggestions
        $reorderSuggestions = $this->stockAlertService->getReorderSuggestions();
        if (!empty($reorderSuggestions)) {
            $this->newLine();
            $this->info('=== Reorder Suggestions ===');
            $this->table(
                ['Item', 'Current Stock', 'Suggested Qty', 'Est. Cost', 'Priority'],
                array_map(function ($suggestion) {
                    return [
                        $suggestion['item']->name,
                        $suggestion['current_stock'],
                        $suggestion['suggested_quantity'],
                        'RM ' . number_format($suggestion['estimated_cost'], 2),
                        strtoupper($suggestion['priority']),
                    ];
                }, array_slice($reorderSuggestions, 0, 10))
            );
        }

        $this->newLine();
        $this->info('Stock check completed.');

        return 0;
    }
}
