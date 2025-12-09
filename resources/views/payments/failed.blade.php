<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .failed-container {
            max-width: 550px;
            width: 100%;
        }

        .failed-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        .failed-header {
            padding: 50px 30px 30px;
        }

        .failed-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: shake 0.5s ease-out;
        }

        .failed-icon i {
            font-size: 50px;
            color: #fff;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .failed-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 10px;
        }

        .failed-header p {
            color: #6b7280;
            margin: 0;
            font-size: 16px;
        }

        .failed-body {
            padding: 0 30px 30px;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }

        .error-message h6 {
            color: #991b1b;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .error-message h6 i {
            margin-right: 10px;
        }

        .error-message p {
            color: #b91c1c;
            font-size: 14px;
            margin: 0;
        }

        .transaction-details {
            background: #f8fafc;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row .label {
            color: #6b7280;
            font-size: 14px;
        }

        .detail-row .value {
            color: #111827;
            font-weight: 500;
            font-size: 14px;
        }

        .help-section {
            background: #fff7ed;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }

        .help-section h6 {
            color: #9a3412;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .help-section ul {
            color: #c2410c;
            font-size: 14px;
            margin: 0;
            padding-left: 20px;
        }

        .help-section li {
            margin-bottom: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-direction: column;
        }

        .btn-retry {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: #fff;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
            color: #fff;
        }

        .btn-secondary-custom {
            background: #f3f4f6;
            border: none;
            color: #374151;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        .failed-footer {
            padding: 20px 30px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .failed-footer p {
            margin: 0;
            font-size: 13px;
            color: #9ca3af;
        }

        .failed-footer a {
            color: #dc2626;
        }

        .support-contact {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 10px;
        }

        .support-contact a {
            color: #6b7280;
            font-size: 13px;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .support-contact a:hover {
            color: #dc2626;
        }

        .support-contact a i {
            margin-right: 5px;
        }

        @media (max-width: 576px) {
            .failed-header h1 {
                font-size: 24px;
            }

            .support-contact {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-card">
            <div class="failed-header">
                <div class="failed-icon">
                    <i class="fas fa-times"></i>
                </div>
                <h1>Payment Failed</h1>
                <p>We couldn't process your payment</p>
            </div>

            <div class="failed-body">
                @if($error)
                    <div class="error-message">
                        <h6><i class="fas fa-exclamation-triangle"></i> Error Details</h6>
                        <p>{{ $error }}</p>
                    </div>
                @endif

                @if($transaction)
                    <div class="transaction-details">
                        <div class="detail-row">
                            <span class="label">Transaction ID</span>
                            <span class="value">{{ $transaction->transaction_id }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Invoice Number</span>
                            <span class="value">{{ $transaction->invoice->invoice_number ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Amount</span>
                            <span class="value">RM {{ number_format($transaction->amount, 2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Payment Gateway</span>
                            <span class="value">{{ ucfirst($transaction->gatewayConfig->gateway_name ?? 'N/A') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Status</span>
                            <span class="value">
                                <span class="badge bg-danger">{{ ucfirst($transaction->status) }}</span>
                            </span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Attempted At</span>
                            <span class="value">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                @endif

                <div class="help-section">
                    <h6><i class="fas fa-lightbulb me-2"></i>What you can try:</h6>
                    <ul>
                        <li>Check if your card/bank account has sufficient funds</li>
                        <li>Verify your payment details are entered correctly</li>
                        <li>Try using a different payment method</li>
                        <li>Contact your bank if the issue persists</li>
                        <li>Wait a few minutes and try again</li>
                    </ul>
                </div>

                <div class="action-buttons">
                    @if($transaction && $transaction->invoice)
                        <a href="{{ route('payment.retry', $transaction) }}" class="btn btn-retry">
                            <i class="fas fa-redo me-2"></i> Try Again
                        </a>
                    @endif

                    @auth
                        @if(auth()->user()->hasRole(['parent']))
                            <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary-custom">
                                <i class="fas fa-home me-2"></i> Return to Dashboard
                            </a>
                        @elseif(auth()->user()->hasRole(['student']))
                            <a href="{{ route('student.dashboard') }}" class="btn btn-secondary-custom">
                                <i class="fas fa-home me-2"></i> Return to Dashboard
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary-custom">
                                <i class="fas fa-home me-2"></i> Return to Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary-custom">
                            <i class="fas fa-sign-in-alt me-2"></i> Login to View Invoice
                        </a>
                    @endauth
                </div>
            </div>

            <div class="failed-footer">
                <p>Need immediate assistance? Contact our support team.</p>
                <div class="support-contact">
                    <a href="mailto:support@arenamatriks.edu.my">
                        <i class="fas fa-envelope"></i> support@arenamatriks.edu.my
                    </a>
                    <a href="tel:+60123456789">
                        <i class="fas fa-phone"></i> +60 12-345 6789
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
