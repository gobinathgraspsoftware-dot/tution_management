<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockAlertService
{
    /**
     * Check and send low stock alerts
     */
    public function checkAndSendAlerts(): array
    {
        $lowStockItems = $this->getLowStockItems();
        $outOfStockItems = $this->getOutOfStockItems();

        $results = [
            'low_stock_count' => $lowStockItems->count(),
            'out_of_stock_count' => $outOfStockItems->count(),
            'notifications_sent' => 0,
            'items' => [],
        ];

        if ($lowStockItems->isEmpty() && $outOfStockItems->isEmpty()) {
            return $results;
        }

        // Get users to notify (admins and staff with inventory permissions)
        $usersToNotify = $this->getUsersToNotify();

        if ($usersToNotify->isEmpty()) {
            Log::warning('No users to notify for low stock alerts');
            return $results;
        }

        // Send notifications for out of stock items (critical)
        foreach ($outOfStockItems as $item) {
            $this->sendStockAlert($item, 'out_of_stock', $usersToNotify);
            $results['items'][] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'status' => 'out_of_stock',
                'current_stock' => $item->current_stock,
            ];
            $results['notifications_sent']++;
        }

        // Send notifications for low stock items (warning)
        foreach ($lowStockItems as $item) {
            // Skip if already in out of stock
            if ($item->current_stock > 0) {
                $this->sendStockAlert($item, 'low_stock', $usersToNotify);
                $results['items'][] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'status' => 'low_stock',
                    'current_stock' => $item->current_stock,
                    'reorder_level' => $item->reorder_level,
                ];
                $results['notifications_sent']++;
            }
        }

        Log::info('Stock alerts processed', $results);

        return $results;
    }

    /**
     * Get low stock items (stock <= reorder_level but > 0)
     */
    public function getLowStockItems()
    {
        return Inventory::with('category')
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->where('current_stock', '>', 0)
            ->where('status', 'active')
            ->orderBy('current_stock', 'asc')
            ->get();
    }

    /**
     * Get out of stock items (stock = 0)
     */
    public function getOutOfStockItems()
    {
        return Inventory::with('category')
            ->where('current_stock', 0)
            ->where('status', '!=', 'inactive')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get critical stock items (stock <= 25% of reorder level)
     */
    public function getCriticalStockItems()
    {
        return Inventory::with('category')
            ->whereRaw('current_stock <= (reorder_level * 0.25)')
            ->where('current_stock', '>', 0)
            ->where('status', 'active')
            ->orderBy('current_stock', 'asc')
            ->get();
    }

    /**
     * Get stock summary
     */
    public function getStockSummary(): array
    {
        $total = Inventory::count();
        $active = Inventory::where('status', 'active')->count();
        $lowStock = Inventory::lowStock()->where('status', 'active')->count();
        $outOfStock = Inventory::where('current_stock', 0)->count();
        $critical = $this->getCriticalStockItems()->count();

        return [
            'total_items' => $total,
            'active_items' => $active,
            'low_stock_items' => $lowStock,
            'out_of_stock_items' => $outOfStock,
            'critical_items' => $critical,
            'healthy_stock' => $active - $lowStock - $outOfStock,
            'alerts_required' => $lowStock + $outOfStock,
        ];
    }

    /**
     * Get items by stock status
     */
    public function getItemsByStockStatus(): array
    {
        return [
            'healthy' => Inventory::with('category')
                ->whereColumn('current_stock', '>', 'reorder_level')
                ->where('status', 'active')
                ->get(),
            'low_stock' => $this->getLowStockItems(),
            'critical' => $this->getCriticalStockItems(),
            'out_of_stock' => $this->getOutOfStockItems(),
        ];
    }

    /**
     * Get reorder suggestions
     */
    public function getReorderSuggestions(): array
    {
        $suggestions = [];

        $lowStockItems = Inventory::with('category')
            ->lowStock()
            ->where('status', 'active')
            ->get();

        foreach ($lowStockItems as $item) {
            $suggestedQuantity = max($item->reorder_level * 2, 10) - $item->current_stock;

            $suggestions[] = [
                'item' => $item,
                'current_stock' => $item->current_stock,
                'reorder_level' => $item->reorder_level,
                'suggested_quantity' => $suggestedQuantity,
                'estimated_cost' => $suggestedQuantity * $item->cost_price,
                'priority' => $item->current_stock == 0 ? 'critical' : ($item->current_stock <= $item->reorder_level * 0.25 ? 'high' : 'medium'),
            ];
        }

        // Sort by priority
        usort($suggestions, function ($a, $b) {
            $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2];
            return $priorityOrder[$a['priority']] - $priorityOrder[$b['priority']];
        });

        return $suggestions;
    }

    /**
     * Get users to notify for stock alerts
     */
    protected function getUsersToNotify()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super-admin', 'admin']);
        })
            ->orWhereHas('permissions', function ($query) {
                $query->whereIn('name', ['view-inventory', 'manage-inventory-stock']);
            })
            ->where('status', 'active')
            ->get();
    }

    /**
     * Send stock alert notification
     */
    protected function sendStockAlert(Inventory $item, string $alertType, $users): void
    {
        try {
            Notification::send($users, new LowStockNotification($item, $alertType));
        } catch (\Exception $e) {
            Log::error('Failed to send stock alert notification', [
                'item_id' => $item->id,
                'alert_type' => $alertType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get stock trend data for an item
     */
    public function getStockTrend(Inventory $item, int $days = 30): array
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days);

        $logs = $item->logs()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $trend = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');

            $dayLogs = $logs->filter(function ($log) use ($dateStr) {
                return $log->created_at->format('Y-m-d') === $dateStr;
            });

            $lastLog = $dayLogs->last();

            $trend[] = [
                'date' => $dateStr,
                'stock_level' => $lastLog ? $lastLog->new_stock : ($trend ? end($trend)['stock_level'] : $item->current_stock),
                'changes' => $dayLogs->count(),
                'added' => $dayLogs->where('type', 'add')->sum('quantity'),
                'removed' => $dayLogs->whereIn('type', ['remove', 'sale'])->sum('quantity'),
            ];

            $currentDate->addDay();
        }

        return $trend;
    }

    /**
     * Calculate average daily consumption
     */
    public function calculateAverageConsumption(Inventory $item, int $days = 30): float
    {
        $totalRemoved = $item->logs()
            ->whereIn('type', ['remove', 'sale'])
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->sum('quantity');

        return $totalRemoved / $days;
    }

    /**
     * Estimate days until out of stock
     */
    public function estimateDaysUntilOutOfStock(Inventory $item, int $baseDays = 30): ?int
    {
        $avgConsumption = $this->calculateAverageConsumption($item, $baseDays);

        if ($avgConsumption <= 0) {
            return null; // No consumption, won't run out
        }

        return (int) ceil($item->current_stock / $avgConsumption);
    }
}
