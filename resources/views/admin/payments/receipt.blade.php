<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt_number ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .company-logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 10px;
            color: #666;
        }

        .receipt-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
            padding: 5px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .receipt-number {
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .info-label {
            color: #666;
        }

        .info-value {
            font-weight: 500;
            text-align: right;
        }

        .amount-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }

        .invoice-details {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin: 15px 0;
        }

        .invoice-summary {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .invoice-summary.highlight {
            font-weight: bold;
            border-top: 1px solid #ddd;
            margin-top: 5px;
            padding-top: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-partial { background: #cce5ff; color: #004085; }
        .status-paid { background: #d4edda; color: #155724; }

        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #333;
        }

        .footer-text {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .thank-you {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }

        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: #f0f0f0;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #999;
        }

        .divider {
            border-bottom: 1px dashed #ccc;
            margin: 10px 0;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt-container {
                max-width: 100%;
                padding: 10px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            @if(isset($company['logo']) && $company['logo'])
            <img src="{{ asset('storage/' . $company['logo']) }}" alt="Logo" class="company-logo">
            @endif
            <div class="company-name">{{ $company['name'] ?? 'Arena Matriks Edu Group' }}</div>
            <div class="company-details">
                {{ $company['address'] ?? '' }}<br>
                Tel: {{ $company['phone'] ?? '' }} | Email: {{ $company['email'] ?? '' }}<br>
                @if(isset($company['registration_no']) && $company['registration_no'])
                Reg No: {{ $company['registration_no'] }}
                @endif
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">Payment Receipt</div>
        <div class="receipt-number">
            <strong>{{ $receipt_number ?? 'N/A' }}</strong><br>
            <small>{{ $payment_details['date'] ?? '' }} {{ $payment_details['time'] ?? '' }}</small>
        </div>

        <!-- Student Information -->
        <div class="section">
            <div class="section-title">Student Information</div>
            <div class="info-row">
                <span class="info-label">Name</span>
                <span class="info-value">{{ $student['name'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Student ID</span>
                <span class="info-value">{{ $student['id'] ?? 'N/A' }}</span>
            </div>
            @if(isset($student['class']) && $student['class'] !== 'N/A')
            <div class="info-row">
                <span class="info-label">Class</span>
                <span class="info-value">{{ $student['class'] }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Payment Details -->
        <div class="section">
            <div class="section-title">Payment Details</div>
            <div class="info-row">
                <span class="info-label">Payment Number</span>
                <span class="info-value">{{ $payment_details['number'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method</span>
                <span class="info-value">{{ $payment_details['method'] ?? 'N/A' }}</span>
            </div>
            @if(isset($payment_details['reference']) && $payment_details['reference'] !== 'N/A')
            <div class="info-row">
                <span class="info-label">Reference No.</span>
                <span class="info-value">{{ $payment_details['reference'] }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Processed By</span>
                <span class="info-value">{{ $payment_details['processed_by'] ?? 'System' }}</span>
            </div>
        </div>

        <!-- Amount Section -->
        <div class="amount-section">
            <div class="total-row">
                <span>Amount Paid</span>
                <span>RM {{ number_format($payment_details['amount'] ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Invoice Details -->
        @if(isset($invoice_details))
        <div class="invoice-details">
            <div class="section-title">Invoice Information</div>
            <div class="info-row">
                <span class="info-label">Invoice No.</span>
                <span class="info-value">{{ $invoice_details['number'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Period</span>
                <span class="info-value">{{ $invoice_details['period'] ?? 'N/A' }}</span>
            </div>
            <div class="divider"></div>
            <div class="invoice-summary">
                <span>Invoice Total</span>
                <span>RM {{ number_format($invoice_details['total'] ?? 0, 2) }}</span>
            </div>
            <div class="invoice-summary">
                <span>Total Paid</span>
                <span>RM {{ number_format($invoice_details['paid'] ?? 0, 2) }}</span>
            </div>
            <div class="invoice-summary highlight">
                <span>Balance Due</span>
                <span>RM {{ number_format($invoice_details['balance'] ?? 0, 2) }}</span>
            </div>
            <div style="text-align: center; margin-top: 10px;">
                <span class="status-badge status-{{ strtolower($invoice_details['status'] ?? 'pending') }}">
                    {{ $invoice_details['status'] ?? 'Pending' }}
                </span>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="thank-you">Thank You!</div>
            <div class="footer-text">
                This is a computer-generated receipt. No signature required.<br>
                Generated on: {{ $generated_at ?? now()->format('d M Y h:i A') }}
            </div>
            @if(isset($company['website']) && $company['website'])
            <div class="footer-text">
                {{ $company['website'] }}
            </div>
            @endif
        </div>

        <!-- Print Button (hidden on print) -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px;">
                Print Receipt
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 4px; margin-left: 10px;">
                Close
            </button>
        </div>
    </div>
</body>
</html>
