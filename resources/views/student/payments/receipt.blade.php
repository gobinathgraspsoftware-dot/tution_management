<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->payment_number }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .receipt-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
            margin-bottom: 30px;
        }
        
        .company-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        
        .receipt-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 15px 0;
        }
        
        .receipt-number {
            font-size: 14px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 150px;
            display: inline-block;
        }
        
        .amount-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .amount-paid {
            font-size: 32px;
            font-weight: bold;
            color: #28a745;
            text-align: center;
        }
        
        .payment-details-table {
            width: 100%;
            margin: 20px 0;
        }
        
        .payment-details-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .receipt-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: center;
            color: #666;
        }
        
        .stamp-section {
            margin-top: 40px;
            text-align: right;
        }
        
        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Buttons -->
        <div class="no-print text-end mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i> Print Receipt
            </button>
            <a href="{{ route('student.payments.download-receipt', $payment) }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i> Download PDF
            </a>
            <a href="{{ route('student.payments.show', $payment) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="receipt-container">
            <!-- Receipt Header -->
            <div class="receipt-header">
                @if(isset($company['logo']))
                    <img src="{{ asset($company['logo']) }}" alt="Company Logo" class="company-logo">
                @endif
                <h1 class="company-name">{{ $company['name'] ?? 'Arena Matriks Edu Group' }}</h1>
                <p class="mb-1">{{ $company['address'] ?? 'Wisma Arena Matriks, No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor' }}</p>
                <p class="mb-1">
                    <i class="fas fa-phone me-2"></i>{{ $company['phone'] ?? '03-5523 4567' }}
                    <i class="fas fa-envelope ms-3 me-2"></i>{{ $company['email'] ?? '<a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="93fafdf5fcd3f2e1f6fdf2fef2e7e1faf8e0bdf0fcfe">[email&#160;protected]</a>' }}
                </p>
                @if(isset($company['registration_no']) && $company['registration_no'])
                    <p class="mb-0"><small>Registration No: {{ $company['registration_no'] }}</small></p>
                @endif
            </div>

            <!-- Receipt Title -->
            <div class="text-center mb-4">
                <div class="receipt-title">PAYMENT RECEIPT</div>
                <div class="receipt-number">Receipt No: <strong>{{ $receipt_number ?? 'RCP-' . $payment->payment_number }}</strong></div>
                <div class="text-muted"><small>Date: {{ $payment->payment_date->format('d F Y, h:i A') }}</small></div>
            </div>

            <!-- Student Information -->
            <div class="info-section">
                <h5 class="mb-3"><i class="fas fa-user me-2"></i>Student Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><span class="info-label">Student Name:</span> {{ $student['name'] ?? $payment->invoice->student->user->name }}</p>
                        <p><span class="info-label">Student ID:</span> {{ $student['id'] ?? $payment->invoice->student->student_id }}</p>
                    </div>
                    <div class="col-md-6">
                        @if(isset($student['class']))
                            <p><span class="info-label">Class:</span> {{ $student['class'] }}</p>
                        @endif
                        @if(isset($student['package']))
                            <p><span class="info-label">Package:</span> {{ $student['package'] }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Amount Paid Section -->
            <div class="amount-section">
                <div class="text-center mb-2">
                    <small class="text-muted">AMOUNT PAID</small>
                </div>
                <div class="amount-paid">
                    RM {{ number_format($payment->amount, 2) }}
                </div>
                <div class="text-center mt-2">
                    <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="info-section">
                <h5 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                <table class="payment-details-table">
                    <tr>
                        <td><strong>Payment Number:</strong></td>
                        <td>{{ $payment->payment_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Method:</strong></td>
                        <td>
                            @switch($payment->payment_method)
                                @case('cash')
                                    <i class="fas fa-money-bill-wave text-success me-1"></i> Cash
                                    @break
                                @case('qr')
                                    <i class="fas fa-qrcode text-info me-1"></i> QR Payment
                                    @break
                                @case('bank_transfer')
                                    <i class="fas fa-university text-primary me-1"></i> Bank Transfer
                                    @break
                                @case('online_gateway')
                                    <i class="fas fa-globe text-purple me-1"></i> Online Payment
                                    @break
                                @default
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            @endswitch
                        </td>
                    </tr>
                    @if($payment->reference_number)
                    <tr>
                        <td><strong>Reference Number:</strong></td>
                        <td>{{ $payment->reference_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Payment Date:</strong></td>
                        <td>{{ $payment->payment_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Time:</strong></td>
                        <td>{{ $payment->created_at->format('h:i A') }}</td>
                    </tr>
                    @if(isset($payment_details['processed_by']))
                    <tr>
                        <td><strong>Processed By:</strong></td>
                        <td>{{ $payment_details['processed_by'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Invoice Details -->
            @if($payment->invoice)
            <div class="info-section">
                <h5 class="mb-3"><i class="fas fa-file-invoice me-2"></i>Invoice Details</h5>
                <table class="payment-details-table">
                    <tr>
                        <td><strong>Invoice Number:</strong></td>
                        <td>{{ $payment->invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Invoice Type:</strong></td>
                        <td>{{ $payment->invoice->type_label ?? ucfirst($payment->invoice->type) }}</td>
                    </tr>
                    @if($payment->invoice->billing_period)
                    <tr>
                        <td><strong>Billing Period:</strong></td>
                        <td>{{ $payment->invoice->billing_period }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Invoice Total:</strong></td>
                        <td>RM {{ number_format($payment->invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Paid:</strong></td>
                        <td class="text-success">RM {{ number_format($payment->invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Balance:</strong></td>
                        <td class="{{ $payment->invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                            <strong>RM {{ number_format($payment->invoice->balance, 2) }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
            @endif

            <!-- Important Notes -->
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-info-circle me-2"></i>Important Notes:</h6>
                <ul class="mb-0 small">
                    <li>This is an official receipt for your payment.</li>
                    <li>Please keep this receipt for your records.</li>
                    <li>For any queries, please contact our office during business hours.</li>
                    @if($payment->invoice && $payment->invoice->balance > 0)
                        <li class="text-danger"><strong>Outstanding balance: RM {{ number_format($payment->invoice->balance, 2) }}</strong></li>
                    @endif
                </ul>
            </div>

            <!-- Signature Section -->
            <div class="stamp-section">
                <p class="mb-1"><small>This is a computer-generated receipt.</small></p>
                <p class="mb-4"><small>No signature is required.</small></p>
                <div style="border-top: 1px solid #333; width: 200px; margin-left: auto;">
                    <p class="mt-2 mb-0"><small>Authorized Signature</small></p>
                </div>
            </div>

            <!-- Receipt Footer -->
            <div class="receipt-footer">
                <p class="mb-1"><strong>Thank you for your payment!</strong></p>
                <p class="mb-0"><small>Generated on: {{ $generated_at ?? now()->format('d M Y h:i A') }}</small></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -