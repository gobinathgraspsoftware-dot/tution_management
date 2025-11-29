@extends('layouts.app')

@section('title', 'My Children')
@section('page-title', 'My Children')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-users me-2"></i> My Children</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Children</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('parent.children.register') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Register New Child
        </a>
    </div>
</div>

@if($children->count() > 0)
<div class="row">
    @foreach($children as $child)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-2" style="width: 40px; height: 40px;">
                        {{ substr($child->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $child->user->name }}</h6>
                        <small class="text-muted">{{ $child->student_id }}</small>
                    </div>
                </div>
                @if($child->approval_status == 'approved')
                    <span class="badge bg-success">Approved</span>
                @elseif($child->approval_status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                @else
                    <span class="badge bg-danger">Rejected</span>
                @endif
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <i class="fas fa-school text-muted me-2"></i>
                    {{ $child->school_name }}
                </div>
                <div class="mb-2">
                    <i class="fas fa-graduation-cap text-muted me-2"></i>
                    {{ $child->grade_level }}
                </div>
                <div class="mb-2">
                    <i class="fas fa-birthday-cake text-muted me-2"></i>
                    {{ $child->date_of_birth->format('d M Y') }}
                    ({{ $child->date_of_birth->age }} years old)
                </div>

                @if($child->enrollments->count() > 0)
                <hr>
                <h6 class="small text-muted mb-2">Active Enrollments:</h6>
                @foreach($child->enrollments->where('status', 'active')->take(2) as $enrollment)
                <div class="mb-1">
                    <span class="badge bg-primary">{{ $enrollment->package->name }}</span>
                </div>
                @endforeach
                @endif
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex gap-2">
                    <a href="{{ route('parent.children.show', $child) }}" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="fas fa-eye me-1"></i> View Details
                    </a>
                    @if($child->approval_status == 'approved')
                    <a href="#" class="btn btn-sm btn-outline-success flex-fill">
                        <i class="fas fa-book me-1"></i> Enroll
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-users fa-4x text-muted mb-3"></i>
        <h4>No Children Registered</h4>
        <p class="text-muted">You haven't registered any children yet.</p>
        <a href="{{ route('parent.children.register') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Register Your First Child
        </a>
    </div>
</div>
@endif

<!-- Referral Code Section -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-gift me-2 text-warning"></i> Share Your Referral Code
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-2">Share your referral code with friends and family. Both you and the referred student will receive <strong>RM50 discount</strong>!</p>
                @if($children->where('approval_status', 'approved')->first())
                    @php $referralCode = $children->where('approval_status', 'approved')->first()->referral_code; @endphp
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $referralCode }}" id="referralCode" readonly>
                        <button class="btn btn-outline-primary" onclick="copyReferralCode()">
                            <i class="fas fa-copy me-1"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">
                        Registration link: {{ route('public.registration.student') }}?ref={{ $referralCode }}
                    </small>
                @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Referral code will be available once your child's registration is approved.
                    </p>
                @endif
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-hands-helping fa-4x text-warning"></i>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyReferralCode() {
        var code = document.getElementById('referralCode');
        code.select();
        document.execCommand('copy');
        alert('Referral code copied!');
    }
</script>
@endpush
