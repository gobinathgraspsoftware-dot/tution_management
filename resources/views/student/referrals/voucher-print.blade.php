<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Voucher - {{ $voucher->voucher_code }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        .voucher-container {
            max-width: 800px;
            margin: 50px auto;
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        }

        .voucher-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
        }

        .voucher-code-display {
            background: white;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .voucher-code {
            font-size: 2.5rem;
            font-weight: bold;
            letter-spacing: 3px;
            color: #667eea;
            font-family: monospace;
        }

        .voucher-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #28a745;
        }

        .voucher-details {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }

        .voucher-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #667eea;
            color: #666;
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Button -->
        <div class="text-center mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="fas fa-print me-2"></i> Print Voucher
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">
                <i class="fas fa-times me-2"></i> Close
            </button>
        </div>

        <!-- Voucher Container -->
        <div class="voucher-container">
            <!-- Header -->
            <div class="voucher-header">
                <h1 class="mb-2" style="color: #667eea;">
                    <i class="fas fa-gift me-2"></i> Referral Voucher
                </h1>
                <h4 class="text-muted">Arena Matriks Edu Group</h4>
            </div>

            <!-- Status Badge -->
            <div class="text-center mb-4">
                @if($voucher->status === 'active')
                    <span class="status-badge bg-success text-white">
                        <i class="fas fa-check-circle me-2"></i> ACTIVE
                    </span>
                @elseif($voucher->status === 'used')
                    <span class="status-badge bg-secondary text-white">
                        <i class="fas fa-times-circle me-2"></i> USED
                    </span>
                @else
                    <span class="status-badge bg-danger text-white">
                        <i class="fas fa-exclamation-circle me-2"></i> EXPIRED
                    </span>
                @endif
            </div>

            <!-- Voucher Amount -->
            <div class="text-center mb-4">
                <div class="text-muted mb-2">VOUCHER VALUE</div>
                <div class="voucher-amount">RM {{ number_format($voucher->amount, 2) }}</div>
            </div>

            <!-- Voucher Code -->
            <div class="voucher-code-display">
                <div class="text-muted mb-2" style="font-size: 0.9rem;">VOUCHER CODE</div>
                <div class="voucher-code">{{ $voucher->voucher_code }}</div>
            </div>

            <!-- Voucher Details -->
            <div class="voucher-details">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Issued To:</label>
                        <div class="fw-bold">{{ $voucher->student->user->name }}</div>
                        <div class="text-muted small">{{ $voucher->student->student_id }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Issue Date:</label>
                        <div class="fw-bold">{{ $voucher->created_at->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="row">
                    @if($voucher->referral && $voucher->referral->referred)
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Earned From Referral:</label>
                        <div class="fw-bold">{{ $voucher->referral->referred->user->name }}</div>
                    </div>
                    @endif

                    <div class="col-md-6 mb-3">
                        @if($voucher->status === 'active')
                            <label class="text-muted small">Expiry Date:</label>
                            <div class="fw-bold {{ $voucher->expires_at && $voucher->expires_at->diffInDays(now()) <= 7 ? 'text-danger' : '' }}">
                                @if($voucher->expires_at)
                                    {{ $voucher->expires_at->format('d M Y') }}
                                @else
                                    No Expiry
                                @endif
                            </div>
                        @elseif($voucher->status === 'used')
                            <label class="text-muted small">Used On:</label>
                            <div class="fw-bold">{{ $voucher->used_at ? $voucher->used_at->format('d M Y') : 'N/A' }}</div>
                        @else
                            <label class="text-muted small">Expired On:</label>
                            <div class="fw-bold text-danger">{{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'N/A' }}</div>
                        @endif
                    </div>
                </div>

                @if($voucher->status === 'used' && $voucher->usedOnInvoice)
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            This voucher was used on Invoice: <strong>{{ $voucher->usedOnInvoice->invoice_number }}</strong>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Terms & Conditions -->
            <div class="voucher-footer">
                <h6 class="mb-3"><strong>Terms & Conditions:</strong></h6>
                <ul class="list-unstyled small text-start" style="max-width: 600px; margin: 0 auto;">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Valid only for {{ $voucher->student->user->name }}</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Can be used for tuition fee payments only</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Not exchangeable for cash</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Cannot be combined with other vouchers</li>
                    @if($voucher->expires_at)
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Must be used before {{ $voucher->expires_at->format('d M Y') }}</li>
                    @endif
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Present this code during payment</li>
                </ul>

                <div class="mt-4 pt-3 border-top">
                    <p class="mb-1"><strong>Arena Matriks Edu Group</strong></p>
                    <p class="mb-1">Tuition Centre Management System</p>
                    <p class="mb-0">Printed on: {{ now()->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
