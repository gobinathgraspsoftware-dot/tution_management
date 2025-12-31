<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payslip->payslip_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        .payslip {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .payslip-title {
            text-align: center;
            background: #f5f5f5;
            padding: 10px;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .info-box {
            width: 48%;
        }
        .info-box h3 {
            font-size: 12px;
            background: #333;
            color: #fff;
            padding: 5px 10px;
            margin-bottom: 10px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 3px 0;
            font-size: 11px;
        }
        .info-box td:first-child {
            color: #666;
            width: 40%;
        }
        .salary-section {
            margin-bottom: 15px;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .salary-table th {
            background: #333;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
        }
        .salary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .salary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .salary-table .subtotal {
            background: #f9f9f9;
            font-weight: bold;
        }
        .salary-table .total-row {
            background: #e8f5e9;
            font-weight: bold;
            font-size: 13px;
        }
        .salary-table .deduction {
            color: #c62828;
        }
        .salary-table .disabled {
            color: #999;
            font-style: italic;
        }
        .employer-section {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .employer-section h3 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #666;
        }
        .employer-section .contrib-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #ddd;
        }
        .employer-section .contrib-row:last-child {
            border-bottom: none;
            font-weight: bold;
            background: #fff;
            margin: 5px -10px -10px;
            padding: 10px;
        }
        .net-pay-box {
            background: #2e7d32;
            color: #fff;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
        }
        .net-pay-box h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .net-pay-box .amount {
            font-size: 28px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 10px;
            color: #666;
        }
        .print-date {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-approved { background: #cce5ff; color: #004085; }
        .status-paid { background: #d4edda; color: #155724; }
        .payment-info {
            background: #d4edda;
            padding: 10px;
            margin-top: 15px;
            border: 1px solid #c3e6cb;
            font-size: 11px;
        }
        @media print {
            body { padding: 0; }
            .payslip { border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; cursor: pointer; font-size: 14px;">
            🖨️ Print Payslip
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; cursor: pointer; font-size: 14px; margin-left: 10px;">
            ✕ Close
        </button>
    </div>

    <div class="payslip">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'Arena Matriks Edu Group') }}</h1>
            <p>Tuition Centre Management System</p>
        </div>

        <!-- Title -->
        <div class="payslip-title">
            PAYSLIP - {{ $payslip->payslip_number }}
            <span class="status-badge status-{{ $payslip->status }}">{{ strtoupper($payslip->status) }}</span>
        </div>

        <!-- Employee & Period Info -->
        <div class="info-section">
            <div class="info-box">
                <h3>Employee Information</h3>
                <table>
                    <tr>
                        <td>Name</td>
                        <td><strong>{{ $payslip->teacher->user->name }}</strong></td>
                    </tr>
                    <tr>
                        <td>Employee ID</td>
                        <td>{{ $payslip->teacher->teacher_id }}</td>
                    </tr>
                    <tr>
                        <td>IC Number</td>
                        <td>{{ $payslip->teacher->formatted_ic_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Pay Type</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payslip->teacher->pay_type)) }}</td>
                    </tr>
                </table>
            </div>
            <div class="info-box">
                <h3>Pay Period</h3>
                <table>
                    <tr>
                        <td>Period Start</td>
                        <td><strong>{{ $payslip->period_start->format('d M Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Period End</td>
                        <td><strong>{{ $payslip->period_end->format('d M Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Total Hours</td>
                        <td>{{ number_format($payslip->total_hours, 2) }} hours</td>
                    </tr>
                    <tr>
                        <td>Total Classes</td>
                        <td>{{ $payslip->total_classes }} classes</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Salary Breakdown -->
        <div class="salary-section">
            <table class="salary-table">
                <thead>
                    <tr>
                        <th colspan="2">Earnings & Deductions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Earnings -->
                    <tr>
                        <td>Basic Pay</td>
                        <td class="amount">RM {{ number_format($payslip->basic_pay, 2) }}</td>
                    </tr>
                    @if($payslip->allowances > 0)
                    <tr>
                        <td>Allowances</td>
                        <td class="amount">RM {{ number_format($payslip->allowances, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="subtotal">
                        <td>Gross Pay</td>
                        <td class="amount">RM {{ number_format($payslip->basic_pay + $payslip->allowances, 2) }}</td>
                    </tr>

                    <!-- Deductions -->
                    @if($payslip->deductions > 0)
                    <tr>
                        <td class="deduction">(-) Other Deductions</td>
                        <td class="amount deduction">RM {{ number_format($payslip->deductions, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="{{ $payslip->epf_employee > 0 ? 'deduction' : 'disabled' }}">
                            (-) EPF (Employee 11%)
                            @if($payslip->epf_employee == 0)
                                <em>[N/A]</em>
                            @endif
                        </td>
                        <td class="amount {{ $payslip->epf_employee > 0 ? 'deduction' : 'disabled' }}">
                            RM {{ number_format($payslip->epf_employee, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="{{ $payslip->socso_employee > 0 ? 'deduction' : 'disabled' }}">
                            (-) SOCSO (Employee)
                            @if($payslip->socso_employee == 0)
                                <em>[N/A]</em>
                            @endif
                        </td>
                        <td class="amount {{ $payslip->socso_employee > 0 ? 'deduction' : 'disabled' }}">
                            RM {{ number_format($payslip->socso_employee, 2) }}
                        </td>
                    </tr>
                    <tr class="subtotal">
                        <td>Total Deductions</td>
                        <td class="amount deduction">
                            RM {{ number_format($payslip->deductions + $payslip->epf_employee + $payslip->socso_employee, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Net Pay -->
        <div class="net-pay-box">
            <h3>NET PAY</h3>
            <div class="amount">RM {{ number_format($payslip->net_pay, 2) }}</div>
        </div>

        <!-- Employer Contributions -->
        <div class="employer-section">
            <h3>Employer Contributions (For Reference Only)</h3>
            <div class="contrib-row">
                <span>EPF (Employer 13%)</span>
                <span>RM {{ number_format($payslip->epf_employer, 2) }}</span>
            </div>
            <div class="contrib-row">
                <span>SOCSO (Employer)</span>
                <span>RM {{ number_format($payslip->socso_employer, 2) }}</span>
            </div>
            <div class="contrib-row">
                <span>Total Employer Cost</span>
                <span>RM {{ number_format($payslip->net_pay + $payslip->epf_employer + $payslip->socso_employer, 2) }}</span>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="info-section" style="margin-bottom: 0;">
            <div class="info-box">
                <h3>Bank Details</h3>
                <table>
                    <tr>
                        <td>Bank Name</td>
                        <td>{{ $payslip->teacher->bank_name ?? 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <td>Account No</td>
                        <td>{{ $payslip->teacher->bank_account ?? 'Not provided' }}</td>
                    </tr>
                </table>
            </div>
            <div class="info-box">
                <h3>Statutory Numbers</h3>
                <table>
                    <tr>
                        <td>EPF Number</td>
                        <td>{{ $payslip->teacher->epf_number ?? 'Not registered' }}</td>
                    </tr>
                    <tr>
                        <td>SOCSO Number</td>
                        <td>{{ $payslip->teacher->socso_number ?? 'Not registered' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($payslip->status == 'paid')
        <div class="payment-info">
            <strong>✓ Payment Completed:</strong> 
            Paid on {{ $payslip->payment_date?->format('d M Y') ?? 'N/A' }} 
            via {{ ucfirst(str_replace('_', ' ', $payslip->payment_method ?? 'N/A')) }}
            @if($payslip->reference_number)
                (Ref: {{ $payslip->reference_number }})
            @endif
        </div>
        @endif

        <!-- Signatures -->
        <div class="footer">
            <div class="signature-box">
                <div class="signature-line">
                    Employee Signature
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Authorized Signature
                </div>
            </div>
        </div>

        <div class="print-date">
            Generated on {{ now()->format('d M Y, h:i A') }} | This is a computer-generated document.
        </div>
    </div>
</body>
</html>
