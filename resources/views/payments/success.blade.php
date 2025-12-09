<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - {{ config('app.name') }}</title>

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
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .success-container {
            max-width: 550px;
            width: 100%;
        }

        .success-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        .success-header {
            padding: 50px 30px 30px;
            position: relative;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon i {
            font-size: 50px;
            color: #fff;
            animation: checkmark 0.5s ease-out 0.3s both;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes checkmark {
            0% {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }
            100% {
                transform: scale(1) rotate(0);
                opacity: 1;
            }
        }

        .success-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #059669;
            margin-bottom: 10px;
        }

        .success-header p {
            color: #6b7280;
            margin: 0;
            font-size: 16px;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: confetti 3s ease-out forwards;
        }

        @keyframes confetti {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(300px) rotate(720deg);
                opacity: 0;
            }
        }

        .success-body {
            padding: 0 30px 30px;
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

        .detail-row.amount .value {
            font-size: 20px;
            font-weight: 700;
            color: #059669;
        }

        .receipt-note {
            background: #ecfdf5;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
        }

        .receipt-note i {
            color: #059669;
            margin-right: 12px;
            margin-top: 3px;
        }

        .receipt-note p {
            margin: 0;
            color: #065f46;
            font-size: 14px;
            text-align: left;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-direction: column;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
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

        .success-footer {
            padding: 20px 30px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .success-footer p {
            margin: 0;
            font-size: 13px;
            color: #9ca3af;
        }

        .success-footer a {
            color: #059669;
        }

        @media (max-width: 576px) {
            .success-header h1 {
                font-size: 24px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-header" id="confetti-container">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Payment Successful!</h1>
                <p>Your payment has been processed successfully</p>
            </div>

            <div class="success-body">
                <div class="transaction-details">
                    <div class="detail-row amount">
                        <span class="label">Amount Paid</span>
                        <span class="value">RM {{ number_format($transaction->amount ?? 0, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Transaction ID</span>
                        <span class="value">{{ $transaction->transaction_id ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Invoice Number</span>
                        <span class="value">{{ $transaction->invoice->invoice_number ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Student Name</span>
                        <span class="value">{{ $transaction->invoice->student->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Package</span>
                        <span class="value">{{ $transaction->invoice->enrollment->package->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Payment Method</span>
                        <span class="value">
                            <span class="badge bg-success">
                                {{ ucfirst($transaction->gatewayConfig->gateway_name ?? 'Online') }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date & Time</span>
                        <span class="value">{{ $transaction->updated_at ? $transaction->updated_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>

                <div class="receipt-note">
                    <i class="fas fa-envelope"></i>
                    <p>
                        A payment confirmation has been sent to <strong>{{ $transaction->customer_email ?? 'your email' }}</strong>.
                        Please check your inbox for the receipt.
                    </p>
                </div>

                <div class="action-buttons">
                    @if($transaction->payment)
                        <a href="{{ route('payments.receipt', $transaction->payment) }}" class="btn btn-primary-custom" target="_blank">
                            <i class="fas fa-download me-2"></i> Download Receipt
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
                            <i class="fas fa-sign-in-alt me-2"></i> Login to View Details
                        </a>
                    @endauth
                </div>
            </div>

            <div class="success-footer">
                <p>
                    Need help? Contact us at <a href="mailto:support@arenamatriks.edu.my">support@arenamatriks.edu.my</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Create confetti effect
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('confetti-container');
            const colors = ['#10b981', '#34d399', '#6ee7b7', '#fbbf24', '#f472b6', '#818cf8'];

            for (let i = 0; i < 30; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.top = '-10px';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 0.5 + 's';
                    confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                    container.appendChild(confetti);

                    setTimeout(() => confetti.remove(), 5000);
                }, i * 100);
            }
        });
    </script>
</body>
</html>
