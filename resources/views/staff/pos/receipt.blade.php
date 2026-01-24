<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            background: #f5f5f5;
            padding: 20px;
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px dashed #333;
            margin-bottom: 15px;
        }
        .receipt-header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .receipt-header p {
            font-size: 11px;
            color: #666;
        }
        .receipt-info {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ccc;
        }
        .receipt-info table {
            width: 100%;
        }
        .receipt-info td {
            padding: 2px 0;
        }
        .receipt-info .label {
            color: #666;
        }
        .receipt-items {
            margin-bottom: 15px;
        }
        .receipt-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-items th {
            text-align: left;
            border-bottom: 1px solid #333;
            padding: 5px 0;
            font-size: 11px;
        }
        .receipt-items th:last-child,
        .receipt-items td:last-child {
            text-align: right;
        }
        .receipt-items td {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            vertical-align: top;
        }
        .item-name {
            font-weight: bold;
        }
        .item-details {
            font-size: 10px;
            color: #666;
        }
        .receipt-totals {
            border-top: 2px dashed #333;
            padding-top: 10px;
            margin-bottom: 15px;
        }
        .receipt-totals table {
            width: 100%;
        }
        .receipt-totals td {
            padding: 3px 0;
        }
        .receipt-totals td:last-child {
            text-align: right;
        }
        .receipt-totals .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .receipt-payment {
            background: #f8f8f8;
            padding: 10px;
            margin-bottom: 15px;
        }
        .receipt-payment table {
            width: 100%;
        }
        .receipt-payment td {
            padding: 2px 0;
        }
        .receipt-payment td:last-child {
            text-align: right;
        }
        .receipt-footer {
            text-align: center;
            padding-top: 15px;
            border-top: 2px dashed #333;
        }
        .receipt-footer p {
            margin-bottom: 5px;
        }
        .receipt-footer .thank-you {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-voided {
            background: #f8d7da;
            color: #721c24;
        }
        .status-refunded {
            background: #fff3cd;
            color: #856404;
        }
        .print-btn {
            display: block;
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #0056b3;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                max-width: 80mm;
                padding: 5mm;
            }
            .print-btn, .no-print {
                display: none !important;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Receipt
    </button>

    <div class="receipt">
        <!-- Header -->
        <div class="receipt-header">
            <h1>{{ $company['name'] ?? 'Arena Matriks Edu Group' }}</h1>
            <p>{{ $company['address'] ?? '' }}</p>
            <p>Tel: {{ $company['phone'] ?? '' }}</p>
            @if($transaction->status != 'completed')
                <span class="status-badge status-{{ $transaction->status }}">
                    {{ strtoupper($transaction->status) }}
                </span>
            @endif
        </div>

        <!-- Transaction Info -->
        <div class="receipt-info">
            <table>
                <tr>
                    <td class="label">Receipt #:</td>
                    <td style="text-align: right;"><strong>{{ $transaction->transaction_number }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td style="text-align: right;">{{ $transaction->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Time:</td>
                    <td style="text-align: right;">{{ $transaction->created_at->format('h:i:s A') }}</td>
                </tr>
                <tr>
                    <td class="label">Cashier:</td>
                    <td style="text-align: right;">{{ $transaction->cashier->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Items -->
        <div class="receipt-items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                        <tr>
                            <td>
                                <span class="item-name">{{ $item->inventory->name ?? 'Unknown' }}</span>
                                <div class="item-details">@ RM {{ number_format($item->unit_price, 2) }}</div>
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="receipt-totals">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td>RM {{ number_format($transaction->subtotal, 2) }}</td>
                </tr>
                @if($transaction->discount_amount > 0)
                    <tr>
                        <td>Discount</td>
                        <td>- RM {{ number_format($transaction->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td><strong>TOTAL</strong></td>
                    <td><strong>RM {{ number_format($transaction->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Payment Info -->
        <div class="receipt-payment">
            <table>
                <tr>
                    <td>Payment Method:</td>
                    <td><strong>{{ strtoupper($transaction->payment_method) }}</strong></td>
                </tr>
                @if($transaction->payment_method == 'cash')
                    <tr>
                        <td>Amount Received:</td>
                        <td>RM {{ number_format($transaction->amount_received, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Change:</td>
                        <td>RM {{ number_format($transaction->change_amount, 2) }}</td>
                    </tr>
                @elseif($transaction->qr_reference)
                    <tr>
                        <td>Reference:</td>
                        <td>{{ $transaction->qr_reference }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p class="thank-you">Thank You!</p>
            <p>Please keep this receipt for your records.</p>
            <p>Goods sold are not refundable.</p>
            <p style="margin-top: 10px; font-size: 10px; color: #999;">
                {{ now()->format('d/m/Y h:i A') }}
            </p>
        </div>
    </div>

    <a href="{{ route('staff.pos.index') }}" class="print-btn no-print" style="background: #28a745;">
        Back to POS
    </a>
</body>
</html>
