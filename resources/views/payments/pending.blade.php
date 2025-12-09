<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Pending - {{ config('app.name') }}</title>

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
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 50%, #fbbf24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .pending-container {
            max-width: 550px;
            width: 100%;
        }

        .pending-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            text-align: center;
        }

        .pending-header {
            padding: 50px 30px 30px;
        }

        .pending-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            position: relative;
        }

        .pending-icon i {
            font-size: 50px;
            color: #fff;
        }

        .pending-icon::after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border: 3px solid #f59e0b;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pending-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #d97706;
            margin-bottom: 10px;
        }

        .pending-header p {
            color: #6b7280;
            margin: 0;
            font-size: 16px;
        }

        .pending-body {
            padding: 0 30px 30px;
        }

        .status-indicator {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .status-indicator .status-text {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #92400e;
            font-weight: 500;
        }

        .status-indicator .status-text i {
            margin-right: 10px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .status-indicator .status-note {
            margin-top: 10px;
            font-size: 13px;
            color: #a16207;
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
            font-size: 18px;
            font-weight: 700;
            color: #d97706;
        }

        .info-section {
            background: #fef3c7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }

        .info-section h6 {
            color: #92400e;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-section h6 i {
            margin-right: 10px;
        }

        .info-section p {
            color: #a16207;
            font-size: 14px;
            margin: 0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-direction: column;
        }

        .btn-check-status {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

        .btn-check-status:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
            color: #fff;
        }

        .btn-check-status:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
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

        .btn-cancel {
            background: transparent;
            border: 2px solid #e5e7eb;
            color: #6b7280;
            padding: 14px 28px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            border-color: #dc2626;
            color: #dc2626;
        }

        .pending-footer {
            padding: 20px 30px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .pending-footer p {
            margin: 0;
            font-size: 13px;
            color: #9ca3af;
        }

        .pending-footer a {
            color: #d97706;
        }

        .auto-refresh-note {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auto-refresh-note i {
            margin-right: 5px;
        }

        .countdown {
            font-weight: 600;
            color: #d97706;
        }

        @media (max-width: 576px) {
            .pending-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="pending-card">
            <div class="pending-header">
                <div class="pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h1>Payment Pending</h1>
                <p>Your payment is being processed</p>
            </div>

            <div class="pending-body">
                <div class="status-indicator">
                    <div class="status-text">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span id="status-message">Waiting for payment confirmation...</span>
                    </div>
                    <p class="status-note">
                        This may take a few moments. Please do not close this page.
                    </p>
                </div>

                <div class="transaction-details">
                    <div class="detail-row amount">
                        <span class="label">Amount</span>
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
                        <span class="label">Payment Gateway</span>
                        <span class="value">{{ ucfirst($transaction->gatewayConfig->gateway_name ?? 'N/A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="badge bg-warning text-dark" id="payment-status">
                                {{ ucfirst($transaction->status ?? 'Pending') }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Initiated At</span>
                        <span class="value">{{ $transaction->created_at ? $transaction->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>

                <div class="info-section">
                    <h6><i class="fas fa-info-circle"></i> What happens next?</h6>
                    <p>
                        We're waiting for confirmation from the payment gateway.
                        Once confirmed, your payment will be automatically recorded and you'll receive a confirmation email.
                        If this takes longer than expected, please contact our support team.
                    </p>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-check-status" id="check-status-btn"
                            data-transaction-id="{{ $transaction->transaction_id ?? '' }}">
                        <i class="fas fa-sync-alt me-2"></i> Check Status
                    </button>

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
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </a>
                    @endauth

                    @if($transaction)
                        <form action="{{ route('payment.cancel', $transaction) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this payment?');">
                            @csrf
                            <button type="submit" class="btn btn-cancel w-100">
                                <i class="fas fa-times me-2"></i> Cancel Payment
                            </button>
                        </form>
                    @endif
                </div>

                <div class="auto-refresh-note">
                    <i class="fas fa-clock"></i>
                    Auto-checking in <span class="countdown" id="countdown">30</span> seconds
                </div>
            </div>

            <div class="pending-footer">
                <p>
                    Need help? Contact us at <a href="mailto:support@arenamatriks.edu.my">support@arenamatriks.edu.my</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            var transactionId = $('#check-status-btn').data('transaction-id');
            var checkInterval = 30; // seconds
            var countdown = checkInterval;
            var countdownTimer;
            var autoCheckEnabled = true;

            // Update countdown display
            function updateCountdown() {
                $('#countdown').text(countdown);
                countdown--;

                if (countdown < 0) {
                    countdown = checkInterval;
                    if (autoCheckEnabled) {
                        checkPaymentStatus(true);
                    }
                }
            }

            // Start countdown timer
            countdownTimer = setInterval(updateCountdown, 1000);

            // Check payment status
            function checkPaymentStatus(refresh) {
                var $btn = $('#check-status-btn');
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Checking...');

                var url = '{{ route("payment.check-status", ":id") }}'.replace(':id', transactionId);

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: { refresh: refresh ? 1 : 0 },
                    success: function(response) {
                        if (response.success) {
                            updateStatusDisplay(response.status);

                            // Redirect based on status
                            if (response.status === 'completed') {
                                autoCheckEnabled = false;
                                clearInterval(countdownTimer);
                                $('#status-message').text('Payment confirmed! Redirecting...');
                                setTimeout(function() {
                                    window.location.href = '{{ route("payment.success") }}?transaction=' + transactionId;
                                }, 2000);
                            } else if (response.status === 'failed' || response.status === 'cancelled') {
                                autoCheckEnabled = false;
                                clearInterval(countdownTimer);
                                $('#status-message').text('Payment ' + response.status + '. Redirecting...');
                                setTimeout(function() {
                                    window.location.href = '{{ route("payment.failed") }}?transaction=' + transactionId;
                                }, 2000);
                            }
                        }
                    },
                    error: function() {
                        console.log('Error checking status');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $btn.html('<i class="fas fa-sync-alt me-2"></i> Check Status');
                        countdown = checkInterval;
                    }
                });
            }

            // Update status display
            function updateStatusDisplay(status) {
                var $badge = $('#payment-status');
                $badge.text(capitalizeFirst(status));

                if (status === 'completed') {
                    $badge.removeClass('bg-warning text-dark').addClass('bg-success');
                } else if (status === 'failed' || status === 'cancelled') {
                    $badge.removeClass('bg-warning text-dark').addClass('bg-danger');
                }
            }

            // Capitalize first letter
            function capitalizeFirst(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            // Manual check button
            $('#check-status-btn').on('click', function() {
                checkPaymentStatus(true);
            });

            // Initial check after 5 seconds
            setTimeout(function() {
                checkPaymentStatus(true);
            }, 5000);
        });
    </script>
</body>
</html>
