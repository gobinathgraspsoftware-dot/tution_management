@extends('layouts.app')

@section('title', 'Referral Details')
@section('page-title', 'Referral Details')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="fas fa-user-friends me-2"></i> Referral Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.referrals.index') }}">Referrals</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.referrals.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Info -->
    <div class="col-md-8">
        <!-- Referral Info Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i> Referral Information</span>
                @if($referral->status === 'completed')
                    <span class="badge bg-success fs-6">Completed</span>
                @elseif($referral->status === 'pending')
                    <span class="badge bg-warning fs-6">Pending</span>
                @elseif($referral->status === 'expired')
                    <span class="badge bg-secondary fs-6">Expired</span>
                @elseif($referral->status === 'cancelled')
                    <span class="badge bg-danger fs-6">Cancelled</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Referrer (Student who referred)</h6>
                        @if($referral->referrer)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 24px; color: white; font-weight: bold;">
                                    {{ strtoupper(substr($referral->referrer->user->name ?? 'NA', 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $referral->referrer->user->name ?? 'N/A' }}</h5>
                                <small class="text-muted">{{ $referral->referrer->student_id }}</small>
                                <br>
                                <a href="{{ route('admin.students.profile', $referral->referrer) }}" class="btn btn-sm btn-outline-primary mt-1">
                                    View Profile
                                </a>
                            </div>
                        </div>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%">Email</td>
                                <td><strong>{{ $referral->referrer->user->email ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Phone</td>
                                <td><strong>{{ $referral->referrer->user->phone ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Enrollments</td>
                                <td><strong>{{ $referral->referrer->enrollments->count() }}</strong></td>
                            </tr>
                        </table>
                        @else
                        <p class="text-muted">Referrer information not available</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Referred Student (New student)</h6>
                        @if($referral->referred)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle me-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 24px; color: white; font-weight: bold;">
                                    {{ strtoupper(substr($referral->referred->user->name ?? 'NA', 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $referral->referred->user->name ?? 'N/A' }}</h5>
                                <small class="text-muted">{{ $referral->referred->student_id }}</small>
                                <br>
                                <a href="{{ route('admin.students.profile', $referral->referred) }}" class="btn btn-sm btn-outline-primary mt-1">
                                    View Profile
                                </a>
                            </div>
                        </div>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%">Email</td>
                                <td><strong>{{ $referral->referred->user->email ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Phone</td>
                                <td><strong>{{ $referral->referred->user->phone ?? 'N/A' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Enrollments</td>
                                <td><strong>{{ $referral->referred->enrollments->count() }}</strong></td>
                            </tr>
                        </table>
                        @else
                        <p class="text-muted">Referred student information not available</p>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="row text-center">
                    <div class="col-md-4">
                        <small class="text-muted">Referral Code</small>
                        <h4><code>{{ $referral->referral_code }}</code></h4>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Created</small>
                        <h5>{{ $referral->created_at->format('d M Y, h:i A') }}</h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Completed</small>
                        <h5>{{ $referral->completed_at ? $referral->completed_at->format('d M Y, h:i A') : 'Not yet' }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vouchers -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-ticket-alt me-2"></i> Generated Vouchers
            </div>
            <div class="card-body">
                @if($referral->vouchers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Voucher Code</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th>Used On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referral->vouchers as $voucher)
                            <tr>
                                <td><code class="fs-6">{{ $voucher->voucher_code }}</code></td>
                                <td><strong>RM {{ number_format($voucher->amount, 2) }}</strong></td>
                                <td>
                                    @if($voucher->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($voucher->status === 'used')
                                        <span class="badge bg-info">Used</span>
                                    @else
                                        <span class="badge bg-secondary">Expired</span>
                                    @endif
                                </td>
                                <td>{{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'No expiry' }}</td>
                                <td>
                                    @if($voucher->used_at)
                                        {{ $voucher->used_at->format('d M Y') }}
                                        @if($voucher->usedOnInvoice)
                                            <br><small class="text-muted">Invoice: {{ $voucher->usedOnInvoice->invoice_number }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">No vouchers generated for this referral yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions Sidebar -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Actions
            </div>
            <div class="card-body">
                @if($referral->status === 'pending')
                    <form action="{{ route('admin.referrals.complete', $referral) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Complete this referral and generate RM50 voucher?')">
                            <i class="fas fa-check me-1"></i> Complete & Generate Voucher
                        </button>
                    </form>
                    <form action="{{ route('admin.referrals.cancel', $referral) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Cancel this referral?')">
                            <i class="fas fa-times me-1"></i> Cancel Referral
                        </button>
                    </form>
                @elseif($referral->status === 'completed')
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle me-1"></i> This referral has been completed and voucher(s) have been generated.
                    </div>
                @elseif($referral->status === 'cancelled')
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-times-circle me-1"></i> This referral has been cancelled.
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">
                        <i class="fas fa-info-circle me-1"></i> This referral has expired.
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Info -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info me-2"></i> Referral System Info
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>How it works:</strong></p>
                <ol class="small text-muted mb-0">
                    <li>Existing student shares their referral code</li>
                    <li>New student registers with the code</li>
                    <li>New student makes first payment</li>
                    <li>Referrer receives RM50 voucher</li>
                    <li>Voucher valid for 90 days</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
