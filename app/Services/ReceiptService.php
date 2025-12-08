<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReceiptService
{
    /**
     * Company details (can be loaded from settings)
     */
    protected array $companyDetails;

    public function __construct()
    {
        $this->companyDetails = $this->loadCompanyDetails();
    }

    /**
     * Load company details from settings
     */
    protected function loadCompanyDetails(): array
    {
        return [
            'name' => Setting::where('key', 'company_name')->first()?->value ?? 'Arena Matriks Edu Group',
            'address' => Setting::where('key', 'company_address')->first()?->value ?? 'Wisma Arena Matriks, No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor',
            'phone' => Setting::where('key', 'company_phone')->first()?->value ?? '03-5523 4567',
            'email' => Setting::where('key', 'company_email')->first()?->value ?? 'info@arenamatriks.com',
            'website' => Setting::where('key', 'company_website')->first()?->value ?? 'www.arenamatriks.com',
            'registration_no' => Setting::where('key', 'company_registration_no')->first()?->value ?? '',
            'logo' => Setting::where('key', 'company_logo')->first()?->value ?? 'images/logo.png',
        ];
    }

    /**
     * Generate receipt data for a payment
     */
    public function generateReceiptData(Payment $payment): array
    {
        $payment->load(['invoice.student.user', 'invoice.student.parent.user', 'invoice.enrollment.package', 'processedBy']);

        $invoice = $payment->invoice;
        $student = $invoice->student;

        return [
            'receipt_number' => $this->generateReceiptNumber($payment),
            'payment' => $payment,
            'invoice' => $invoice,
            'student' => [
                'id' => $student->student_id ?? $student->id,
                'name' => $student->user->name,
                'ic_number' => $student->ic_number ?? 'N/A',
                'class' => $invoice->enrollment?->class?->name ?? 'N/A',
                'package' => $invoice->enrollment?->package?->name ?? 'N/A',
            ],
            'parent' => [
                'name' => $student->parent?->user?->name ?? 'N/A',
                'phone' => $student->parent?->whatsapp_number ?? 'N/A',
                'email' => $student->parent?->user?->email ?? 'N/A',
            ],
            'payment_details' => [
                'number' => $payment->payment_number,
                'amount' => $payment->amount,
                'method' => $this->getMethodLabel($payment->payment_method),
                'reference' => $payment->reference_number ?? 'N/A',
                'date' => $payment->payment_date->format('d M Y'),
                'time' => $payment->created_at->format('h:i A'),
                'processed_by' => $payment->processedBy?->name ?? 'System',
            ],
            'invoice_details' => [
                'number' => $invoice->invoice_number,
                'type' => $invoice->type_label,
                'period' => $invoice->billing_period,
                'subtotal' => $invoice->subtotal,
                'online_fee' => $invoice->online_fee,
                'discount' => $invoice->discount,
                'total' => $invoice->total_amount,
                'paid' => $invoice->paid_amount,
                'balance' => $invoice->balance,
                'status' => $invoice->status_label,
            ],
            'company' => $this->companyDetails,
            'generated_at' => Carbon::now()->format('d M Y h:i A'),
        ];
    }

    /**
     * Generate receipt number
     */
    protected function generateReceiptNumber(Payment $payment): string
    {
        return 'RCP-' . $payment->payment_number;
    }

    /**
     * Get payment method label
     */
    protected function getMethodLabel(string $method): string
    {
        $methods = [
            'cash' => 'Cash',
            'qr' => 'QR Payment',
            'online_gateway' => 'Online Payment',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
        ];

        return $methods[$method] ?? ucfirst($method);
    }

    /**
     * Generate receipt HTML
     */
    public function generateReceiptHtml(Payment $payment): string
    {
        $data = $this->generateReceiptData($payment);
        return View::make('admin.payments.receipt', $data)->render();
    }

    /**
     * Generate receipt PDF
     */
    public function generateReceiptPdf(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $data = $this->generateReceiptData($payment);

        $pdf = Pdf::loadView('admin.payments.receipt', $data);
        $pdf->setPaper('A5', 'portrait');

        return $pdf;
    }

    /**
     * Download receipt PDF
     */
    public function downloadReceiptPdf(Payment $payment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $pdf = $this->generateReceiptPdf($payment);
        $filename = 'receipt_' . $payment->payment_number . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream receipt PDF
     */
    public function streamReceiptPdf(Payment $payment): \Illuminate\Http\Response
    {
        $pdf = $this->generateReceiptPdf($payment);
        return $pdf->stream();
    }

    /**
     * Save receipt PDF to storage
     */
    public function saveReceiptPdf(Payment $payment): string
    {
        $pdf = $this->generateReceiptPdf($payment);
        $filename = 'receipts/' . date('Y/m') . '/receipt_' . $payment->payment_number . '.pdf';

        \Storage::put('public/' . $filename, $pdf->output());

        return $filename;
    }

    /**
     * Get receipt for preview (array data)
     */
    public function getReceiptForPreview(Payment $payment): array
    {
        return $this->generateReceiptData($payment);
    }

    /**
     * Generate bulk receipts for multiple payments
     */
    public function generateBulkReceipts(array $paymentIds): array
    {
        $receipts = [];

        foreach ($paymentIds as $paymentId) {
            $payment = Payment::find($paymentId);
            if ($payment) {
                $receipts[] = [
                    'payment_id' => $paymentId,
                    'receipt_number' => $this->generateReceiptNumber($payment),
                    'file_path' => $this->saveReceiptPdf($payment),
                ];
            }
        }

        return $receipts;
    }

    /**
     * Format amount in words (Malaysian Ringgit)
     */
    public function amountInWords(float $amount): string
    {
        $ones = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
            'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
            'seventeen', 'eighteen', 'nineteen'
        ];

        $tens = [
            '', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'
        ];

        $number = number_format($amount, 2, '.', '');
        $parts = explode('.', $number);
        $ringgit = (int) $parts[0];
        $sen = (int) $parts[1];

        $words = '';

        if ($ringgit >= 1000) {
            $thousands = (int) ($ringgit / 1000);
            $words .= $this->convertToWords($thousands, $ones, $tens) . ' thousand ';
            $ringgit %= 1000;
        }

        if ($ringgit >= 100) {
            $hundreds = (int) ($ringgit / 100);
            $words .= $ones[$hundreds] . ' hundred ';
            $ringgit %= 100;
        }

        if ($ringgit > 0) {
            $words .= $this->convertToWords($ringgit, $ones, $tens) . ' ';
        }

        $words = trim($words) . ' ringgit';

        if ($sen > 0) {
            $words .= ' and ' . $this->convertToWords($sen, $ones, $tens) . ' sen';
        }

        return ucfirst($words) . ' only';
    }

    /**
     * Helper function to convert number to words
     */
    protected function convertToWords(int $number, array $ones, array $tens): string
    {
        if ($number < 20) {
            return $ones[$number];
        }

        $ten = (int) ($number / 10);
        $one = $number % 10;

        return $tens[$ten] . ($one > 0 ? '-' . $ones[$one] : '');
    }

    /**
     * Get receipt template data for WhatsApp message
     */
    public function getReceiptForWhatsApp(Payment $payment): string
    {
        $data = $this->generateReceiptData($payment);

        return "🧾 *PAYMENT RECEIPT*\n\n" .
            "*{$data['company']['name']}*\n\n" .
            "Receipt No: {$data['receipt_number']}\n" .
            "Date: {$data['payment_details']['date']}\n\n" .
            "*Student Details:*\n" .
            "Name: {$data['student']['name']}\n" .
            "ID: {$data['student']['id']}\n\n" .
            "*Payment Details:*\n" .
            "Invoice: {$data['invoice_details']['number']}\n" .
            "Amount: RM " . number_format($data['payment_details']['amount'], 2) . "\n" .
            "Method: {$data['payment_details']['method']}\n" .
            "Reference: {$data['payment_details']['reference']}\n\n" .
            "*Invoice Status:*\n" .
            "Total: RM " . number_format($data['invoice_details']['total'], 2) . "\n" .
            "Paid: RM " . number_format($data['invoice_details']['paid'], 2) . "\n" .
            "Balance: RM " . number_format($data['invoice_details']['balance'], 2) . "\n\n" .
            "Thank you for your payment! 🙏";
    }

    /**
     * Get receipt for email
     */
    public function getReceiptForEmail(Payment $payment): array
    {
        $data = $this->generateReceiptData($payment);

        return [
            'subject' => "Payment Receipt - {$data['receipt_number']}",
            'data' => $data,
            'template' => 'emails.payment-receipt',
        ];
    }
}
