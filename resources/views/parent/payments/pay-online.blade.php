@extends('layouts.parent')

@section('title', 'Pay Online')
@section('page-title', 'Online Payment')

@push('styles')
<style>
    .payment-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .payment-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 25px;
    }

    .payment-header h4 {
        margin: 0;
        font-weight: 600;
    }

    .invoice-badge {
        background: rgba(255,255,255,0.2);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        display: inline-block;
        margin-top: 10px;
    }

    .payment-body {
        padding: 25px;
    }

    .child-selector {
        margin-bottom: 25px;
    }

    .child-tab {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .child-tab .nav-link {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px 20px;
        color: #495057;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .child-tab .nav-link:hover {
        border-color: #667eea;
        background: #fff;
    }

    .child-tab .nav-link.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
    }

    .child-tab .nav-link .child-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-weight: 600;
    }

    .child-tab .nav-link.active .child-avatar {
        background: rgba(255,255,255,0.3);
    }

    .amount-display {
        text-align: center;
        padding: 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        margin-bottom: 25px;
    }

    .amount-display .label {
        color: #6c757d;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .amount-display .amount {
        font-size: 42px;
        font-weight: 700;
        color: #495057;
        margin: 10px 0;
    }

    .amount-display .amount .currency {
        font-size: 20px;
        font-weight: 400;
        color: #6c757d;
    }

    .student-info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .student-info-card .student-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 600;
    }

    .invoice-details {
        margin-bottom: 25px;
    }

    .invoice-details .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .invoice-details .detail-item:last-child {
        border-bottom: none;
    }

    .invoice-details .detail-label {
        color: #6c757d;
    }

    .invoice-details .detail-value {
        font-weight: 500;
    }

    .gateway-selection {
        margin-bottom: 25px;
    }

    .gateway-option {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .gateway-option:hover {
        border-color: #667eea;
        background: #f8f9fa;
    }

    .gateway-option.selected {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }

    .gateway-option input {
        position: absolute;
        opacity: 0;
    }

    .gateway-info {
        display: flex;
        align-items: center;
    }

    .gateway-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 24px;
    }

    .gateway-icon.toyyibpay { background: #e8eaf6; color: #1a237e; }
    .gateway-icon.senangpay { background: #e0f7fa; color: #00838f; }
    .gateway-icon.billplz { background: #fff3e0; color: #e65100; }

    .gateway-details h6 {
        margin: 0;
        font-weight: 600;
    }

    .gateway-details small {
        color: #6c757d;
    }

    .gateway-fee {
        text-align: right;
    }

    .gateway-fee .fee-label {
        font-size: 12px;
        color: #6c757d;
    }

    .gateway-fee .fee-amount {
        font-weight: 600;
        color: #667eea;
    }

    .btn-pay-now {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: #fff;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 10px;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-pay-now:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        color: #fff;
    }

    .btn-pay-now:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .secure-note {
        text-align: center;
        margin-top: 20px;
        color: #6c757d;
        font-size: 14px;
    }

    .secure-note i {
        color: #28a745;
        margin-right: 5px;
    }

    .unpaid-invoices .invoice-item {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid #ffc107;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
    }

    .unpaid-invoices .invoice-item:hover {
        border-left-color: #667eea;
        transform: translateX(5px);
    }

    .unpaid-invoices .invoice-item.overdue {
        border-left-color: #dc3545;
    }

    .children-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .child-summary-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        flex: 1;
        min-width: 250px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .child-summary-card .child-name {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .child-summary-card .avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-weight: 600;
    }

    .child-summary-card .summary-stats {
        display: flex;
        justify-content: space-between;
    }

    .child-summary-card .stat {
        text-align: center;
    }

    .child-summary-card .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #495057;
    }

    .child-summary-card .stat-label {
        font-size: 12px;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-credit-card me-2"></i> Online Payment
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('parent.invoices.index') }}">Invoices</a></li>
                <li class="breadcrumb-item active">Pay Online</li>
            </ol>
        </nav>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Children Summary Cards -->
@if(isset($children) && $children->count() > 1)
<div class="children-summary">
    @foreach($children as $child)
        @php
            $pendingCount = $child->invoices()->whereIn('status', ['pending', 'partial', 'overdue'])->count();
            $totalDue = $child->invoices()->whereIn('status', ['pending', 'partial', 'overdue'])
                              ->sum(\DB::raw('total_amount - paid_amount'));
        @endphp
        <div class="child-summary-card">
            <div class="child-name">
                <div class="avatar">{{ strtoupper(substr($child->user->name, 0, 1)) }}</div>
                <div>
                    <h6 class="mb-0">{{ $child->user->name }}</h6>
                    <small class="text-muted">{{ $child->student_id }}</small>
                </div>
            </div>
            <div class="summary-stats">
                <div class="stat">
                    <div class="stat-value text-warning">{{ $pendingCount }}</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat">
                    <div class="stat-value text-danger">RM {{ number_format($totalDue, 2) }}</div>
                    <div class="stat-label">Total Due</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif

<div class="row">
    <!-- Main Payment Section -->
    <div class="col-lg-8">
        @if(isset($invoice))
            <!-- Single Invoice Payment -->
            <div class="payment-card">
                <div class="payment-header">
                    <h4><i class="fas fa-file-invoice me-2"></i> Invoice Payment</h4>
                    <div class="invoice-badge">
                        <i class="fas fa-hashtag me-1"></i> {{ $invoice->invoice_number }}
                    </div>
                </div>
                <div class="payment-body">
                    <!-- Student Info -->
                    <div class="student-info-card">
                        <div class="d-flex align-items-center">
                            <div class="student-avatar me-3">
                                {{ strtoupper(substr($invoice->student->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <h5 class="mb-1">{{ $invoice->student->user->name ?? 'Student' }}</h5>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-book me-1"></i>
                                    {{ $invoice->enrollment->package->name ?? 'Package' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Amount Display -->
                    <div class="amount-display">
                        <div class="label">Amount to Pay</div>
                        <div class="amount">
                            <span class="currency">RM</span> {{ number_format($invoice->balance, 2) }}
                        </div>
                        @if($invoice->isOverdue())
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i> Overdue by {{ $invoice->days_overdue }} days
                            </span>
                        @elseif($invoice->due_date)
                            <span class="badge bg-warning text-dark">
                                Due: {{ $invoice->due_date->format('d M Y') }}
                            </span>
                        @endif
                    </div>

                    <!-- Invoice Details -->
                    <div class="invoice-details">
                        <div class="detail-item">
                            <span class="detail-label">Invoice Type</span>
                            <span class="detail-value">{{ $invoice->type_label }}</span>
                        </div>
                        @if($invoice->billing_period_start && $invoice->billing_period_end)
                        <div class="detail-item">
                            <span class="detail-label">Billing Period</span>
                            <span class="detail-value">{{ $invoice->billing_period }}</span>
                        </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Subtotal</span>
                            <span class="detail-value">RM {{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        @if($invoice->online_fee > 0)
                        <div class="detail-item">
                            <span class="detail-label">Online Fee</span>
                            <span class="detail-value">RM {{ number_format($invoice->online_fee, 2) }}</span>
                        </div>
                        @endif
                        @if($invoice->discount > 0)
                        <div class="detail-item text-success">
                            <span class="detail-label">Discount</span>
                            <span class="detail-value">- RM {{ number_format($invoice->discount, 2) }}</span>
                        </div>
                        @endif
                        @if($invoice->paid_amount > 0)
                        <div class="detail-item text-info">
                            <span class="detail-label">Already Paid</span>
                            <span class="detail-value">- RM {{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Gateway Selection -->
                    @if(!empty($gateways))
                        <h6 class="mb-3"><i class="fas fa-wallet me-2"></i> Select Payment Method</h6>
                        <div class="gateway-selection">
                            @foreach($gateways as $gateway)
                                <label class="gateway-option {{ $gateway['value'] === $defaultGateway ? 'selected' : '' }}">
                                    <input type="radio" name="selected_gateway" value="{{ $gateway['value'] }}"
                                           {{ $gateway['value'] === $defaultGateway ? 'checked' : '' }}>
                                    <div class="gateway-info">
                                        <div class="gateway-icon {{ $gateway['value'] }}">
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
                                            @endswitch
                                        </div>
                                        <div class="gateway-details">
                                            <h6>{{ $gateway['label'] }}</h6>
                                            <small>
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
                                            <div class="fee-label">Total with fee</div>
                                            <div class="fee-amount">RM {{ number_format($gatewayFees[$gateway['value']]['total'], 2) }}</div>
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        <!-- Pay Button -->
                        <a href="{{ route('payment.checkout', $invoice) }}" class="btn btn-pay-now">
                            <i class="fas fa-lock me-2"></i> Proceed to Payment
                        </a>

                        <div class="secure-note">
                            <i class="fas fa-shield-alt"></i> Secured by trusted payment gateways
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No payment gateway is currently available. Please contact the admin or pay at the center.
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Child Selector for Unpaid Invoices -->
            @if(isset($children) && $children->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-child me-2"></i> Select Child</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav child-tab" id="childTabs" role="tablist">
                            @foreach($children as $index => $child)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                            id="child-{{ $child->id }}-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#child-{{ $child->id }}"
                                            type="button"
                                            role="tab">
                                        <span class="child-avatar">
                                            {{ strtoupper(substr($child->user->name, 0, 1)) }}
                                        </span>
                                        {{ $child->user->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="childTabsContent">
                    @foreach($children as $index => $child)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                             id="child-{{ $child->id }}"
                             role="tabpanel">

                            @php
                                $childInvoices = $child->invoices()
                                    ->whereIn('status', ['pending', 'partial', 'overdue'])
                                    ->orderBy('due_date')
                                    ->get();
                            @endphp

                            @if($childInvoices->isNotEmpty())
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-file-invoice-dollar me-2"></i>
                                            Pending Invoices for {{ $child->user->name }}
                                        </h5>
                                    </div>
                                    <div class="card-body unpaid-invoices">
                                        @foreach($childInvoices as $inv)
                                            <a href="{{ route('parent.payments.pay-online', ['invoice' => $inv->id]) }}"
                                               class="invoice-item {{ $inv->isOverdue() ? 'overdue' : '' }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1 text-dark">{{ $inv->invoice_number }}</h6>
                                                        <small class="text-muted">
                                                            {{ $inv->type_label }} |
                                                            Due: {{ $inv->due_date ? $inv->due_date->format('d M Y') : 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <h5 class="mb-0 {{ $inv->isOverdue() ? 'text-danger' : 'text-primary' }}">
                                                            RM {{ number_format($inv->balance, 2) }}
                                                        </h5>
                                                        @if($inv->isOverdue())
                                                            <span class="badge bg-danger">Overdue</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark">{{ ucfirst($inv->status) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                        <h5>All Paid Up!</h5>
                                        <p class="text-muted">{{ $child->user->name }} doesn't have any pending invoices.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                        <h5>No Children Linked</h5>
                        <p class="text-muted">You don't have any children linked to your account.</p>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Payment Info Card -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Payment Information</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Payments are processed securely through trusted payment gateways.</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>You will receive a confirmation email upon successful payment.</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Gateway fees may apply depending on the payment method.</small>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>You can pay for any of your children's invoices from here.</small>
                    </li>
                    <li>
                        <i class="fas fa-check text-success me-2"></i>
                        <small>For issues, contact our support team.</small>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Quick Stats -->
        @if(isset($children) && $children->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Payment Summary</h6>
            </div>
            <div class="card-body">
                @php
                    $totalPending = 0;
                    $totalOverdue = 0;
                    foreach($children as $child) {
                        $totalPending += $child->invoices()->whereIn('status', ['pending', 'partial'])->count();
                        $totalOverdue += $child->invoices()->where('status', 'overdue')
                            ->orWhere(function($q) {
                                $q->whereIn('status', ['pending', 'partial'])->where('due_date', '<', now());
                            })->count();
                    }
                @endphp
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Pending Invoices</span>
                    <span class="badge bg-warning">{{ $totalPending }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Overdue Invoices</span>
                    <span class="badge bg-danger">{{ $totalOverdue }}</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Contact Support -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-headset me-2"></i> Need Help?</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    If you have any questions about payments, feel free to contact us.
                </p>
                <div class="d-grid gap-2">
                    <a href="mailto:support@arenamatriks.edu.my" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-envelope me-2"></i> Email Support
                    </a>
                    <a href="https://wa.me/60123456789" target="_blank" class="btn btn-outline-success btn-sm">
                        <i class="fab fa-whatsapp me-2"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Gateway selection
    $('.gateway-option').on('click', function() {
        $('.gateway-option').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
    });
});
</script>
@endpush
