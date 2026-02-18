@extends('layouts.app')

@section('title', 'Referral Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.referrals.index') }}">Referrals</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
        <h2><i class="fas fa-user-plus text-primary me-2"></i> Referral Details</h2>
    </div>

    <div class="row">
        <!-- Referral Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Referral Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Referred Student</label>
                            <h5 class="mb-0">{{ $referral->referred->user->name }}</h5>
                            <p class="text-muted small mb-0">{{ $referral->referred->student_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Status</label>
                            <div>
                                @if($referral->status === 'completed')
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle me-1"></i> Completed
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Referral Code Used</label>
                            <h6 class="mb-0">{{ $referral->referral_code }}</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Registration Date</label>
                            <h6 class="mb-0">{{ $referral->created_at->format('d M Y, h:i A') }}</h6>
                        </div>
                    </div>

                    @if($referral->status === 'completed')
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small text-muted">Completed Date</label>
                                <h6 class="mb-0">{{ $referral->completed_at ? $referral->completed_at->format('d M Y, h:i A') : 'N/A' }}</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted">Time to Complete</label>
                                <h6 class="mb-0">
                                    @if($referral->completed_at)
                                        {{ $referral->created_at->diffForHumans($referral->completed_at, true) }}
                                    @else
                                        N/A
                                    @endif
                                </h6>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Waiting for first payment</strong><br>
                            This referral will be completed when {{ $referral->referred->user->name }} makes their first payment.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vouchers Earned -->
            @if($referral->vouchers->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i> Vouchers Earned</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Voucher Code</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Expires</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referral->vouchers as $voucher)
                                        <tr>
                                            <td>
                                                <span class="font-monospace fw-bold">{{ $voucher->voucher_code }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-primary">RM {{ number_format($voucher->amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($voucher->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($voucher->status === 'used')
                                                    <span class="badge bg-secondary">Used</span>
                                                @else
                                                    <span class="badge bg-danger">Expired</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($voucher->expires_at)
                                                    {{ $voucher->expires_at->format('d M Y') }}
                                                @else
                                                    Never
                                                @endif
                                            </td>
                                            <td>
                                                @if($voucher->status === 'active')
                                                    <button onclick="copyVoucherCode('{{ $voucher->voucher_code }}')"
                                                            class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-copy"></i> Copy
                                                    </button>
                                                @elseif($voucher->status === 'used')
                                                    <span class="text-muted small">
                                                        Used on {{ $voucher->used_at ? $voucher->used_at->format('d M Y') : 'N/A' }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Expired</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Actions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('student.referrals.index') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-arrow-left me-2"></i> Back to Referrals
                    </a>
                    <a href="{{ route('student.referrals.vouchers') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-ticket-alt me-2"></i> View All Vouchers
                    </a>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i> Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Registration</h6>
                                <p class="small text-muted mb-0">
                                    {{ $referral->created_at->format('d M Y, h:i A') }}
                                </p>
                                <p class="small mb-0">{{ $referral->referred->user->name }} registered with your code</p>
                            </div>
                        </div>

                        @if($referral->status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Completed</h6>
                                    <p class="small text-muted mb-0">
                                        {{ $referral->completed_at ? $referral->completed_at->format('d M Y, h:i A') : 'N/A' }}
                                    </p>
                                    <p class="small mb-0">First payment received, voucher issued</p>
                                </div>
                            </div>
                        @else
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Pending Payment</h6>
                                    <p class="small mb-0">Waiting for first payment...</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -23px;
    top: 10px;
    bottom: -10px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px currentColor;
}
</style>
@endpush

@push('scripts')
<script>
function copyVoucherCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('✓ Voucher code copied: ' + code);
    });
}
</script>
@endpush
@endsection
