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
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            background: #f5f5f5;
            padding: 20px;
        }
        .receipt {
            width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #000;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 11px;
            color: #444;
        }
        .transaction-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .transaction-info table {
            width: 100%;
        }
        .transaction-info td {
            padding: 2px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed { background: #d4edda; color: #155724; }
        .status-voided { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #fff3cd; color: #856404; }
        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            padding: 5px 0;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }
        .item-row {
            margin-bottom: 8px;
        }
        .item-name {
            font-weight: bold;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            color: #444;
            font-size: 11px;
        }
        .totals {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .payment-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #000;
            text-align: center;
            font-size: 11px;
        }
        .thank-you {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-primary {
            background: #FDA530;
            color: #fff;
        }
        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
                width: 80mm;
                padding: 5mm;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            @if($company['address'])
            <div class="company-info">{{ $company['address'] }}</div>
            @endif
            @if($company['phone'])
            <div class="company-info">Tel: {{ $company['phone'] }}</div>
            @endif
        </div>

        <div class="transaction-info">
            <table>
                <tr>
                    <td>Receipt #:</td>
                    <td style="text-align: right;">{{ $transaction->transaction_number }}</td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td style="text-align: right;">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Time:</td>
                    <td style="text-align: right;">{{ $transaction->transaction_date->format('h:i A') }}</td>
                </tr>
                <tr>
                    <td>Cashier:</td>
                    <td style="text-align: right;">{{ $transaction->cashier->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td style="text-align: right;">
                        <span class="status-badge status-{{ $transaction->status }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="items">
            <div class="items-header">
                <span>Item</span>
                <span>Amount</span>
            </div>
            @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->inventory->name ?? 'Deleted Item' }}</div>
                <div class="item-details">
                    <span>{{ $item->quantity }} x RM {{ number_format($item->unit_price, 2) }}</span>
                    <span>RM {{ number_format($item->total_price, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>RM {{ number_format($transaction->subtotal, 2) }}</span>
            </div>
            @if($transaction->discount > 0)
            <div class="total-row">
                <span>Discount</span>
                <span>- RM {{ number_format($transaction->discount, 2) }}</span>
            </div>
            @endif
            @if($transaction->tax > 0)
            <div class="total-row">
                <span>Tax</span>
                <span>RM {{ number_format($transaction->tax, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span>RM {{ number_format($transaction->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="payment-info">
            <div class="total-row">
                <span>Payment Method</span>
                <span>{{ $transaction->payment_method == 'cash' ? 'Cash' : 'QR Pay' }}</span>
            </div>
            @if($transaction->payment_method == 'cash')
            <div class="total-row">
                <span>Received</span>
                <span>RM {{ number_format($transaction->amount_received, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Change</span>
                <span>RM {{ number_format($transaction->change_amount, 2) }}</span>
            </div>
            @else
            <div class="total-row">
                <span>Reference #</span>
                <span>{{ $transaction->reference_number }}</span>
            </div>
            @endif
        </div>

        <div class="footer">
            <div class="thank-you">Thank You!</div>
            <div>Please come again</div>
            <div style="margin-top: 10px; font-size: 10px; color: #888;">
                Printed: {{ $generated_at->format('d/m/Y h:i A') }}
            </div>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-primary" onclick="window.print()">
            Print Receipt
        </button>
        <a href="{{ route('admin.pos.transactions') }}" class="btn btn-secondary">
            Back to Transactions
        </a>
    </div>

    @if(isset($autoPrint) && $autoPrint)
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>
