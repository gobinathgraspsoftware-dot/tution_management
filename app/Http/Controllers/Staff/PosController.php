<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosTransactionRequest;
use App\Services\PosService;
use App\Models\PosTransaction;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    protected $posService;

    public function __construct(PosService $posService)
    {
        $this->posService = $posService;
    }

    /**
     * Display POS interface
     */
    public function index()
    {
        // Check if drawer is open
        if (!$this->posService->isDrawerOpen()) {
            return redirect()->route('staff.dashboard')
                ->with('warning', 'Cash drawer is not open. Please contact admin to open the drawer first.');
        }
        
        $categories = InventoryCategory::where('status', 'active')->orderBy('name')->get();
        $items = $this->posService->getAvailableItems();
        $todayStats = $this->getStaffTodayStats();
        $cashReport = $this->posService->getTodayCashReport();
        
        return view('staff.pos.index', compact(
            'categories',
            'items',
            'todayStats',
            'cashReport'
        ));
    }

    /**
     * Get today's stats for current staff only
     */
    protected function getStaffTodayStats(): array
    {
        $transactions = PosTransaction::today()
            ->completed()
            ->where('cashier_id', Auth::id())
            ->get();
        
        return [
            'total_sales' => $transactions->sum('total_amount'),
            'total_transactions' => $transactions->count(),
            'cash_sales' => $transactions->where('payment_method', 'cash')->sum('total_amount'),
            'qr_sales' => $transactions->where('payment_method', 'qr')->sum('total_amount'),
        ];
    }

    /**
     * Process a sale
     */
    public function processSale(PosTransactionRequest $request)
    {
        // Check if drawer is open
        if (!$this->posService->isDrawerOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Cash drawer is not open. Please contact admin.',
            ], 422);
        }

        try {
            $transaction = $this->posService->processTransaction($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully!',
                'transaction' => $transaction,
                'receipt_url' => route('staff.pos.receipt', $transaction->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display staff's transactions
     */
    public function myTransactions(Request $request)
    {
        $query = PosTransaction::with(['items.inventory'])
            ->where('cashier_id', Auth::id());
        
        // Search
        if ($request->filled('search')) {
            $query->where('transaction_number', 'like', "%{$request->search}%");
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        } elseif ($request->filled('date')) {
            $query->whereDate('transaction_date', $request->date);
        }
        
        $transactions = $query->orderByDesc('transaction_date')->paginate(20)->withQueryString();
        $todayStats = $this->getStaffTodayStats();
        
        return view('staff.pos.my-transactions', compact('transactions', 'todayStats'));
    }

    /**
     * Show transaction detail
     */
    public function show(PosTransaction $transaction)
    {
        // Staff can only view their own transactions
        if ($transaction->cashier_id !== Auth::id()) {
            abort(403, 'You can only view your own transactions.');
        }
        
        $transaction->load(['items.inventory']);
        
        return view('staff.pos.show', compact('transaction'));
    }

    /**
     * Display receipt
     */
    public function receipt(PosTransaction $transaction)
    {
        // Staff can only view receipts for their own transactions
        if ($transaction->cashier_id !== Auth::id()) {
            abort(403, 'You can only view receipts for your own transactions.');
        }
        
        $receiptData = $this->posService->generateReceiptData($transaction);
        
        return view('staff.pos.receipt', $receiptData);
    }

    /**
     * Print receipt
     */
    public function printReceipt(PosTransaction $transaction)
    {
        // Staff can only print receipts for their own transactions
        if ($transaction->cashier_id !== Auth::id()) {
            abort(403, 'You can only print receipts for your own transactions.');
        }
        
        $receiptData = $this->posService->generateReceiptData($transaction);
        
        return view('staff.pos.receipt', array_merge($receiptData, ['autoPrint' => true]));
    }

    /**
     * AJAX: Get item details
     */
    public function getItemDetails(Inventory $item)
    {
        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'price' => $item->selling_price,
                'stock' => $item->current_stock,
                'image' => $item->image ? asset('storage/' . $item->image) : null,
                'category' => $item->category->name ?? 'Uncategorized',
            ],
        ]);
    }

    /**
     * AJAX: Search items
     */
    public function searchItems(Request $request)
    {
        $items = $this->posService->getAvailableItems(
            $request->category_id,
            $request->search
        );
        
        return response()->json([
            'success' => true,
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'price' => $item->selling_price,
                    'stock' => $item->current_stock,
                    'image' => $item->image ? asset('storage/' . $item->image) : null,
                    'category' => $item->category->name ?? 'Uncategorized',
                ];
            }),
        ]);
    }

    /**
     * AJAX: Get staff's today summary
     */
    public function getTodaySummary()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->getStaffTodayStats(),
        ]);
    }
}
