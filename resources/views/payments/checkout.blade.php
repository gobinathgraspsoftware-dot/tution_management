<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #818cf8;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            padding: 40px 0;
        }

        .checkout-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .checkout-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .checkout-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .checkout-header .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .checkout-header .invoice-info {
            display: inline-block;
            background: rgba(255,255,255,0.1);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
        }

        .checkout-body {
            padding: 30px;
        }

        .invoice-summary {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .invoice-summary h5 {
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-top: 15px;
            margin-top: 10px;
            border-top: 2px solid #e2e8f0;
        }

        .summary-row.total {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .student-info {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e2e8f0;
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--bg-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
            margin-right: 20px;
        }

        .student-details h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .student-details p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .gateway-options {
            margin-bottom: 25px;
        }

        .gateway-option {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .gateway-option:hover {
            border-color: var(--primary-light);
            background: #f8fafc;
        }

        .gateway-option.selected {
            border-color: var(--primary-color);
            background: rgba(79, 70, 229, 0.05);
        }

        .gateway-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .gateway-option .gateway-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gateway-option .gateway-name {
            display: flex;
            align-items: center;
        }

        .gateway-option .gateway-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .gateway-option .gateway-fee {
            text-align: right;
        }

        .gateway-option .gateway-fee .fee-amount {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .gateway-option .gateway-fee .fee-label {
            font-size: 12px;
            color: #64748b;
        }

        .gateway-option .check-mark {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .gateway-option.selected .check-mark {
            display: flex;
        }

        .toyyibpay-icon { background: #e8eaf6; color: #1a237e; }
        .senangpay-icon { background: #e0f7fa; color: #00838f; }
        .billplz-icon { background: #fff3e0; color: #e65100; }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section h6 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .form-section h6 i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .terms-check {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .terms-check input {
            margin-top: 4px;
            margin-right: 12px;
        }

        .terms-check label {
            font-size: 14px;
            color: #64748b;
        }

        .terms-check a {
            color: var(--primary-color);
        }

        .btn-pay {
            background: var(--bg-gradient);
            border: none;
            color: #fff;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
            color: #fff;
        }

        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .secure-badge {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .secure-badge i {
            color: var(--success-color);
            margin-right: 8px;
        }

        .secure-badge span {
            color: #64748b;
            font-size: 14px;
        }

        .amount-breakdown {
            background: #fef3c7;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }

        .amount-breakdown h6 {
            color: #92400e;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .amount-breakdown .breakdown-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #78350f;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .checkout-body {
                padding: 20px;
            }

            .gateway-option .gateway-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .gateway-option .gateway-fee {
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-card">
            <div class="checkout-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap me-2"></i>
                    {{ config('app.name', 'Arena Matriks Edu') }}
                </div>
                <div class="invoice-info">
                    <i class="fas fa-file-invoice me-1"></i>
                    Invoice #{{ $invoice->invoice_number }}
                </div>
            </div>

            <div class="checkout-body">
                <!-- Student Info -->
                <div class="student-info">
                    <div class="student-avatar">
                        {{ strtoupper(substr($invoice->student->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="student-details">
                        <h4>{{ $invoice->student->user->name ?? 'Student' }}</h4>
                        <p>
                            <i class="fas fa-book me-1"></i>
                            {{ $invoice->enrollment->package->name ?? 'Package' }}
                        </p>
                    </div>
                </div>

                <!-- Invoice Summary -->
                <div class="invoice-summary">
                    <h5><i class="fas fa-receipt me-2"></i>Invoice Summary</h5>
                    <div class="summary-row">
                        <span>{{ $invoice->type_label }}</span>
                        <span>RM {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->online_fee > 0)
                    <div class="summary-row">
                        <span>Online Payment Fee</span>
                        <span>RM {{ number_format($invoice->online_fee, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->discount > 0)
                    <div class="summary-row text-success">
                        <span>Discount</span>
                        <span>- RM {{ number_format($invoice->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->paid_amount > 0)
                    <div class="summary-row text-info">
                        <span>Already Paid</span>
                        <span>- RM {{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="summary-row total">
                        <span>Amount to Pay</span>
                        <span>RM {{ number_format($invoice->balance, 2) }}</span>
                    </div>
                </div>

                <!-- Payment Form -->
                <form action="{{ route('payment.process', $invoice) }}" method="POST" id="payment-form">
                    @csrf

                    <!-- Gateway Selection -->
                    <div class="form-section">
                        <h6><i class="fas fa-credit-card"></i> Select Payment Method</h6>

                        @foreach($gateways as $gateway)
                            <label class="gateway-option {{ $gateway['value'] === $defaultGateway ? 'selected' : '' }}">
                                <input type="radio" name="gateway" value="{{ $gateway['value'] }}"
                                       {{ $gateway['value'] === $defaultGateway ? 'checked' : '' }} required>
                                <div class="gateway-content">
                                    <div class="gateway-name">
                                        <div class="gateway-icon {{ $gateway['value'] }}-icon">
                                            @switch($gateway['value'])
                                                @case('toyyibpay')
                                                    <i class="fas fa-credit-card"></i>
                                                    @break
                                                @case('senangpay')
                                                    <i class="fas fa-wallet"></i>
                                                    @break
                                                @case('billplz')
                                                    <i class="fas fa-money-check"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-credit-card"></i>
                                            @endswitch
                                        </div>
                                        <div>
                                            <strong>{{ $gateway['label'] }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                @switch($gateway['value'])
                                                    @case('toyyibpay')
                                                        FPX, Credit/Debit Card
                                                        @break
                                                    @case('senangpay')
                                                        FPX Online Banking
                                                        @break
                                                    @case('billplz')
                                                        FPX, E-Wallet
                                                        @break
                                                @endswitch
                                            </small>
                                        </div>
                                    </div>
                                    @if(isset($gatewayFees[$gateway['value']]))
                                        <div class="gateway-fee">
                                            <div class="fee-amount">
                                                RM {{ number_format($gatewayFees[$gateway['value']]['total'], 2) }}
                                            </div>
                                            <div class="fee-label">
                                                incl. RM {{ number_format($gatewayFees[$gateway['value']]['fee'], 2) }} fee
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="check-mark">
                                    <i class="fas fa-check fa-sm"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Customer Info -->
                    <div class="form-section">
                        <h6><i class="fas fa-user"></i> Payer Information</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       name="name" value="{{ old('name', $invoice->student->parent->user->name ?? $invoice->student->user->name ?? '') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email', $invoice->student->parent->user->email ?? $invoice->student->user->email ?? '') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone', $invoice->student->parent->user->phone ?? $invoice->student->user->phone ?? '') }}"
                                       placeholder="e.g., 0123456789" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="terms-check">
                        <input type="checkbox" id="agree_terms" name="agree_terms" required
                               class="form-check-input @error('agree_terms') is-invalid @enderror">
                        <label for="agree_terms">
                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms & Conditions</a>
                            and understand that this payment is non-refundable once processed.
                        </label>
                    </div>
                    @error('agree_terms')
                        <div class="text-danger mb-3 small">{{ $message }}</div>
                    @enderror

                    <!-- Pay Button -->
                    <button type="submit" class="btn btn-pay" id="pay-btn">
                        <i class="fas fa-lock me-2"></i>
                        Pay RM <span id="total-amount">{{ number_format($invoice->balance, 2) }}</span>
                    </button>

                    <!-- Secure Badge -->
                    <div class="secure-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure payment powered by trusted payment gateways</span>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-4">
            <a href="{{ url()->previous() }}" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i> Back to previous page
            </a>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms & Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Payment Terms</h6>
                    <ul>
                        <li>All payments are processed securely through our authorized payment gateways.</li>
                        <li>Payment confirmation will be sent to the provided email address.</li>
                        <li>Please ensure all payment details are correct before proceeding.</li>
                        <li>Transaction fees may apply depending on the selected payment method.</li>
                    </ul>

                    <h6 class="mt-4">Refund Policy</h6>
                    <ul>
                        <li>Payments made are generally non-refundable once processed successfully.</li>
                        <li>Refund requests must be submitted in writing within 7 days of payment.</li>
                        <li>Refunds, if approved, may take 7-14 business days to process.</li>
                        <li>Gateway transaction fees are non-refundable.</li>
                    </ul>

                    <h6 class="mt-4">Privacy</h6>
                    <p>Your payment information is encrypted and processed securely. We do not store your full card details on our servers.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            // Gateway selection
            $('.gateway-option').on('click', function() {
                $('.gateway-option').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);

                // Update total amount based on gateway fee
                var gateway = $(this).find('input[type="radio"]').val();
                updateTotalAmount(gateway);
            });

            // Update total amount display
            function updateTotalAmount(gateway) {
                var fees = @json($gatewayFees);
                if (fees[gateway]) {
                    $('#total-amount').text(parseFloat(fees[gateway].total).toFixed(2));
                }
            }

            // Form submission
            $('#payment-form').on('submit', function(e) {
                var $btn = $('#pay-btn');
                $btn.prop('disabled', true);
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
            });

            // Initialize with default gateway
            var defaultGateway = $('input[name="gateway"]:checked').val();
            if (defaultGateway) {
                updateTotalAmount(defaultGateway);
            }
        });
    </script>
</body>
</html>
