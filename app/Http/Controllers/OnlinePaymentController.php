<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentGatewayTransaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlinePaymentController extends Controller
{
    protected PaymentGatewayService $gatewayService;

    public function __construct(PaymentGatewayService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Display checkout page for an invoice.
     */
    public function checkout(Request $request, Invoice $invoice)
    {
        // Verify invoice can receive payment
        if (!$invoice->canReceivePayment()) {
            return redirect()->back()
                ->with('error', 'This invoice cannot receive online payments.');
        }

        // Load relationships
        $invoice->load(['student.user', 'student.parent.user', 'enrollment.package']);

        // Get available gateways
        $gateways = $this->gatewayService->getGatewayOptions();

        if (empty($gateways)) {
            return redirect()->back()
                ->with('error', 'No payment gateway is currently available. Please contact admin.');
        }

        // Calculate fees for each gateway
        $gatewayFees = [];
        foreach ($gateways as $gateway) {
            $gatewayFees[$gateway['value']] = $this->gatewayService->calculateFees(
                $invoice->balance,
                $gateway['value']
            );
        }

        // Default gateway
        $defaultGateway = config('payment_gateways.default', 'toyyibpay');

        // Online fee
        $onlineFee = config('payment_gateways.online_fee', 130.00);

        return view('payments.checkout', compact(
            'invoice',
            'gateways',
            'gatewayFees',
            'defaultGateway',
            'onlineFee'
        ));
    }

    /**
     * Process payment and redirect to gateway.
     */
    public function processPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'gateway' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'agree_terms' => 'accepted',
        ]);

        // Verify invoice can receive payment
        if (!$invoice->canReceivePayment()) {
            return redirect()->back()
                ->with('error', 'This invoice cannot receive online payments.');
        }

        $customerData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        $result = $this->gatewayService->initiatePayment(
            $invoice,
            $request->gateway,
            $customerData
        );

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['error'] ?? 'Failed to initiate payment.');
        }

        // Redirect to payment gateway
        return redirect()->away($result['payment_url']);
    }

    /**
     * Handle payment callback/return from gateway.
     */
    public function callback(Request $request, string $gateway)
    {
        Log::info("Payment callback received for {$gateway}", $request->all());

        $result = $this->gatewayService->processCallback($gateway, $request->all());

        if (!$result['success']) {
            Log::warning("Payment callback failed for {$gateway}", [
                'error' => $result['error'],
                'data' => $request->all(),
            ]);

            return redirect()->route('payment.failed', [
                'transaction' => $result['transaction']->transaction_id ?? null,
                'error' => $result['error'],
            ]);
        }

        $transaction = $result['transaction'];
        $status = $result['result']['status'] ?? 'unknown';

        // Redirect based on payment status
        return match ($status) {
            'completed' => redirect()->route('payment.success', ['transaction' => $transaction->transaction_id]),
            'pending' => redirect()->route('payment.pending', ['transaction' => $transaction->transaction_id]),
            default => redirect()->route('payment.failed', ['transaction' => $transaction->transaction_id]),
        };
    }

    /**
     * Handle payment webhook from gateway.
     */
    public function webhook(Request $request, string $gateway)
    {
        Log::info("Payment webhook received for {$gateway}", $request->all());

        $result = $this->gatewayService->processCallback($gateway, $request->all());

        if (!$result['success']) {
            Log::warning("Payment webhook failed for {$gateway}", [
                'error' => $result['error'],
                'data' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => $result['error']], 400);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Payment success page.
     */
    public function success(Request $request)
    {
        $transactionId = $request->query('transaction');

        $transaction = PaymentGatewayTransaction::with([
            'invoice.student.user',
            'invoice.enrollment.package',
            'payment',
            'gatewayConfig',
        ])->where('transaction_id', $transactionId)->first();

        if (!$transaction) {
            return redirect()->route('dashboard')
                ->with('error', 'Transaction not found.');
        }

        return view('payments.success', compact('transaction'));
    }

    /**
     * Payment failed page.
     */
    public function failed(Request $request)
    {
        $transactionId = $request->query('transaction');
        $error = $request->query('error');

        $transaction = null;
        if ($transactionId) {
            $transaction = PaymentGatewayTransaction::with([
                'invoice.student.user',
                'gatewayConfig',
            ])->where('transaction_id', $transactionId)->first();
        }

        return view('payments.failed', compact('transaction', 'error'));
    }

    /**
     * Payment pending page.
     */
    public function pending(Request $request)
    {
        $transactionId = $request->query('transaction');

        $transaction = PaymentGatewayTransaction::with([
            'invoice.student.user',
            'gatewayConfig',
        ])->where('transaction_id', $transactionId)->first();

        if (!$transaction) {
            return redirect()->route('dashboard')
                ->with('error', 'Transaction not found.');
        }

        return view('payments.pending', compact('transaction'));
    }

    /**
     * Check payment status (for AJAX polling).
     */
    public function checkStatus(Request $request, string $transactionId)
    {
        $transaction = PaymentGatewayTransaction::with(['payment'])
            ->where('transaction_id', $transactionId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'error' => 'Transaction not found',
            ], 404);
        }

        // Optionally refresh from gateway
        if ($request->boolean('refresh') && $transaction->status === 'pending') {
            $this->gatewayService->refreshTransactionStatus($transaction);
            $transaction->refresh();
        }

        return response()->json([
            'success' => true,
            'status' => $transaction->status,
            'payment_id' => $transaction->payment_id,
            'amount' => $transaction->amount,
            'updated_at' => $transaction->updated_at->toIso8601String(),
        ]);
    }

    /**
     * Retry payment for a failed transaction.
     */
    public function retry(PaymentGatewayTransaction $transaction)
    {
        if (!in_array($transaction->status, ['failed', 'cancelled'])) {
            return redirect()->back()
                ->with('error', 'Can only retry failed or cancelled payments.');
        }

        $invoice = $transaction->invoice;

        if (!$invoice->canReceivePayment()) {
            return redirect()->back()
                ->with('error', 'This invoice cannot receive payments anymore.');
        }

        return redirect()->route('payment.checkout', $invoice);
    }

    /**
     * Cancel a pending payment.
     */
    public function cancel(Request $request, PaymentGatewayTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Can only cancel pending payments.');
        }

        $this->gatewayService->cancelTransaction($transaction, 'Cancelled by user');

        return redirect()->route('payment.failed', [
            'transaction' => $transaction->transaction_id,
            'error' => 'Payment cancelled.',
        ]);
    }

    /**
     * Get invoice payment page (for students/parents).
     */
    public function invoicePayment(Invoice $invoice)
    {
        // Security check - ensure user can access this invoice
        $user = auth()->user();

        if ($user->hasRole('student')) {
            if ($invoice->student_id !== $user->student?->id) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        } elseif ($user->hasRole('parent')) {
            $childIds = $user->parent?->students->pluck('id')->toArray() ?? [];
            if (!in_array($invoice->student_id, $childIds)) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        } elseif (!$user->hasRole(['super-admin', 'admin', 'staff'])) {
            abort(403, 'Unauthorized access.');
        }

        return $this->checkout(request(), $invoice);
    }
}
