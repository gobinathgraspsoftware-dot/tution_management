<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip->payslip_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .payslip-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .payslip-title {
            color: #667eea;
            font-size: 28px;
            font-weight: bold;
        }
        .table-salary td {
            padding: 8px 12px;
        }
        .net-pay-row {
            background-color: #e8f5e9;
            font-weight: bold;
            font-size: 16px;
        }
        .footer-note {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>

        <!-- Payslip Content -->
        <div class="payslip-header">
            <div class="row">
                <div class="col-8">
                    <h1 class="payslip-title">ARENA MATRIKS EDU GROUP</h1>
                    <p class="mb-0">Tuition Center Management System</p>
                </div>
                <div class="col-4 text-end">
                    <h3>PAYSLIP</h3>
                    <p class="mb-0"><strong>{{ $payslip->payslip_number }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Teacher and Period Info -->
        <div class="row mb-4">
            <div class="col-6">
                <h5>Teacher Information</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="120"><strong>Name:</strong></td>
                        <td>{{ $payslip->teacher->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $payslip->teacher->user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>{{ $payslip->teacher->user->phone }}</td>
                    </tr>
                    <tr>
                        <td><strong>IC Number:</strong></td>
                        <td>{{ $payslip->teacher->ic_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Employment:</strong></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payslip->teacher->employment_type)) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <h5>Payslip Information</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="120"><strong>Period:</strong></td>
                        <td>{{ $payslip->period_start->format('d M Y') }} to {{ $payslip->period_end->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pay Type:</strong></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Hours:</strong></td>
                        <td>{{ number_format($payslip->total_hours, 2) }} hours</td>
                    </tr>
                    <tr>
                        <td><strong>Total Classes:</strong></td>
                        <td>{{ $payslip->total_classes }} classes</td>
                    </tr>
                    <tr>
                        <td><strong>Generated:</strong></td>
                        <td>{{ $payslip->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Salary Breakdown -->
        <h5 class="mb-3">Salary Breakdown</h5>
        <table class="table table-bordered table-salary">
            <tbody>
                <tr>
                    <td width="60%"><strong>Basic Pay</strong></td>
                    <td width="40%" class="text-end">RM {{ number_format($payslip->basic_pay, 2) }}</td>
                </tr>
                @if($payslip->allowances > 0)
                <tr>
                    <td>Add: Allowances</td>
                    <td class="text-end text-success">+ RM {{ number_format($payslip->allowances, 2) }}</td>
                </tr>
                @endif
                @if($payslip->deductions > 0)
                <tr>
                    <td>Less: Deductions</td>
                    <td class="text-end text-danger">- RM {{ number_format($payslip->deductions, 2) }}</td>
                </tr>
                @endif
                @if($payslip->epf_employee > 0)
                <tr>
                    <td>Less: EPF Employee Contribution (11%)</td>
                    <td class="text-end text-danger">- RM {{ number_format($payslip->epf_employee, 2) }}</td>
                </tr>
                @endif
                @if($payslip->socso_employee > 0)
                <tr>
                    <td>Less: SOCSO Employee Contribution</td>
                    <td class="text-end text-danger">- RM {{ number_format($payslip->socso_employee, 2) }}</td>
                </tr>
                @endif
                <tr class="net-pay-row">
                    <td><strong>NET PAY</strong></td>
                    <td class="text-end"><strong>RM {{ number_format($payslip->net_pay, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        @if($payslip->epf_employer > 0 || $payslip->socso_employer > 0)
        <h6 class="mt-4 mb-2">Employer Contributions</h6>
        <table class="table table-bordered table-sm">
            <tbody>
                @if($payslip->epf_employer > 0)
                <tr>
                    <td width="60%">EPF Employer Contribution (13%)</td>
                    <td width="40%" class="text-end">RM {{ number_format($payslip->epf_employer, 2) }}</td>
                </tr>
                @endif
                @if($payslip->socso_employer > 0)
                <tr>
                    <td>SOCSO Employer Contribution</td>
                    <td class="text-end">RM {{ number_format($payslip->socso_employer, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif

        <!-- Payment Information -->
        @if($payslip->status == 'paid' && $payslip->payment_date)
        <div class="mt-4">
            <h5>Payment Information</h5>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="150"><strong>Payment Date:</strong></td>
                    <td>{{ $payslip->payment_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td>{{ ucfirst($payslip->payment_method) }}</td>
                </tr>
                @if($payslip->reference_number)
                <tr>
                    <td><strong>Reference Number:</strong></td>
                    <td>{{ $payslip->reference_number }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <!-- Bank Details -->
        @if($payslip->teacher->bank_name)
        <div class="mt-4">
            <h5>Bank Details</h5>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="150"><strong>Bank Name:</strong></td>
                    <td>{{ $payslip->teacher->bank_name }}</td>
                </tr>
                <tr>
                    <td><strong>Account Number:</strong></td>
                    <td>{{ $payslip->teacher->bank_account }}</td>
                </tr>
                @if($payslip->teacher->epf_number)
                <tr>
                    <td><strong>EPF Number:</strong></td>
                    <td>{{ $payslip->teacher->epf_number }}</td>
                </tr>
                @endif
                @if($payslip->teacher->socso_number)
                <tr>
                    <td><strong>SOCSO Number:</strong></td>
                    <td>{{ $payslip->teacher->socso_number }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <!-- Notes -->
        @if($payslip->notes)
        <div class="mt-4">
            <h5>Notes</h5>
            <p>{{ $payslip->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer-note">
            <p class="mb-1">This is a computer-generated payslip and does not require a signature.</p>
            <p class="mb-0"><strong>Arena Matriks Edu Group</strong> | Generated on {{ now()->format('d M Y, g:i A') }}</p>
        </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
