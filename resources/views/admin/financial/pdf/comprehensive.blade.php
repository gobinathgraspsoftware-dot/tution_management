<!DOCTYPE html>
<html>
<head>
    <title>Comprehensive Financial Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { page-break-inside: avoid; margin-bottom: 30px; }
        .section-title { background-color: #2196F3; color: white; padding: 10px; font-weight: bold; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .summary-box { background-color: #f9f9f9; padding: 15px; margin: 15px 0; border: 2px solid #2196F3; }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPREHENSIVE FINANCIAL REPORT</h1>
        <p>Period: {{ $reportData['period']['start'] }} to {{ $reportData['period']['end'] }}</p>
        <p>{{ $reportData['period']['days'] }} Days | Generated: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="summary-box">
        <h2>EXECUTIVE SUMMARY</h2>
        <table>
            <tr><td>Total Revenue</td><td class="text-right">RM {{ number_format($reportData['summary']['total_revenue'], 2) }}</td></tr>
            <tr><td>Total Expenses</td><td class="text-right">RM {{ number_format($reportData['summary']['total_expenses'], 2) }}</td></tr>
            <tr><td>Net Profit/Loss</td><td class="text-right"><strong>RM {{ number_format($reportData['summary']['net_profit'], 2) }}</strong></td></tr>
            <tr><td>Profit Margin</td><td class="text-right"><strong>{{ number_format($reportData['summary']['profit_margin'], 2) }}%</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">REVENUE BREAKDOWN</div>
        <table>
            @foreach($reportData['revenue'] as $key => $data)
                @if($key !== 'total')
                <tr><td>{{ ucwords(str_replace('_', ' ', $key)) }}</td><td class="text-right">RM {{ number_format($data['amount'], 2) }}</td></tr>
                @endif
            @endforeach
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td>TOTAL</td><td class="text-right">RM {{ number_format($reportData['revenue']['total'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">EXPENSE BREAKDOWN</div>
        <table>
            @foreach($reportData['expenses']['by_category'] as $expense)
            <tr><td>{{ $expense['category'] }}</td><td class="text-right">RM {{ number_format($expense['amount'], 2) }}</td></tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td>TOTAL</td><td class="text-right">RM {{ number_format($reportData['expenses']['total'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">FINANCIAL ANALYSIS</div>
        <p><strong>Status:</strong> {{ $reportData['profit_loss']['status'] }}</p>
        <p><strong>Profit Margin:</strong> {{ number_format($reportData['profit_loss']['profit_margin'], 2) }}%</p>
        <p><strong>Operating Ratio:</strong> {{ number_format($reportData['profit_loss']['operating_ratio'], 2) }}%</p>
        
        @if(!empty($reportData['profit_loss']['recommendations']))
        <h4>Recommendations:</h4>
        <ul>
            @foreach($reportData['profit_loss']['recommendations'] as $rec)
            <li>{{ $rec }}</li>
            @endforeach
        </ul>
        @endif
    </div>
</body>
</html>
