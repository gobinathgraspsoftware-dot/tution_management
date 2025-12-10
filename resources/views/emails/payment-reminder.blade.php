{{--
    Payment Reminder Email Template
    
    Variables available:
    - $student: Student model
    - $invoice: Invoice model
    - $reminder: PaymentReminder model
    - $installment: Installment model (if applicable)
    - $daysOverdue: Number of days overdue
    - $paymentUrl: URL to make payment online
    - $type: 'initial', 'followup', 'final', 'installment'
--}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder - {{ config('app.name') }}</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
        }
        
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        
        .email-header.urgent {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .email-header.warning {
            background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);
        }
        
        .email-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .email-header .subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Alert Banner */
        .alert-banner {
            padding: 15px 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .alert-banner.overdue {
            background: #f8d7da;
            color: #721c24;
            border-bottom: 2px solid #f5c6cb;
        }
        
        .alert-banner.due-soon {
            background: #fff3cd;
            color: #856404;
            border-bottom: 2px solid #ffeeba;
        }
        
        /* Content */
        .email-content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .message-text {
            font-size: 14px;
            color: #555;
            margin-bottom: 25px;
        }
        
        /* Invoice Details Box */
        .invoice-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .invoice-box h3 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .invoice-details {
            width: 100%;
        }
        
        .invoice-details tr td {
            padding: 8px 0;
            font-size: 14px;
        }
        
        .invoice-details tr td:first-child {
            color: #666;
            width: 40%;
        }
        
        .invoice-details tr td:last-child {
            font-weight: 600;
            text-align: right;
        }
        
        .amount-due {
            font-size: 20px;
            color: #e74c3c;
            font-weight: 700;
        }
        
        .overdue-days {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Installment Info */
        .installment-info {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .installment-info h4 {
            font-size: 14px;
            color: #0c5460;
            margin-bottom: 10px;
        }
        
        .installment-progress {
            background: #dee2e6;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .installment-progress-bar {
            background: #28a745;
            height: 100%;
            border-radius: 4px;
        }
        
        /* CTA Button */
        .cta-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1f6f9f 100%);
            transform: translateY(-2px);
        }
        
        .cta-button.urgent {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        /* Payment Methods */
        .payment-methods {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .payment-methods h4 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .payment-method-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .payment-method-item:last-child {
            border-bottom: none;
        }
        
        .payment-method-icon {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .payment-method-details {
            flex: 1;
        }
        
        .payment-method-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .payment-method-info {
            font-size: 12px;
            color: #666;
        }
        
        /* Bank Details */
        .bank-details {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .bank-details h5 {
            font-size: 13px;
            color: #856404;
            margin-bottom: 10px;
        }
        
        .bank-details p {
            font-size: 13px;
            color: #856404;
            margin: 5px 0;
        }
        
        /* Contact Info */
        .contact-info {
            background: #e8f4fd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .contact-info h4 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .contact-info p {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }
        
        .contact-info a {
            color: #3498db;
            text-decoration: none;
        }
        
        /* Footer */
        .email-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .email-footer p {
            font-size: 12px;
            color: #666;
            margin: 5px 0;
        }
        
        .email-footer .company-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .social-links {
            margin-top: 15px;
        }
        
        .social-links a {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #e9ecef;
            border-radius: 50%;
            margin: 0 5px;
            text-decoration: none;
            line-height: 35px;
            color: #666;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 0;
            }
            
            .email-header, .email-content, .email-footer {
                padding: 20px;
            }
            
            .cta-button {
                display: block;
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="email-header {{ ($daysOverdue ?? 0) > 30 ? 'urgent' : (($daysOverdue ?? 0) > 0 ? 'warning' : '') }}">
            <h1>{{ config('app.name', 'Arena Matriks Edu Group') }}</h1>
            <div class="subtitle">Payment Reminder</div>
        </div>
        
        {{-- Alert Banner --}}
        @if(($daysOverdue ?? 0) > 0)
            <div class="alert-banner overdue">
                ⚠️ Your payment is {{ $daysOverdue }} day(s) overdue
            </div>
        @elseif(isset($invoice) && $invoice->due_date && \Carbon\Carbon::parse($invoice->due_date)->isFuture() && \Carbon\Carbon::parse($invoice->due_date)->diffInDays(now()) <= 7)
            <div class="alert-banner due-soon">
                📅 Payment due in {{ \Carbon\Carbon::parse($invoice->due_date)->diffInDays(now()) }} day(s)
            </div>
        @endif
        
        {{-- Content --}}
        <div class="email-content">
            {{-- Greeting --}}
            <p class="greeting">
                Dear <strong>{{ $student->user->name ?? 'Valued Student' }}</strong>,
            </p>
            
            {{-- Message based on type --}}
            @php
                $type = $type ?? 'initial';
            @endphp
            
            @if($type === 'initial')
                <p class="message-text">
                    This is a friendly reminder that your tuition fee payment is due. 
                    We kindly request you to make the payment at your earliest convenience 
                    to ensure uninterrupted access to classes and learning materials.
                </p>
            @elseif($type === 'followup')
                <p class="message-text">
                    We noticed that your payment is still outstanding. We understand that 
                    circumstances can sometimes make it difficult to pay on time. If you're 
                    facing any difficulties, please don't hesitate to contact us to discuss 
                    payment arrangements.
                </p>
            @elseif($type === 'final')
                <p class="message-text">
                    <strong>FINAL REMINDER:</strong> Despite our previous reminders, your payment 
                    remains overdue. Please settle your outstanding amount immediately to avoid 
                    any disruption to your enrollment. If you've already made this payment, 
                    please disregard this notice.
                </p>
            @elseif($type === 'installment')
                <p class="message-text">
                    This is a reminder for your upcoming installment payment. Please ensure 
                    timely payment to maintain your installment plan and avoid any late fees.
                </p>
            @endif
            
            {{-- Invoice Details --}}
            <div class="invoice-box">
                <h3>📋 Invoice Details</h3>
                <table class="invoice-details">
                    <tr>
                        <td>Invoice Number:</td>
                        <td>{{ $invoice->invoice_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Student ID:</td>
                        <td>{{ $student->student_code ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Description:</td>
                        <td>{{ $invoice->description ?? ($invoice->enrollment->package->name ?? 'Tuition Fee') }}</td>
                    </tr>
                    <tr>
                        <td>Due Date:</td>
                        <td>
                            {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d F Y') : 'N/A' }}
                            @if(($daysOverdue ?? 0) > 0)
                                <span class="overdue-days">{{ $daysOverdue }} days overdue</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td>RM {{ number_format($invoice->total_amount ?? 0, 2) }}</td>
                    </tr>
                    @if(($invoice->paid_amount ?? 0) > 0)
                    <tr>
                        <td>Paid Amount:</td>
                        <td style="color: #28a745;">RM {{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="border-top: 2px solid #e9ecef;">
                        <td><strong>Amount Due:</strong></td>
                        <td class="amount-due">RM {{ number_format($invoice->balance ?? $invoice->total_amount ?? 0, 2) }}</td>
                    </tr>
                </table>
                
                {{-- Installment Info --}}
                @if(isset($installment))
                    <div class="installment-info">
                        <h4>💳 Installment Information</h4>
                        <p style="font-size: 13px; color: #0c5460;">
                            <strong>Installment #{{ $installment->installment_number }}</strong> of 
                            {{ $invoice->installments->count() ?? '?' }}
                        </p>
                        <p style="font-size: 13px; color: #0c5460;">
                            Amount: <strong>RM {{ number_format($installment->amount, 2) }}</strong>
                        </p>
                        @php
                            $paidCount = $invoice->installments->where('status', 'paid')->count() ?? 0;
                            $totalCount = $invoice->installments->count() ?? 1;
                            $progressPercent = ($paidCount / $totalCount) * 100;
                        @endphp
                        <div class="installment-progress">
                            <div class="installment-progress-bar" style="width: {{ $progressPercent }}%;"></div>
                        </div>
                        <p style="font-size: 12px; color: #666; text-align: center;">
                            {{ $paidCount }} of {{ $totalCount }} installments paid
                        </p>
                    </div>
                @endif
            </div>
            
            {{-- CTA Button --}}
            @if(isset($paymentUrl) && $paymentUrl)
                <div class="cta-container">
                    <a href="{{ $paymentUrl }}" class="cta-button {{ ($daysOverdue ?? 0) > 30 ? 'urgent' : '' }}">
                        💳 Pay Now Online
                    </a>
                </div>
            @endif
            
            {{-- Payment Methods --}}
            <div class="payment-methods">
                <h4>💰 Payment Methods Available</h4>
                
                <div class="payment-method-item">
                    <div class="payment-method-icon">🌐</div>
                    <div class="payment-method-details">
                        <div class="payment-method-name">Online Payment</div>
                        <div class="payment-method-info">Pay securely via FPX, credit card, or e-wallet</div>
                    </div>
                </div>
                
                <div class="payment-method-item">
                    <div class="payment-method-icon">🏦</div>
                    <div class="payment-method-details">
                        <div class="payment-method-name">Bank Transfer</div>
                        <div class="payment-method-info">Transfer to our company bank account</div>
                    </div>
                </div>
                
                <div class="payment-method-item">
                    <div class="payment-method-icon">🏢</div>
                    <div class="payment-method-details">
                        <div class="payment-method-name">Walk-in Payment</div>
                        <div class="payment-method-info">Pay at our centre (cash or card accepted)</div>
                    </div>
                </div>
                
                {{-- Bank Details --}}
                <div class="bank-details">
                    <h5>🏦 Bank Transfer Details</h5>
                    <p><strong>Bank:</strong> {{ config('payment.bank_name', 'Maybank') }}</p>
                    <p><strong>Account Name:</strong> {{ config('payment.account_name', 'Arena Matriks Edu Group') }}</p>
                    <p><strong>Account Number:</strong> {{ config('payment.account_number', '1234567890') }}</p>
                    <p style="margin-top: 10px; font-style: italic;">
                        * Please include your invoice number as payment reference
                    </p>
                </div>
            </div>
            
            {{-- Contact Information --}}
            <div class="contact-info">
                <h4>📞 Need Help?</h4>
                <p>If you have any questions or need assistance, please contact us:</p>
                <p>
                    📱 <a href="tel:{{ config('contact.phone', '+60123456789') }}">{{ config('contact.phone', '+60 12-345 6789') }}</a>
                </p>
                <p>
                    📧 <a href="mailto:{{ config('contact.email', 'info@arenamatriks.com') }}">{{ config('contact.email', 'info@arenamatriks.com') }}</a>
                </p>
                <p>
                    💬 <a href="{{ config('contact.whatsapp_url', '#') }}">WhatsApp Us</a>
                </p>
            </div>
            
            {{-- Closing --}}
            <p class="message-text">
                Thank you for your prompt attention to this matter. We appreciate your 
                continued trust in {{ config('app.name', 'Arena Matriks Edu Group') }}.
            </p>
            
            <p style="margin-top: 20px; font-size: 14px;">
                Best regards,<br>
                <strong>{{ config('app.name', 'Arena Matriks Edu Group') }}</strong><br>
                <em>Finance Department</em>
            </p>
        </div>
        
        {{-- Footer --}}
        <div class="email-footer">
            <p class="company-name">{{ config('app.name', 'Arena Matriks Edu Group') }}</p>
            <p>{{ config('contact.address', 'Your Centre Address Here') }}</p>
            <p style="margin-top: 10px; font-size: 11px; color: #999;">
                This is an automated reminder. If you have already made this payment, 
                please disregard this email. It may take 1-2 business days for payments 
                to be reflected in our system.
            </p>
            <p style="margin-top: 10px; font-size: 11px; color: #999;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
