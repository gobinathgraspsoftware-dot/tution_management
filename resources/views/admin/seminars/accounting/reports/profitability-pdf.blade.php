<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profitability Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
            font-size: 11px;
        }
        .filter-info {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 10px;
        }
        .filter-info strong {
            color: #2c3e50;
        }
        .summary-section {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-section h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-label, .summary-value {
            display: table-cell;
            padding: 5px;
        }
        .summary-label {
            width: 60%;
            font-weight: bold;
        }
        .summary-value {
            text-align: right;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        table th {
            background: #2c3e50;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
        }
        table td {
            padding: 6px 5px;
            border-bottom: 1px solid #dee2e6;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        table tfoot td {
            font-weight: bold;
            background: #e9ecef;
            border-top: 2px solid #2c3e50;
        }
        .section-title {
            font-size: 16px;
            color: #2c3e50;
            margin: 25px 0 15px 0;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        .text-warning { color: #ffc107; font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>SEMINAR PROFITABILITY REPORT</h1>
        <p>Comparative Financial Analysis Across Multiple Seminars</p>
        <p>Generated on: {{ now()->format('d F Y h:i A') }}</p>
    </div>

    <!-- Filter Information -->
    @if($filters['date_from'] || $filters['date_to'] || $filters['type'] || $filters['status'])
    <div class="filter-info">
        <strong>Applied Filters:</strong>
        @if($filters['date_from'])
            Date From: {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }} |
        @endif
        @if($filters['date_to'])
            Date To: {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }} |
        @endif
        @if($filters['type'])
            Type: {{ ucfirst($filters['type']) }} |
        @endif
        @if($filters['status'])
            Status: {{ ucfirst($filters['status']) }}
        @endif
    </div>
    @endif

    <!-- Overall Summary -->
    <div class="summary-section">
        <h3>Overall Performance Summary</h3>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-label">Total Seminars:</div>
                <div class="summary-value">{{ $report['summary']['total_seminars'] }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Profitable Seminars:</div>
                <div class="summary-value" style="color: #28a745;">{{ $report['summary']['profitable_count'] }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Loss-Making Seminars:</div>
                <div class="summary-value" style="color: #dc3545;">{{ $report['summary']['loss_count'] }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Revenue:</div>
                <div class="summary-value">RM {{ number_format($report['summary']['total_revenue'], 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Expenses:</div>
                <div class="summary-value">RM {{ number_format($report['summary']['total_expenses'], 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Profit/Loss:</div>
                <div class="summary-value" style="color: {{ $report['summary']['total_profit'] >= 0 ? '#28a745' : '#dc3545' }}; font-size: 15px;">
                    RM {{ number_format($report['summary']['total_profit'], 2) }}
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Average Profit per Seminar:</div>
                <div class="summary-value">RM {{ number_format($report['summary']['average_profit'], 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Overall Profit Margin:</div>
                <div class="summary-value">{{ number_format($report['summary']['overall_margin'], 2) }}%</div>
            </div>
        </div>
    </div>

    <!-- Detailed Seminar Performance -->
    <h2 class="section-title">Detailed Seminar Performance</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Code</th>
                <th style="width: 25%;">Seminar Name</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 7%;">Participants</th>
                <th style="width: 12%;">Revenue</th>
                <th style="width: 12%;">Expenses</th>
                <th style="width: 12%;">Profit/Loss</th>
                <th style="width: 8%;">Margin %</th>
                <th style="width: 6%;">Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['seminars'] as $seminar)
            <tr>
                <td>{{ $seminar['seminar_code'] }}</td>
                <td>{{ Str::limit($seminar['seminar_name'], 35) }}</td>
                <td>{{ $seminar['date']->format('d/m/Y') }}</td>
                <td class="text-center">{{ $seminar['participants'] }}</td>
                <td class="text-right">{{ number_format($seminar['revenue'], 2) }}</td>
                <td class="text-right">{{ number_format($seminar['expenses'], 2) }}</td>
                <td class="text-right {{ $seminar['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($seminar['profit'], 2) }}
                </td>
                <td class="text-center">{{ $seminar['profit_margin'] }}%</td>
                <td class="text-center">
                    @if($seminar['profit'] > 0)
                        <span class="badge badge-success">Profit</span>
                    @elseif($seminar['profit'] < 0)
                        <span class="badge badge-danger">Loss</span>
                    @else
                        <span class="badge badge-warning">Break Even</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #6c757d;">
                    No seminars found matching the selected criteria
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(count($report['seminars']) > 0)
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right">RM {{ number_format($report['summary']['total_revenue'], 2) }}</td>
                <td class="text-right">RM {{ number_format($report['summary']['total_expenses'], 2) }}</td>
                <td class="text-right {{ $report['summary']['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    RM {{ number_format($report['summary']['total_profit'], 2) }}
                </td>
                <td class="text-center">{{ number_format($report['summary']['overall_margin'], 2) }}%</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Key Insights -->
    @if(count($report['seminars']) > 0)
    <div class="summary-section">
        <h3>Key Insights</h3>
        <div class="summary-grid">
            @php
                $mostProfitable = collect($report['seminars'])->sortByDesc('profit')->first();
                $leastProfitable = collect($report['seminars'])->sortBy('profit')->first();
                $highestMargin = collect($report['seminars'])->sortByDesc('profit_margin')->first();
            @endphp
            
            @if($mostProfitable)
            <div class="summary-row">
                <div class="summary-label">Most Profitable Seminar:</div>
                <div class="summary-value" style="color: #28a745;">
                    {{ $mostProfitable['seminar_name'] }} (RM {{ number_format($mostProfitable['profit'], 2) }})
                </div>
            </div>
            @endif
            
            @if($leastProfitable)
            <div class="summary-row">
                <div class="summary-label">Least Profitable Seminar:</div>
                <div class="summary-value" style="color: #dc3545;">
                    {{ $leastProfitable['seminar_name'] }} (RM {{ number_format($leastProfitable['profit'], 2) }})
                </div>
            </div>
            @endif
            
            @if($highestMargin)
            <div class="summary-row">
                <div class="summary-label">Highest Profit Margin:</div>
                <div class="summary-value">
                    {{ $highestMargin['seminar_name'] }} ({{ $highestMargin['profit_margin'] }}%)
                </div>
            </div>
            @endif
            
            <div class="summary-row">
                <div class="summary-label">Success Rate:</div>
                <div class="summary-value">
                    {{ $report['summary']['total_seminars'] > 0 ? round(($report['summary']['profitable_count'] / $report['summary']['total_seminars']) * 100, 1) : 0 }}% of seminars were profitable
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Arena Matriks Edu Group - Tuition Centre Management System</strong></p>
        <p>This is a system-generated report. For inquiries, please contact the administration.</p>
        <p>Report generated by Seminar Accounting Module v1.0</p>
    </div>
</body>
</html>
