<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Cash Report - {{ $report->report_date->format('d M Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h2 {
            font-size: 18px;
            color: #333;
        }
        .report-title .date {
            font-size: 14px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .status-open {
            background: #ffc107;
            color: #000;
        }
        .status-closed {
            background: #198754;
            color: #fff;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px 10px;
            background: #f8f9fa;
            border-left: 3px solid #0d6efd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        table th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #198754;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-info {
            color: #0dcaf0;
        }
        .text-muted {
            color: #6c757d;
        }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #dee2e6;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #333;
            margin-top: 5px;
            padding-top: 10px;
        }
        .two-column {
            display: flex;
            gap: 20px;
        }
        .two-column > div {
            flex: 1;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        .variance-positive {
            background: #d1e7dd;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .variance-negative {
            background: #f8d7da;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .variance-balanced {
            background: #d1e7dd;
            padding: 5px 10px;
            border-radius: 3px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $company['name'] ?? 'Arena Matriks Edu Group' }}</h1>
        <p>{{ $company['address'] ?? '' }}</p>
        <p>Tel: {{ $company['phone'] ?? '' }} | Email: {{ $company['email'] ?? '' }}</p>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        <h2>Daily Cash Report</h2>
        <div class="date">{{ $report->report_date->format('l, d F Y') }}</div>
        <div style="margin-top: 10px;">
            <span class="status-badge {{ $report->status == 'open' ? 'status-open' : 'status-closed' }}">
                {{ ucfirst($report->status) }}
            </span>
        </div>
    </div>

    <!-- Report Info -->
    <div class="section">
        <div class="section-title">Report Information</div>
        <table>
            <tr>
                <td width="25%"><strong>Opened By:</strong></td>
                <td width="25%">{{ $report->openedBy->name ?? 'N/A' }}</td>
                <td width="25%"><strong>Opened At:</strong></td>
                <td width="25%">{{ $report->opened_at ? $report->opened_at->format('h:i A') : 'N/A' }}</td>
            </tr>
            @if($report->status == 'closed')
            <tr>
                <td><strong>Closed By:</strong></td>
                <td>{{ $report->closedBy->name ?? 'N/A' }}</td>
                <td><strong>Closed At:</strong></td>
                <td>{{ $report->closed_at ? $report->closed_at->format('h:i A') : 'N/A' }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Cash Summary & Sales Summary -->
    <div class="two-column">
        <!-- Cash Summary -->
        <div class="section">
            <div class="section-title">Cash Summary</div>
            <div class="summary-box">
                <div class="summary-row">
                    <span>Opening Cash</span>
                    <span>RM {{ number_format($report->opening_cash, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Cash Sales</span>
                    <span class="text-success">+ RM {{ number_format($report->cash_sales, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Cash Refunds</span>
                    <span class="text-danger">- RM {{ number_format($report->cash_refunds ?? 0, 2) }}</span>
                </div>
                <div class="summary-row total">
                    <span>Expected Cash</span>
                    <span>RM {{ number_format($report->expected_cash, 2) }}</span>
                </div>
                @if($report->status == 'closed')
                <div class="summary-row">
                    <span>Actual Cash</span>
                    <span>RM {{ number_format($report->actual_cash, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Variance</span>
                    @php
                        $variance = $report->actual_cash - $report->expected_cash;
                    @endphp
                    <span class="{{ $variance == 0 ? 'variance-balanced' : ($variance > 0 ? 'variance-positive' : 'variance-negative') }}">
                        @if($variance == 0)
                            Balanced
                        @elseif($variance > 0)
                            + RM {{ number_format($variance, 2) }} (Over)
                        @else
                            - RM {{ number_format(abs($variance), 2) }} (Short)
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Sales Summary -->
        <div class="section">
            <div class="section-title">Sales Summary</div>
            <div class="summary-box">
                <div class="summary-row">
                    <span>Total Transactions</span>
                    <span>{{ number_format($report->total_transactions) }}</span>
                </div>
                <div class="summary-row">
                    <span>Completed</span>
                    <span class="text-success">{{ number_format($report->completed_transactions ?? $report->total_transactions) }}</span>
                </div>
                <div class="summary-row">
                    <span>Voided</span>
                    <span class="text-danger">{{ number_format($report->voided_transactions ?? 0) }}</span>
                </div>
                <div class="summary-row">
                    <span>Refunded</span>
                    <span class="text-info">{{ number_format($report->refunded_transactions ?? 0) }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total Sales</span>
                    <span>RM {{ number_format($report->total_sales, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <div class="section">
        <div class="section-title">Payment Methods Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cash</td>
                    <td class="text-center">{{ $report->cash_transactions ?? '-' }}</td>
                    <td class="text-right">RM {{ number_format($report->cash_sales, 2) }}</td>
                    <td class="text-right">
                        {{ $report->total_sales > 0 ? number_format(($report->cash_sales / $report->total_sales) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr>
                    <td>QR Payment</td>
                    <td class="text-center">{{ $report->qr_transactions ?? '-' }}</td>
                    <td class="text-right">RM {{ number_format($report->qr_sales, 2) }}</td>
                    <td class="text-right">
                        {{ $report->total_sales > 0 ? number_format(($report->qr_sales / $report->total_sales) * 100, 1) : 0 }}%
                    </td>
                </tr>
                <tr style="font-weight: bold; background: #f8f9fa;">
                    <td>Total</td>
                    <td class="text-center">{{ $report->total_transactions }}</td>
                    <td class="text-right">RM {{ number_format($report->total_sales, 2) }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Transactions List -->
    @if(isset($transactions) && $transactions->count() > 0)
    <div class="section">
        <div class="section-title">Transactions ({{ $transactions->count() }} total)</div>
        <table>
            <thead>
                <tr>
                    <th>Transaction #</th>
                    <th class="text-center">Time</th>
                    <th class="text-center">Items</th>
                    <th class="text-center">Payment</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td class="text-center">{{ $transaction->created_at->format('h:i A') }}</td>
                    <td class="text-center">{{ $transaction->items_count ?? $transaction->items->count() }}</td>
                    <td class="text-center">{{ ucfirst($transaction->payment_method) }}</td>
                    <td class="text-center">
                        @if($transaction->status == 'completed')
                            <span class="text-success">✓</span>
                        @elseif($transaction->status == 'voided')
                            <span class="text-danger">✗</span>
                        @else
                            <span class="text-info">↩</span>
                        @endif
                    </td>
                    <td class="text-right">RM {{ number_format($transaction->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Notes -->
    @if($report->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <p style="padding: 10px; background: #f8f9fa; border-radius: 5px;">
            {{ $report->notes }}
        </p>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <strong>Cashier</strong><br>
                {{ $report->openedBy->name ?? 'N/A' }}
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>Supervisor</strong><br>
                {{ $report->closedBy->name ?? '_________________' }}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
        <p>{{ $company['name'] ?? 'Arena Matriks Edu Group' }} - Daily Cash Report</p>
    </div>
</body>
</html>
