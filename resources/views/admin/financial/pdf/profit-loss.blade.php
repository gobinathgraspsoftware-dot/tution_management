<!DOCTYPE html>
<html>
<head>
    <title>Profit & Loss Statement</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section-title { background-color: #f0f0f0; padding: 8px; font-weight: bold; margin-top: 15px; }
        .row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #eee; }
        .total-row { background-color: #e8e8e8; padding: 10px; font-weight: bold; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; }
        .text-right { text-align: right; }
        .summary-box { background-color: #f9f9f9; padding: 15px; margin-top: 20px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PROFIT & LOSS STATEMENT</h1>
        <p>Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="section-title">REVENUE</div>
    <table>
        <tr><td>Student Fees (Online)</td><td class="text-right">RM {{ number_format($statement['revenue']['student_fees_online'], 2) }}</td></tr>
        <tr><td>Student Fees (Physical)</td><td class="text-right">RM {{ number_format($statement['revenue']['student_fees_physical'], 2) }}</td></tr>
        <tr><td>Seminar Revenue</td><td class="text-right">RM {{ number_format($statement['revenue']['seminar_revenue'], 2) }}</td></tr>
        <tr><td>Cafeteria Sales</td><td class="text-right">RM {{ number_format($statement['revenue']['cafeteria_sales'], 2) }}</td></tr>
        <tr><td>Material Sales</td><td class="text-right">RM {{ number_format($statement['revenue']['material_sales'], 2) }}</td></tr>
        <tr><td>Other Revenue</td><td class="text-right">RM {{ number_format($statement['revenue']['other_revenue'], 2) }}</td></tr>
    </table>
    <div class="total-row">
        <table><tr><td><strong>TOTAL REVENUE</strong></td><td class="text-right"><strong>RM {{ number_format($statement['revenue']['total_revenue'], 2) }}</strong></td></tr></table>
    </div>

    <div class="section-title">EXPENSES</div>
    <table>
        @foreach($statement['expenses']['by_category'] as $expense)
        <tr><td>{{ $expense->name }}</td><td class="text-right">RM {{ number_format($expense->total, 2) }}</td></tr>
        @endforeach
    </table>
    <div class="total-row">
        <table><tr><td><strong>TOTAL EXPENSES</strong></td><td class="text-right"><strong>RM {{ number_format($statement['expenses']['total_expenses'], 2) }}</strong></td></tr></table>
    </div>

    <div class="summary-box">
        <h3>SUMMARY</h3>
        <table>
            <tr><td>Gross Profit/Loss</td><td class="text-right"><strong>RM {{ number_format($statement['summary']['gross_profit'], 2) }}</strong></td></tr>
            <tr><td>Profit Margin</td><td class="text-right"><strong>{{ number_format($statement['summary']['profit_margin'], 2) }}%</strong></td></tr>
            <tr><td>Status</td><td class="text-right"><strong>{{ $statement['summary']['status'] }}</strong></td></tr>
        </table>
    </div>

    @if(!empty($analysis['recommendations']))
    <div class="section-title">RECOMMENDATIONS</div>
    <ul>
        @foreach($analysis['recommendations'] as $recommendation)
        <li>{{ $recommendation }}</li>
        @endforeach
    </ul>
    @endif
</body>
</html>
