@extends('layouts.app')

@section('title', 'My Referrals')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-user-plus text-primary me-2"></i> My Referrals</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Referrals</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('student.referrals.vouchers') }}" class="btn btn-outline-primary">
                <i class="fas fa-ticket-alt me-1"></i> My Vouchers
            </a>
        </div>
    </div>

    <!-- Referral Code Card -->
    <div class="card border-0 shadow-sm mb-4 bg-gradient" style="background: linear-gradient(180deg, #fda530 0%, #4c4c4c 100%) !important;">
        <div class="card-body text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2"><i class="fas fa-gift me-2"></i> Share & Earn RM50 Voucher!</h4>
                    <p class="mb-3">Invite your friends to join Arena Matriks. When they enroll and make their first payment, you both earn rewards!</p>

                    <div class="bg-white bg-opacity-25 rounded p-3 mb-3">
                        <label class="small mb-2"><strong>Your Referral Code:</strong></label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text"
                                   id="referralCodeInput"
                                   class="form-control form-control-lg bg-white"
                                   value="{{ $referralCode }}"
                                   readonly
                                   style="font-size: 1.5rem; font-weight: bold; letter-spacing: 2px;">
                            <button onclick="copyReferralCode()" class="btn btn-light btn-lg">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button onclick="shareWhatsApp()" class="btn btn-success">
                            <i class="fab fa-whatsapp me-1"></i> Share via WhatsApp
                        </button>
                        <button onclick="shareLink()" class="btn btn-light">
                            <i class="fas fa-share-alt me-1"></i> Share Link
                        </button>
                    </div>
                </div>

                <div class="col-md-4 text-center">
                    <div class="bg-white bg-opacity-25 rounded p-4">
                        <i class="fas fa-users fa-4x mb-3"></i>
                        <h3 class="mb-0">{{ $stats['total_referrals'] }}</h3>
                        <p class="mb-0 small">Total Referrals</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
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
                            <h6 class="text-muted mb-1">Successful</h6>
                            <h4 class="mb-0">{{ $stats['completed_referrals'] }}</h4>
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
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h4 class="mb-0">{{ $stats['pending_referrals'] }}</h4>
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
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-ticket-alt text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Active Vouchers</h6>
                            <h4 class="mb-0">{{ $stats['active_vouchers'] }}</h4>
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
                            <h6 class="text-muted mb-1">Total Earned</h6>
                            <h4 class="mb-0">RM {{ number_format($stats['total_earned'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Vouchers -->
        @if($activeVouchers->count() > 0)
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i> Active Vouchers</h5>
                    <a href="{{ route('student.referrals.vouchers') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($activeVouchers->take(3) as $voucher)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <span class="badge bg-success">{{ $voucher->voucher_code }}</span>
                                        </h6>
                                        <p class="mb-1 small text-muted">
                                            Value: <strong>RM {{ number_format($voucher->amount, 2) }}</strong>
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            <i class="fas fa-calendar me-1"></i> Expires: {{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'Never' }}
                                        </p>
                                    </div>
                                    <button onclick="copyVoucherCode('{{ $voucher->voucher_code }}')" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Referral History -->
        <div class="col-md-{{ $activeVouchers->count() > 0 ? '6' : '12' }} mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Referrals</h5>
                    <a href="{{ route('student.referrals.history') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($referrals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referrals as $referral)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $referral->referred->user->name }}</div>
                                                        <small class="text-muted">{{ $referral->referred->student_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $referral->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if($referral->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('student.referrals.show', $referral) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $referrals->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No referrals yet</h5>
                            <p class="text-muted">Start sharing your referral code to earn rewards!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- How it Works -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> How It Works</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                        <i class="fas fa-share-alt fa-2x text-primary"></i>
                    </div>
                    <h6>1. Share Your Code</h6>
                    <p class="small text-muted">Share your unique referral code with friends and family</p>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-success bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                        <i class="fas fa-user-plus fa-2x text-success"></i>
                    </div>
                    <h6>2. They Register</h6>
                    <p class="small text-muted">Your friend registers using your referral code</p>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                        <i class="fas fa-credit-card fa-2x text-warning"></i>
                    </div>
                    <h6>3. First Payment</h6>
                    <p class="small text-muted">They complete their first payment</p>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="bg-info bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                        <i class="fas fa-gift fa-2x text-info"></i>
                    </div>
                    <h6>4. Get Rewarded</h6>
                    <p class="small text-muted">You receive a RM50 voucher instantly!</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralCode() {
    const input = document.getElementById('referralCodeInput');
    input.select();
    document.execCommand('copy');

    // Show success message
    alert('✓ Referral code copied to clipboard!');
}

function copyVoucherCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('✓ Voucher code copied: ' + code);
    });
}

function shareWhatsApp() {
    const referralCode = '{{ $referralCode }}';
    const message = `🎓 Join Arena Matriks Edu Group with my referral code and we both get rewards!\n\nUse my code: *${referralCode}*\n\nRegister here: {{ route('register') }}?ref=${referralCode}\n\n🎁 You'll get quality education and I'll earn a voucher. Win-win!`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

function shareLink() {
    const referralLink = '{{ route('register') }}?ref={{ $referralCode }}';
    navigator.clipboard.writeText(referralLink).then(() => {
        alert('✓ Referral link copied to clipboard!\n\n' + referralLink);
    });
}
</script>
@endpush
@endsection
