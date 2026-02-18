@extends('layouts.app')

@section('title', 'My Vouchers')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.referrals.index') }}">Referrals</a></li>
                <li class="breadcrumb-item active">My Vouchers</li>
            </ol>
        </nav>
        <h2><i class="fas fa-ticket-alt text-primary me-2"></i> My Vouchers</h2>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-ticket-alt text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Vouchers</h6>
                            <h4 class="mb-0">{{ $voucherStats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-check-circle text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h4 class="mb-0">{{ $voucherStats['active'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-coins text-info fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Available Value</h6>
                            <h4 class="mb-0">RM {{ number_format($voucherStats['total_value'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-chart-line text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Earned</h6>
                            <h4 class="mb-0">RM {{ number_format($voucherStats['total_earned'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="btn-group w-100" role="group">
                <a href="{{ route('student.referrals.vouchers', ['filter' => 'all']) }}"
                   class="btn btn-{{ $filter === 'all' ? 'primary' : 'outline-primary' }}">
                    All Vouchers
                </a>
                <a href="{{ route('student.referrals.vouchers', ['filter' => 'active']) }}"
                   class="btn btn-{{ $filter === 'active' ? 'primary' : 'outline-primary' }}">
                    Active ({{ $voucherStats['active'] }})
                </a>
                <a href="{{ route('student.referrals.vouchers', ['filter' => 'used']) }}"
                   class="btn btn-{{ $filter === 'used' ? 'primary' : 'outline-primary' }}">
                    Used ({{ $voucherStats['used'] }})
                </a>
                <a href="{{ route('student.referrals.vouchers', ['filter' => 'expired']) }}"
                   class="btn btn-{{ $filter === 'expired' ? 'primary' : 'outline-primary' }}">
                    Expired ({{ $voucherStats['expired'] }})
                </a>
            </div>
        </div>
    </div>

    <!-- Vouchers List -->
    <div class="row">
        @forelse($vouchers as $voucher)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 {{ $voucher->status === 'active' ? 'border-start border-success border-4' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">
                                    @if($voucher->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($voucher->status === 'used')
                                        <span class="badge bg-secondary">Used</span>
                                    @else
                                        <span class="badge bg-danger">Expired</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 text-primary">RM {{ number_format($voucher->amount, 2) }}</h3>
                            </div>
                        </div>

                        <!-- Voucher Code -->
                        <div class="bg-light rounded p-3 mb-3">
                            <label class="small text-muted mb-1">Voucher Code</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text"
                                       class="form-control form-control-lg font-monospace fw-bold"
                                       value="{{ $voucher->voucher_code }}"
                                       readonly
                                       id="voucher-{{ $voucher->id }}">
                                @if($voucher->status === 'active')
                                    <button onclick="copyVoucherCode('{{ $voucher->voucher_code }}', {{ $voucher->id }})"
                                            class="btn btn-primary">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Voucher Details -->
                        <div class="mb-3">
                            @if($voucher->referral && $voucher->referral->referred)
                                <p class="mb-1 small">
                                    <i class="fas fa-user me-1"></i>
                                    <strong>Earned from:</strong> {{ $voucher->referral->referred->user->name }}
                                </p>
                            @endif

                            <p class="mb-1 small">
                                <i class="fas fa-calendar-plus me-1"></i>
                                <strong>Issued:</strong> {{ $voucher->created_at->format('d M Y') }}
                            </p>

                            @if($voucher->status === 'active')
                                <p class="mb-1 small {{ $voucher->expires_at && $voucher->expires_at->diffInDays(now()) <= 7 ? 'text-danger' : '' }}">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    <strong>Expires:</strong>
                                    @if($voucher->expires_at)
                                        {{ $voucher->expires_at->format('d M Y') }}
                                        ({{ $voucher->expires_at->diffForHumans() }})
                                    @else
                                        Never
                                    @endif
                                </p>
                            @elseif($voucher->status === 'used')
                                <p class="mb-1 small">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <strong>Used on:</strong> {{ $voucher->used_at ? $voucher->used_at->format('d M Y') : 'N/A' }}
                                </p>
                                @if($voucher->usedOnInvoice)
                                    <p class="mb-1 small">
                                        <i class="fas fa-file-invoice me-1"></i>
                                        <strong>Invoice:</strong> {{ $voucher->usedOnInvoice->invoice_number }}
                                    </p>
                                @endif
                            @elseif($voucher->status === 'expired')
                                <p class="mb-1 small text-danger">
                                    <i class="fas fa-times-circle me-1"></i>
                                    <strong>Expired on:</strong> {{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'N/A' }}
                                </p>
                            @endif
                        </div>

                        <!-- Actions -->
                        @if($voucher->status === 'active')
                            <div class="alert alert-info mb-0 small">
                                <i class="fas fa-info-circle me-1"></i>
                                Use this voucher code during payment to get RM {{ number_format($voucher->amount, 2) }} discount!
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No vouchers found</h5>
                        <p class="text-muted">
                            @if($filter === 'active')
                                You don't have any active vouchers.
                            @elseif($filter === 'used')
                                You haven't used any vouchers yet.
                            @elseif($filter === 'expired')
                                You don't have any expired vouchers.
                            @else
                                Start referring friends to earn vouchers!
                            @endif
                        </p>
                        <a href="{{ route('student.referrals.index') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Start Referring
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($vouchers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $vouchers->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function copyVoucherCode(code, voucherId) {
    const input = document.getElementById('voucher-' + voucherId);
    input.select();
    document.execCommand('copy');

    alert('✓ Voucher code copied: ' + code);
}
</script>
@endpush
@endsection
