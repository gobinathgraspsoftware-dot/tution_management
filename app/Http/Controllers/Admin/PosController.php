<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosTransactionRequest;
use App\Services\PosService;
use App\Models\PosTransaction;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Exports\PosTransactionExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $categories = InventoryCategory::where('status', 'active')->orderBy('name')->get();
        $items = $this->posService->getAvailableItems();
        $todayStats = $this->posService->getTodayStatistics();
        $cashReport = $this->posService->getTodayCashReport();
        $drawerOpen = $this->posService->isDrawerOpen();
        
        return view('admin.pos.index', compact(
            'categories',
            'items',
            'todayStats',
            'cashReport',
            'drawerOpen'
        ));
    }

    /**
     * Process a sale
     */
    public function processSale(PosTransactionRequest $request)
    {
        try {
            $transaction = $this->posService->processTransaction($request->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully!',
                'transaction' => $transaction,
                'receipt_url' => route('admin.pos.receipt', $transaction->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display transaction history
     */
    public function transactions(Request $request)
    {
        $query = PosTransaction::with(['items.inventory', 'cashier']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('cashier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
        
        // Filter by cashier
        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }
        
        $transactions = $query->orderByDesc('transaction_date')->paginate(20)->withQueryString();
        $todayStats = $this->posService->getTodayStatistics();
        
        return view('admin.pos.transactions', compact('transactions', 'todayStats'));
    }

    /**
     * Show transaction detail
     */
    public function show(PosTransaction $transaction)
    {
        $transaction->load(['items.inventory', 'cashier']);
        
        return view('admin.pos.show', compact('transaction'));
    }

    /**
     * Display receipt
     */
    public function receipt(PosTransaction $transaction)
    {
        $receiptData = $this->posService->generateReceiptData($transaction);
        
        return view('admin.pos.receipt', $receiptData);
    }

    /**
     * Download receipt as PDF
     */
    public function downloadReceipt(PosTransaction $transaction)
    {
        $receiptData = $this->posService->generateReceiptData($transaction);
        
        $pdf = Pdf::loadView('admin.pos.receipt-pdf', $receiptData);
        $pdf->setPaper([0, 0, 226.77, 500], 'portrait'); // 80mm width
        
        return $pdf->download("receipt-{$transaction->transaction_number}.pdf");
    }

    /**
     * Print receipt
     */
    public function printReceipt(PosTransaction $transaction)
    {
        $receiptData = $this->posService->generateReceiptData($transaction);
        
        return view('admin.pos.receipt-print', $receiptData);
    }

    /**
     * Void transaction
     */
    public function voidTransaction(Request $request, PosTransaction $transaction)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $transaction = $this->posService->voidTransaction($transaction, $request->reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction voided successfully.',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Refund transaction
     */
    public function refundTransaction(Request $request, PosTransaction $transaction)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:pos_transaction_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $transaction = $this->posService->refundTransaction(
                $transaction,
                $request->items,
                $request->reason
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully.',
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export transactions to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'payment_method', 'start_date', 'end_date', 'cashier_id']);
        
        return Excel::download(
            new PosTransactionExport($filters),
            'pos-transactions-' . now()->format('Y-m-d') . '.xlsx'
        );
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
     * AJAX: Get today's summary
     */
    public function getTodaySummary()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->posService->getTodayStatistics(),
            'cash_report' => $this->posService->getTodayCashReport(),
        ]);
    }
}
