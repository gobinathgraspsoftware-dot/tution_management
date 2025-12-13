<!DOCTYPE html>
<html>
<head>
    <title>Category Revenue Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot { font-weight: bold; background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CATEGORY REVENUE ANALYSIS</h1>
        <p>Period: {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Amount (RM)</th>
                <th class="text-center">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($revenueBreakdown as $key => $data)
                @if($key !== 'total')
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td class="text-right">{{ number_format($data['amount'], 2) }}</td>
                    <td class="text-center">{{ number_format($data['percentage'], 2) }}%</td>
                </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL REVENUE</td>
                <td class="text-right">{{ number_format($revenueBreakdown['total'], 2) }}</td>
                <td class="text-center">100.00%</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
