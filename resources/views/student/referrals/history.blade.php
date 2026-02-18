@extends('layouts.app')

@section('title', 'Referral History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('student.referrals.index') }}">Referrals</a></li>
                <li class="breadcrumb-item active">History</li>
            </ol>
        </nav>
        <h2><i class="fas fa-history text-primary me-2"></i> Referral History</h2>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="btn-group w-100" role="group">
                <a href="{{ route('student.referrals.history', ['status' => 'all']) }}"
                   class="btn btn-{{ $status === 'all' ? 'primary' : 'outline-primary' }}">
                    All Referrals
                </a>
                <a href="{{ route('student.referrals.history', ['status' => 'pending']) }}"
                   class="btn btn-{{ $status === 'pending' ? 'primary' : 'outline-primary' }}">
                    <i class="fas fa-clock me-1"></i> Pending
                </a>
                <a href="{{ route('student.referrals.history', ['status' => 'completed']) }}"
                   class="btn btn-{{ $status === 'completed' ? 'primary' : 'outline-primary' }}">
                    <i class="fas fa-check-circle me-1"></i> Completed
                </a>
            </div>
        </div>
    </div>

    <!-- Referrals List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($referrals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Referral Code</th>
                                <th>Registration Date</th>
                                <th>Status</th>
                                <th>Completed Date</th>
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
                                            <strong>{{ $referral->referred->user->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $referral->referred->student_id }}</td>
                                    <td>
                                        <span class="badge bg-secondary font-monospace">{{ $referral->referral_code }}</span>
                                    </td>
                                    <td>{{ $referral->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($referral->status === 'completed')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i> Completed
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i> Pending Payment
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($referral->completed_at)
                                            {{ $referral->completed_at->format('d M Y') }}
                                            <br>
                                            <small class="text-muted">
                                                ({{ $referral->created_at->diffForHumans($referral->completed_at, true) }})
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.referrals.show', $referral) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    {{ $referrals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No referrals found</h5>
                    <p class="text-muted">
                        @if($status === 'pending')
                            You don't have any pending referrals.
                        @elseif($status === 'completed')
                            You don't have any completed referrals yet.
                        @else
                            Start referring friends to see them here!
                        @endif
                    </p>
                    <a href="{{ route('student.referrals.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Referrals
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
