<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt_number ?? $payment->payment_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .company-logo {
            max-width: 120px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a5f7a;
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
            letter-spacing: 2px;
            margin: 15px 0;
            padding: 8px;
            background: #1a5f7a;
            color: #fff;
        }

        .receipt-info {
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px dotted #ddd;
        }

        .info-label {
            color: #666;
            font-size: 11px;
        }

        .info-value {
            font-weight: bold;
            text-align: right;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            background: #f5f5f5;
            padding: 5px 8px;
            margin: 15px 0 10px 0;
            border-left: 3px solid #1a5f7a;
        }

        .amount-box {
            text-align: center;
            padding: 15px;
            margin: 15px 0;
            background: #e8f4f8;
            border: 2px solid #1a5f7a;
            border-radius: 5px;
        }

        .amount-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #1a5f7a;
        }

        .amount-words {
            font-size: 10px;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }

        .payment-method {
            text-align: center;
            padding: 8px;
            background: #f0f0f0;
            border-radius: 3px;
            margin-bottom: 15px;
        }

        .balance-section {
            background: #fff9e6;
            padding: 10px;
            border: 1px solid #ffc107;
            border-radius: 3px;
            margin: 15px 0;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .balance-label {
            font-size: 11px;
        }

        .balance-value {
            font-weight: bold;
        }

        .balance-due {
            color: #dc3545;
        }

        .balance-paid {
            color: #28a745;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #999;
        }

        .footer-note {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
        }

        .thank-you {
            font-size: 14px;
            font-weight: bold;
            color: #1a5f7a;
            margin: 10px 0;
        }

        .qr-code {
            margin: 15px 0;
        }

        .qr-code img {
            max-width: 80px;
        }

        .generated-at {
            font-size: 9px;
            color: #999;
            margin-top: 10px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt-container {
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #1a5f7a;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #154b61;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Print Receipt
    </button>

    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            @if(isset($company['logo']) && $company['logo'])
                <img src="{{ asset('storage/' . $company['logo']) }}" alt="Logo" class="company-logo">
            @endif
            <div class="company-name">{{ $company['name'] ?? 'Arena Matriks Edu Group' }}</div>
            <div class="company-details">
                {{ $company['address'] ?? '' }}<br>
                Tel: {{ $company['phone'] ?? '' }} | Email: {{ $company['email'] ?? '' }}
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">Payment Receipt</div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Receipt No:</span>
                <span class="info-value">{{ $receipt_number ?? 'RCP-' . $payment->payment_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $payment_details['date'] ?? $payment->payment_date->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Time:</span>
                <span class="info-value">{{ $payment_details['time'] ?? $payment->created_at->format('h:i A') }}</span>
            </div>
        </div>

        <!-- Student Details -->
        <div class="section-title">Student Details</div>
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $student['name'] ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Student ID:</span>
                <span class="info-value">{{ $student['id'] ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Package:</span>
                <span class="info-value">{{ $student['package'] ?? '-' }}</span>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="section-title">Invoice Details</div>
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Invoice No:</span>
                <span class="info-value">{{ $invoice_details['number'] ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Type:</span>
                <span class="info-value">{{ $invoice_details['type'] ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Period:</span>
                <span class="info-value">{{ $invoice_details['period'] ?? '-' }}</span>
            </div>
        </div>

        <!-- Payment Amount -->
        <div class="amount-box">
            <div class="amount-label">Amount Paid</div>
            <div class="amount-value">RM {{ number_format($payment_details['amount'] ?? $payment->amount, 2) }}</div>
            @if(isset($amount_in_words))
                <div class="amount-words">{{ $amount_in_words }}</div>
            @endif
        </div>

        <!-- Payment Method -->
        <div class="payment-method">
            <strong>Payment Method:</strong> {{ $payment_details['method'] ?? ucfirst($payment->payment_method) }}
            @if(isset($payment_details['reference']) && $payment_details['reference'] !== 'N/A')
                <br><small>Ref: {{ $payment_details['reference'] }}</small>
            @endif
        </div>

        <!-- Invoice Balance -->
        <div class="balance-section">
            <div class="balance-row">
                <span class="balance-label">Invoice Total:</span>
                <span class="balance-value">RM {{ number_format($invoice_details['total'] ?? 0, 2) }}</span>
            </div>
            <div class="balance-row">
                <span class="balance-label">Total Paid:</span>
                <span class="balance-value balance-paid">RM {{ number_format($invoice_details['paid'] ?? 0, 2) }}</span>
            </div>
            <div class="balance-row" style="border-top: 1px solid #ddd; padding-top: 5px; margin-top: 5px;">
                <span class="balance-label"><strong>Balance Due:</strong></span>
                <span class="balance-value balance-due">RM {{ number_format($invoice_details['balance'] ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-note">
                This is a computer-generated receipt. No signature required.
            </div>
            <div class="thank-you">Thank You For Your Payment!</div>
            <div class="footer-note">
                For enquiries, please contact us at {{ $company['phone'] ?? '' }}
            </div>
            <div class="generated-at">
                Generated: {{ $generated_at ?? now()->format('d M Y h:i A') }}<br>
                Processed by: {{ $payment_details['processed_by'] ?? 'System' }}
            </div>
        </div>
    </div>

    <script>
        // Auto print if requested via URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
