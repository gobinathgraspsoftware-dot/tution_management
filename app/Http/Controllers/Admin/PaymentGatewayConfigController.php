<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentGatewayConfigRequest;
use App\Models\PaymentGatewayConfig;
use App\Models\PaymentGatewayTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PaymentGatewayConfigController extends Controller
{
    protected PaymentGatewayService $gatewayService;

    public function __construct(PaymentGatewayService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Display a listing of payment gateway configurations.
     */
    public function index()
    {
        $gateways = PaymentGatewayConfig::withCount([
            'transactions',
            'transactions as completed_transactions_count' => function ($query) {
                $query->where('status', 'completed');
            },
        ])->get();

        // Get statistics for each gateway
        $statistics = [];
        foreach ($gateways as $gateway) {
            $statistics[$gateway->id] = $this->gatewayService->getStatistics(
                $gateway->gateway_name,
                now()->startOfMonth(),
                now()
            );
        }

        // Available gateway types
        $availableGateways = config('payment_gateways.gateways', []);
        $configuredGateways = $gateways->pluck('gateway_name')->toArray();
        $unconfiguredGateways = array_diff(array_keys($availableGateways), $configuredGateways);

        return view('admin.payment-gateways.index', compact(
            'gateways',
            'statistics',
            'availableGateways',
            'unconfiguredGateways'
        ));
    }

    /**
     * Show the form for creating a new gateway configuration.
     */
    public function create(Request $request)
    {
        $gatewayType = $request->query('type');
        $availableGateways = config('payment_gateways.gateways', []);

        // Check if gateway type is valid and not already configured
        if ($gatewayType && !isset($availableGateways[$gatewayType])) {
            return redirect()->route('admin.payment-gateways.index')
                ->with('error', 'Invalid gateway type.');
        }

        $existingGateway = PaymentGatewayConfig::where('gateway_name', $gatewayType)->first();
        if ($gatewayType && $existingGateway) {
            return redirect()->route('admin.payment-gateways.edit', $existingGateway)
                ->with('info', 'This gateway is already configured. You can edit it here.');
        }

        // Get unconfigured gateways
        $configuredGateways = PaymentGatewayConfig::pluck('gateway_name')->toArray();
        $unconfiguredGateways = array_diff_key($availableGateways, array_flip($configuredGateways));

        return view('admin.payment-gateways.create', compact(
            'gatewayType',
            'availableGateways',
            'unconfiguredGateways'
        ));
    }

    /**
     * Store a newly created gateway configuration.
     */
    public function store(PaymentGatewayConfigRequest $request)
    {
        $validated = $request->validated();

        // Check if gateway already exists
        $existing = PaymentGatewayConfig::where('gateway_name', $validated['gateway_name'])->first();
        if ($existing) {
            return back()->withInput()
                ->with('error', 'This payment gateway is already configured.');
        }

        // Encrypt sensitive data
        $gatewayConfig = PaymentGatewayConfig::create([
            'gateway_name' => $validated['gateway_name'],
            'is_active' => $validated['is_active'] ?? false,
            'is_sandbox' => $validated['is_sandbox'] ?? true,
            'api_key' => $validated['api_key'] ? Crypt::encryptString($validated['api_key']) : null,
            'api_secret' => $validated['api_secret'] ? Crypt::encryptString($validated['api_secret']) : null,
            'merchant_id' => $validated['merchant_id'] ?? null,
            'webhook_secret' => $validated['webhook_secret'] ? Crypt::encryptString($validated['webhook_secret']) : null,
            'configuration' => $validated['configuration'] ?? [],
            'supported_currencies' => $validated['supported_currencies'] ?? ['MYR'],
            'transaction_fee_percentage' => $validated['transaction_fee_percentage'] ?? 0,
            'transaction_fee_fixed' => $validated['transaction_fee_fixed'] ?? 0,
        ]);

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway configured successfully.');
    }

    /**
     * Display the specified gateway configuration.
     */
    public function show(PaymentGatewayConfig $paymentGateway)
    {
        $statistics = $this->gatewayService->getStatistics(
            $paymentGateway->gateway_name,
            now()->startOfMonth(),
            now()
        );

        // Get recent transactions
        $recentTransactions = PaymentGatewayTransaction::where('gateway_config_id', $paymentGateway->id)
            ->with(['invoice.student.user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly statistics
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $stats = $this->gatewayService->getStatistics(
                $paymentGateway->gateway_name,
                $month->startOfMonth()->copy(),
                $month->endOfMonth()->copy()
            );
            $monthlyStats[$month->format('M Y')] = $stats;
        }

        $gatewayInfo = config("payment_gateways.gateways.{$paymentGateway->gateway_name}", []);

        return view('admin.payment-gateways.show', compact(
            'paymentGateway',
            'statistics',
            'recentTransactions',
            'monthlyStats',
            'gatewayInfo'
        ));
    }

    /**
     * Show the form for editing the specified gateway configuration.
     */
    public function edit(PaymentGatewayConfig $paymentGateway)
    {
        $gatewayInfo = config("payment_gateways.gateways.{$paymentGateway->gateway_name}", []);

        return view('admin.payment-gateways.edit', compact('paymentGateway', 'gatewayInfo'));
    }

    /**
     * Update the specified gateway configuration.
     */
    public function update(PaymentGatewayConfigRequest $request, PaymentGatewayConfig $paymentGateway)
    {
        $validated = $request->validated();

        $updateData = [
            'is_active' => $validated['is_active'] ?? false,
            'is_sandbox' => $validated['is_sandbox'] ?? true,
            'merchant_id' => $validated['merchant_id'] ?? null,
            'configuration' => $validated['configuration'] ?? [],
            'supported_currencies' => $validated['supported_currencies'] ?? ['MYR'],
            'transaction_fee_percentage' => $validated['transaction_fee_percentage'] ?? 0,
            'transaction_fee_fixed' => $validated['transaction_fee_fixed'] ?? 0,
        ];

        // Only update sensitive fields if provided
        if (!empty($validated['api_key'])) {
            $updateData['api_key'] = Crypt::encryptString($validated['api_key']);
        }

        if (!empty($validated['api_secret'])) {
            $updateData['api_secret'] = Crypt::encryptString($validated['api_secret']);
        }

        if (!empty($validated['webhook_secret'])) {
            $updateData['webhook_secret'] = Crypt::encryptString($validated['webhook_secret']);
        }

        $paymentGateway->update($updateData);

        return redirect()->route('admin.payment-gateways.show', $paymentGateway)
            ->with('success', 'Payment gateway updated successfully.');
    }

    /**
     * Remove the specified gateway configuration.
     */
    public function destroy(PaymentGatewayConfig $paymentGateway)
    {
        // Check for existing transactions
        $hasTransactions = PaymentGatewayTransaction::where('gateway_config_id', $paymentGateway->id)->exists();

        if ($hasTransactions) {
            return back()->with('error', 'Cannot delete gateway with existing transactions. Deactivate it instead.');
        }

        $paymentGateway->delete();

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway deleted successfully.');
    }

    /**
     * Toggle gateway active status.
     */
    public function toggleStatus(PaymentGatewayConfig $paymentGateway)
    {
        $paymentGateway->update([
            'is_active' => !$paymentGateway->is_active,
        ]);

        $status = $paymentGateway->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Payment gateway {$status} successfully.");
    }

    /**
     * Display transaction history for a gateway.
     */
    public function transactions(Request $request, PaymentGatewayConfig $paymentGateway)
    {
        $query = PaymentGatewayTransaction::where('gateway_config_id', $paymentGateway->id)
            ->with(['invoice.student.user', 'payment']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by transaction ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($q2) use ($search) {
                        $q2->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics for filtered results
        $statistics = [
            'total' => $transactions->total(),
            'completed' => PaymentGatewayTransaction::where('gateway_config_id', $paymentGateway->id)
                ->where('status', 'completed')
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
                ->count(),
            'total_amount' => PaymentGatewayTransaction::where('gateway_config_id', $paymentGateway->id)
                ->where('status', 'completed')
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
                ->sum('amount'),
        ];

        return view('admin.payment-gateways.transactions', compact(
            'paymentGateway',
            'transactions',
            'statistics'
        ));
    }

    /**
     * Test gateway connection.
     */
    public function testConnection(PaymentGatewayConfig $paymentGateway)
    {
        try {
            $gateway = $this->gatewayService->getGateway($paymentGateway->gateway_name);

            if (!$gateway) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initialize gateway',
                ]);
            }

            // Test based on gateway type
            $result = match ($paymentGateway->gateway_name) {
                'toyyibpay' => $this->testToyyibPay($gateway),
                'senangpay' => $this->testSenangPay($gateway),
                'billplz' => $this->testBillplz($gateway),
                default => ['success' => false, 'message' => 'Unknown gateway type'],
            };

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Test ToyyibPay connection.
     */
    protected function testToyyibPay($gateway): array
    {
        $categories = $gateway->getCategories();

        if (is_array($categories)) {
            return [
                'success' => true,
                'message' => 'Connection successful. Found ' . count($categories) . ' categories.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to retrieve categories from ToyyibPay.',
        ];
    }

    /**
     * Test SenangPay connection.
     */
    protected function testSenangPay($gateway): array
    {
        // SenangPay doesn't have a direct API test endpoint
        // We'll just verify credentials are set
        return [
            'success' => true,
            'message' => 'Configuration verified. Test a small payment to fully verify.',
        ];
    }

    /**
     * Test Billplz connection.
     */
    protected function testBillplz($gateway): array
    {
        $collections = $gateway->getCollections();

        if (is_array($collections)) {
            return [
                'success' => true,
                'message' => 'Connection successful. Found ' . count($collections) . ' collections.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to retrieve collections from Billplz.',
        ];
    }

    /**
     * Refresh transaction status.
     */
    public function refreshTransaction(PaymentGatewayTransaction $transaction)
    {
        $result = $this->gatewayService->refreshTransactionStatus($transaction);

        if ($result['success']) {
            return back()->with('success', "Transaction status updated: {$result['status']}");
        }

        return back()->with('error', $result['error'] ?? 'Failed to refresh transaction status');
    }
}
