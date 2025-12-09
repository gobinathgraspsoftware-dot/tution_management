@extends('layouts.app')

@section('title', 'Pending Payment Verifications')
@section('page-title', 'Pending Payment Verifications')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-clock me-2"></i> Pending Payment Verifications</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
                <li class="breadcrumb-item active">Pending Verifications</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Payments
    </a>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $payments->count() }}</h3>
                <p class="text-muted mb-0">Pending Verifications</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($payments->sum('amount'), 2) }}</h3>
                <p class="text-muted mb-0">Total Amount Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-qrcode"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $payments->where('payment_method', 'qr')->count() }}</h3>
                <p class="text-muted mb-0">QR Payments</p>
            </div>
        </div>
    </div>
</div>

@if($payments->count() > 0)
<!-- Pending Payments List -->
<div class="row">
    @foreach($payments as $payment)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 border-warning">
            <div class="card-header bg-warning bg-opacity-25 d-flex justify-content-between align-items-center">
                <span class="badge bg-info">{{ $payment->payment_number }}</span>
                <small class="text-muted">{{ $payment->created_at->diffForHumans() }}</small>
            </div>
            <div class="card-body">
                <!-- Student Info -->
                <div class="d-flex align-items-center mb-3">
                    <div class="user-avatar me-3" style="width:45px;height:45px;">
                        {{ substr($payment->student->user->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $payment->student->user->name ?? 'N/A' }}</h6>
                        <small class="text-muted">{{ $payment->student->student_id ?? 'N/A' }}</small>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Amount:</span>
                        <strong class="text-success">RM {{ number_format($payment->amount, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Method:</span>
                        <span class="badge bg-info">
                            <i class="fas fa-qrcode me-1"></i> QR Payment
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Date:</span>
                        <span>{{ $payment->payment_date->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($payment->reference_number)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Reference:</span>
                        <code>{{ $payment->reference_number }}</code>
                    </div>
                    @endif
                    @if($payment->invoice)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Invoice:</span>
                        <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="text-decoration-none">
                            {{ $payment->invoice->invoice_number }}
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Screenshot Preview -->
                @if($payment->screenshot_path)
                <div class="mb-3 text-center">
                    <a href="{{ asset('storage/' . $payment->screenshot_path) }}" target="_blank" class="d-block">
                        <img src="{{ asset('storage/' . $payment->screenshot_path) }}"
                             alt="Payment Screenshot" class="img-thumbnail" style="max-height: 150px;">
                    </a>
                    <small class="text-muted">Click to view full screenshot</small>
                </div>
                @else
                <div class="alert alert-light text-center mb-3">
                    <i class="fas fa-image text-muted"></i>
                    <small class="text-muted d-block">No screenshot uploaded</small>
                </div>
                @endif

                @if($payment->notes)
                <div class="alert alert-light mb-0">
                    <small><strong>Notes:</strong> {{ $payment->notes }}</small>
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent">
                <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" class="verify-form">
                    @csrf
                    <input type="hidden" name="verification_notes" class="verification-notes" value="">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success flex-fill btn-approve"
                                data-payment-id="{{ $payment->id }}">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger flex-fill btn-reject"
                                data-payment-id="{{ $payment->id }}">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
        <h4>All Clear!</h4>
        <p class="text-muted mb-4">No pending payment verifications at the moment.</p>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> View All Payments
        </a>
    </div>
</div>
@endif

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verificationModalTitle">Verify Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="approvalContent">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        You are about to <strong>approve</strong> this payment.
                    </div>
                </div>
                <div id="rejectionContent" class="d-none">
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle me-2"></i>
                        You are about to <strong>reject</strong> this payment.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Verification Notes</label>
                    <textarea id="modalNotes" class="form-control" rows="3"
                              placeholder="Add notes about this verification (optional)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmVerification" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
@if($payments->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-bolt me-2"></i> Bulk Actions
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-md-0 text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    You can approve or reject payments individually using the buttons above, or review them in detail by clicking the eye icon.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-1"></i> View as List
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('verificationModal'));
    let currentForm = null;
    let isApproval = true;

    // Approve button click
    document.querySelectorAll('.btn-approve').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentForm = this.closest('.verify-form');
            isApproval = true;
            showModal(true);
        });
    });

    // Reject button click
    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentForm = this.closest('.verify-form');
            isApproval = false;
            showModal(false);
        });
    });

    function showModal(approve) {
        const title = document.getElementById('verificationModalTitle');
        const approvalContent = document.getElementById('approvalContent');
        const rejectionContent = document.getElementById('rejectionContent');
        const confirmBtn = document.getElementById('confirmVerification');

        if (approve) {
            title.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i> Approve Payment';
            approvalContent.classList.remove('d-none');
            rejectionContent.classList.add('d-none');
            confirmBtn.className = 'btn btn-success';
            confirmBtn.innerHTML = '<i class="fas fa-check me-1"></i> Approve Payment';
        } else {
            title.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i> Reject Payment';
            approvalContent.classList.add('d-none');
            rejectionContent.classList.remove('d-none');
            confirmBtn.className = 'btn btn-danger';
            confirmBtn.innerHTML = '<i class="fas fa-times me-1"></i> Reject Payment';
        }

        document.getElementById('modalNotes').value = '';
        modal.show();
    }

    // Confirm verification
    document.getElementById('confirmVerification').addEventListener('click', function() {
        if (currentForm) {
            // Add hidden input for approval status
            let approvedInput = currentForm.querySelector('input[name="approved"]');
            if (!approvedInput) {
                approvedInput = document.createElement('input');
                approvedInput.type = 'hidden';
                approvedInput.name = 'approved';
                currentForm.appendChild(approvedInput);
            }
            approvedInput.value = isApproval ? '1' : '0';

            // Add notes
            currentForm.querySelector('.verification-notes').value = document.getElementById('modalNotes').value;

            // Submit form
            currentForm.submit();
        }
        modal.hide();
    });
});
</script>
@endpush

@push('styles')
<style>
.card.border-warning {
    border-width: 2px;
}

.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 15px;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}
</style>
@endpush
