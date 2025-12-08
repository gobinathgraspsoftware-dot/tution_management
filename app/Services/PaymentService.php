<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\DailyCashReport;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Payment methods
     */
    const METHOD_CASH = 'cash';
    const METHOD_QR = 'qr';
    const METHOD_ONLINE = 'online_gateway';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHEQUE = 'cheque';

    /**
     * Payment statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_QR => 'QR Payment',
            self::METHOD_ONLINE => 'Online Gateway',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHEQUE => 'Cheque',
        ];
    }

    /**
     * Get manual payment methods (available for staff/admin)
     */
    public static function getManualPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_QR => 'QR Payment',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHEQUE => 'Cheque',
        ];
    }

    /**
     * Get payment statuses
     */
    public static function getPaymentStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Process a cash payment
     */
    public function processCashPayment(Invoice $invoice, array $data): Payment
    {
        return $this->processPayment($invoice, array_merge($data, [
            'payment_method' => self::METHOD_CASH,
        ]));
    }

    /**
     * Process a QR payment
     */
    public function processQRPayment(Invoice $invoice, array $data, ?UploadedFile $screenshot = null): Payment
    {
        $paymentData = array_merge($data, [
            'payment_method' => self::METHOD_QR,
        ]);

        // Handle screenshot upload for QR payment
        if ($screenshot) {
            $screenshotPath = $this->uploadScreenshot($screenshot);
            $paymentData['screenshot_path'] = $screenshotPath;
        }

        return $this->processPayment($invoice, $paymentData);
    }

    /**
     * Process a generic payment
     */
    public function processPayment(Invoice $invoice, array $data): Payment
    {
        // Validate invoice can receive payment
        if (!$invoice->canReceivePayment()) {
            throw new \Exception("This invoice cannot receive payments. Status: {$invoice->status}");
        }

        // Validate amount
        $amount = floatval($data['amount']);
        if ($amount <= 0) {
            throw new \Exception("Payment amount must be greater than 0");
        }

        // Check if amount exceeds balance
        $balance = $invoice->balance;
        if ($amount > $balance) {
            throw new \Exception("Payment amount (RM {$amount}) exceeds outstanding balance (RM {$balance})");
        }

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'] ?? now(),
                'reference_number' => $data['reference_number'] ?? null,
                'screenshot_path' => $data['screenshot_path'] ?? null,
                'status' => self::STATUS_COMPLETED,
                'processed_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Update invoice
            $invoice->recordPayment($amount);

            // Update daily cash report
            $this->updateDailyCashReport($payment);

            // Log activity
            $this->logPaymentActivity($payment, 'created');

            DB::commit();

            Log::info("Payment processed successfully", [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => $data['payment_method']
            ]);

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment processing failed", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process payment directly for a student (without specific invoice)
     */
    public function processStudentPayment(Student $student, array $data, ?UploadedFile $screenshot = null): Payment
    {
        // If invoice_id is provided, use it
        if (isset($data['invoice_id'])) {
            $invoice = Invoice::findOrFail($data['invoice_id']);
        } else {
            // Find the oldest unpaid invoice
            $invoice = Invoice::forStudent($student->id)
                ->unpaid()
                ->orderBy('due_date')
                ->first();

            if (!$invoice) {
                throw new \Exception("No outstanding invoices found for this student");
            }
        }

        // Handle screenshot for QR payments
        if ($screenshot && $data['payment_method'] === self::METHOD_QR) {
            $data['screenshot_path'] = $this->uploadScreenshot($screenshot);
        }

        return $this->processPayment($invoice, $data);
    }

    /**
     * Upload payment screenshot
     */
    protected function uploadScreenshot(UploadedFile $file): string
    {
        $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('payments/screenshots', $filename, 'public');

        return $path;
    }

    /**
     * Update daily cash report
     */
    protected function updateDailyCashReport(Payment $payment): void
    {
        $reportDate = $payment->payment_date->format('Y-m-d');

        $report = DailyCashReport::firstOrCreate(
            ['report_date' => $reportDate],
            [
                'opening_cash' => 0,
                'total_cash_sales' => 0,
                'total_qr_sales' => 0,
                'total_transactions' => 0,
                'expected_closing' => 0,
                'status' => 'open'
            ]
        );

        // Only update open reports
        if ($report->status === 'open') {
            if ($payment->payment_method === self::METHOD_CASH) {
                $report->increment('total_cash_sales', $payment->amount);
            } elseif ($payment->payment_method === self::METHOD_QR) {
                $report->increment('total_qr_sales', $payment->amount);
            }

            $report->increment('total_transactions');
            $report->expected_closing = $report->opening_cash + $report->total_cash_sales;
            $report->save();
        }
    }

    /**
     * Log payment activity
     */
    protected function logPaymentActivity(Payment $payment, string $action): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'log_type' => 'payment',
            'model_type' => Payment::class,
            'model_id' => $payment->id,
            'action' => $action,
            'description' => "Payment {$payment->payment_number} {$action}",
            'changes' => json_encode([
                'amount' => $payment->amount,
                'method' => $payment->payment_method,
                'invoice_id' => $payment->invoice_id,
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Verify a pending payment (for QR payments that need verification)
     */
    public function verifyPayment(Payment $payment, bool $approved, ?string $notes = null): Payment
    {
        if ($payment->status !== self::STATUS_PENDING) {
            throw new \Exception("Only pending payments can be verified");
        }

        try {
            DB::beginTransaction();

            if ($approved) {
                $payment->update([
                    'status' => self::STATUS_COMPLETED,
                    'notes' => $payment->notes . ($notes ? "\nVerification: {$notes}" : ''),
                ]);

                // Update invoice
                $payment->invoice->recordPayment($payment->amount);

                // Update daily report
                $this->updateDailyCashReport($payment);
            } else {
                $payment->update([
                    'status' => self::STATUS_FAILED,
                    'notes' => $payment->notes . "\nRejected: " . ($notes ?? 'No reason provided'),
                ]);
            }

            $this->logPaymentActivity($payment, $approved ? 'verified' : 'rejected');

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Payment $payment, float $amount, string $reason): Payment
    {
        if ($payment->status !== self::STATUS_COMPLETED) {
            throw new \Exception("Only completed payments can be refunded");
        }

        if ($amount > $payment->amount) {
            throw new \Exception("Refund amount cannot exceed original payment amount");
        }

        try {
            DB::beginTransaction();

            // If full refund
            if ($amount >= $payment->amount) {
                $payment->update([
                    'status' => self::STATUS_REFUNDED,
                    'notes' => $payment->notes . "\nRefunded: {$reason}",
                ]);
            }

            // Update invoice (decrease paid amount)
            $invoice = $payment->invoice;
            $invoice->paid_amount = max(0, $invoice->paid_amount - $amount);

            // Update invoice status
            if ($invoice->paid_amount == 0) {
                $invoice->status = $invoice->isOverdue() ? 'overdue' : 'pending';
            } elseif ($invoice->paid_amount < $invoice->total_amount) {
                $invoice->status = 'partial';
            }
            $invoice->save();

            $this->logPaymentActivity($payment, 'refunded');

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', self::STATUS_COMPLETED);

        return [
            'total_collected' => (clone $payments)->sum('amount'),
            'cash_collected' => (clone $payments)->where('payment_method', self::METHOD_CASH)->sum('amount'),
            'qr_collected' => (clone $payments)->where('payment_method', self::METHOD_QR)->sum('amount'),
            'online_collected' => (clone $payments)->where('payment_method', self::METHOD_ONLINE)->sum('amount'),
            'other_collected' => (clone $payments)->whereNotIn('payment_method', [
                self::METHOD_CASH, self::METHOD_QR, self::METHOD_ONLINE
            ])->sum('amount'),
            'total_transactions' => (clone $payments)->count(),
            'cash_transactions' => (clone $payments)->where('payment_method', self::METHOD_CASH)->count(),
            'qr_transactions' => (clone $payments)->where('payment_method', self::METHOD_QR)->count(),
            'online_transactions' => (clone $payments)->where('payment_method', self::METHOD_ONLINE)->count(),
            'pending_verification' => Payment::where('status', self::STATUS_PENDING)->count(),
            'refunded_amount' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                ->where('status', self::STATUS_REFUNDED)->sum('amount'),
        ];
    }

    /**
     * Get payments by date range
     */
    public function getPaymentsByDateRange(Carbon $startDate, Carbon $endDate, ?array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Payment::with(['invoice', 'student.user', 'processedBy'])
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->orderBy('payment_date', 'desc')->get();
    }

    /**
     * Get daily collection summary
     */
    public function getDailyCollectionSummary(Carbon $date): array
    {
        $payments = Payment::whereDate('payment_date', $date)
            ->where('status', self::STATUS_COMPLETED);

        $byMethod = $payments->get()->groupBy('payment_method');

        return [
            'date' => $date->format('Y-m-d'),
            'total_collected' => (clone $payments)->sum('amount'),
            'by_method' => [
                'cash' => [
                    'amount' => (clone $payments)->where('payment_method', self::METHOD_CASH)->sum('amount'),
                    'count' => (clone $payments)->where('payment_method', self::METHOD_CASH)->count(),
                ],
                'qr' => [
                    'amount' => (clone $payments)->where('payment_method', self::METHOD_QR)->sum('amount'),
                    'count' => (clone $payments)->where('payment_method', self::METHOD_QR)->count(),
                ],
                'other' => [
                    'amount' => (clone $payments)->whereNotIn('payment_method', [
                        self::METHOD_CASH, self::METHOD_QR, self::METHOD_ONLINE
                    ])->sum('amount'),
                    'count' => (clone $payments)->whereNotIn('payment_method', [
                        self::METHOD_CASH, self::METHOD_QR, self::METHOD_ONLINE
                    ])->count(),
                ],
            ],
            'total_transactions' => (clone $payments)->count(),
        ];
    }

    /**
     * Get payment history for a student
     */
    public function getStudentPaymentHistory(int $studentId, ?int $limit = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Payment::with(['invoice', 'processedBy'])
            ->where('student_id', $studentId)
            ->orderBy('payment_date', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get pending verifications
     */
    public function getPendingVerifications(): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::with(['invoice', 'student.user'])
            ->where('status', self::STATUS_PENDING)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get QR code settings (from settings or config)
     */
    public function getQRSettings(): array
    {
        return [
            'bank_name' => Setting::where('key', 'qr_bank_name')->first()?->value ?? config('payment.qr.bank_name', 'DuitNow'),
            'account_name' => Setting::where('key', 'qr_account_name')->first()?->value ?? config('payment.qr.account_name', 'Arena Matriks Edu Group'),
            'account_number' => Setting::where('key', 'qr_account_number')->first()?->value ?? config('payment.qr.account_number', ''),
            'qr_image' => Setting::where('key', 'qr_image_path')->first()?->value ?? config('payment.qr.image_path', ''),
        ];
    }
}
