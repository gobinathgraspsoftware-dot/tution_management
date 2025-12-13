<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ $overview['seminar']->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .info-section {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #2c3e50;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .info-row {
            display: table-row;
        }
        .info-label, .info-value {
            display: table-cell;
            padding: 5px;
        }
        .info-label {
            width: 40%;
            font-weight: bold;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 2px solid #dee2e6;
            margin: 0 5px;
        }
        .summary-card h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #6c757d;
        }
        .summary-card .amount {
            font-size: 22px;
            font-weight: bold;
            margin: 10px 0;
        }
        .summary-card.revenue .amount { color: #28a745; }
        .summary-card.expense .amount { color: #dc3545; }
        .summary-card.profit .amount { color: #007bff; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin: 30px 0 15px 0;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
        }
        .breakdown-table td:first-child {
            font-weight: bold;
        }
        .breakdown-table td:last-child {
            text-align: right;
        }
        .text-right { text-align: right; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .text-warning { color: #ffc107; }
        .profitability-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        .profitability-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        .profitability-item .label {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .profitability-item .value {
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>SEMINAR FINANCIAL REPORT</h1>
        <p><strong>{{ $overview['seminar']->name }}</strong></p>
        <p>Code: {{ $overview['seminar']->code }} | Date: {{ $overview['seminar']->date->format('d F Y') }}</p>
        <p>Generated on: {{ now()->format('d F Y h:i A') }}</p>
    </div>

    <!-- Seminar Information -->
    <div class="info-section">
        <h3>Seminar Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Seminar Type:</div>
                <div class="info-value">{{ ucfirst($overview['seminar']->type) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($overview['seminar']->status) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Venue:</div>
                <div class="info-value">{{ $overview['seminar']->is_online ? 'Online Seminar' : $overview['seminar']->venue }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Capacity:</div>
                <div class="info-value">{{ $overview['seminar']->capacity ?: 'Unlimited' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Participants:</div>
                <div class="info-value">{{ $overview['revenue']['participant_count'] }}</div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card revenue">
            <h4>TOTAL REVENUE</h4>
            <div class="amount">RM {{ number_format($overview['revenue']['total'], 2) }}</div>
            <small>{{ $overview['revenue']['paid_count'] }} paid participants</small>
        </div>
        <div class="summary-card expense">
            <h4>TOTAL EXPENSES</h4>
            <div class="amount">RM {{ number_format($overview['expenses']['total'], 2) }}</div>
            <small>{{ $overview['expenses']['approved_count'] }} approved items</small>
        </div>
        <div class="summary-card profit">
            <h4>NET PROFIT/LOSS</h4>
            <div class="amount" style="color: {{ $overview['profitability']['net_profit'] >= 0 ? '#28a745' : '#dc3545' }}">
                RM {{ number_format($overview['profitability']['net_profit'], 2) }}
            </div>
            <small>{{ $overview['profitability']['profit_margin'] }}% margin</small>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <h2 class="section-title">Revenue Breakdown</h2>
    <table class="breakdown-table">
        <tr>
            <th>Description</th>
            <th class="text-right">Amount (RM)</th>
        </tr>
        <tr>
            <td>Paid Revenue</td>
            <td class="text-right text-success"><strong>{{ number_format($overview['revenue']['total'], 2) }}</strong></td>
        </tr>
        <tr>
            <td>Pending Revenue</td>
            <td class="text-right text-warning">{{ number_format($overview['revenue']['pending'], 2) }}</td>
        </tr>
        <tr>
            <td>Refunded</td>
            <td class="text-right text-danger">{{ number_format($overview['revenue']['refunded'], 2) }}</td>
        </tr>
    </table>

    <h3 style="font-size: 14px; margin-top: 20px;">By Payment Method</h3>
    <table class="breakdown-table">
        <tr>
            <th>Payment Method</th>
            <th class="text-right">Amount (RM)</th>
        </tr>
        @forelse($overview['revenue']['by_method'] as $method => $amount)
        <tr>
            <td>{{ ucfirst($method) }}</td>
            <td class="text-right">{{ number_format($amount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" style="text-align: center; color: #6c757d;">No payment data available</td>
        </tr>
        @endforelse
    </table>

    <!-- Expense Breakdown -->
    <h2 class="section-title">Expense Breakdown</h2>
    <table class="breakdown-table">
        <tr>
            <th>Description</th>
            <th class="text-right">Amount (RM)</th>
        </tr>
        <tr>
            <td>Approved Expenses</td>
            <td class="text-right text-danger"><strong>{{ number_format($overview['expenses']['total'], 2) }}</strong></td>
        </tr>
        <tr>
            <td>Pending Approval</td>
            <td class="text-right text-warning">{{ number_format($overview['expenses']['pending'], 2) }}</td>
        </tr>
    </table>

    <h3 style="font-size: 14px; margin-top: 20px;">By Category</h3>
    <table class="breakdown-table">
        <tr>
            <th>Category</th>
            <th class="text-right">Amount (RM)</th>
        </tr>
        @forelse($overview['expenses']['by_category'] as $category => $amount)
        <tr>
            <td>{{ \App\Services\SeminarAccountingService::getCategoryLabel($category) }}</td>
            <td class="text-right">{{ number_format($amount, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2" style="text-align: center; color: #6c757d;">No expenses recorded</td>
        </tr>
        @endforelse
    </table>

    <!-- Profitability Analysis -->
    <h2 class="section-title">Profitability Analysis</h2>
    <div class="profitability-grid">
        <div class="profitability-item">
            <div class="label">Net Profit/Loss</div>
            <div class="value" style="color: {{ $overview['profitability']['net_profit'] >= 0 ? '#28a745' : '#dc3545' }}">
                RM {{ number_format($overview['profitability']['net_profit'], 2) }}
            </div>
        </div>
        <div class="profitability-item">
            <div class="label">Profit Margin</div>
            <div class="value">{{ $overview['profitability']['profit_margin'] }}%</div>
        </div>
        <div class="profitability-item">
            <div class="label">ROI</div>
            <div class="value">{{ $overview['profitability']['roi'] }}%</div>
        </div>
        <div class="profitability-item">
            <div class="label">Status</div>
            <div class="value" style="color: {{ $overview['profitability']['net_profit'] >= 0 ? '#28a745' : '#dc3545' }}">
                {{ $overview['profitability']['status'] }}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Arena Matriks Edu Group - Tuition Centre Management System</p>
        <p>This is a system-generated report. For inquiries, please contact the administration.</p>
    </div>
</body>
</html>
