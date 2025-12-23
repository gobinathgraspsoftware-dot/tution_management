@extends('layouts.app')

@section('title', 'Password Reset Logs')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-2">
                <i class="fas fa-key text-primary"></i> Password Reset Logs
            </h1>
            <p class="text-muted">Monitor and manage password reset attempts</p>
        </div>
        <div class="col-md-4 text-end">
            <form action="{{ route('admin.password-resets.clear-expired') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Clear all expired OTPs?')">
                    <i class="fas fa-trash-alt"></i> Clear Expired OTPs
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Requests</h6>
                            <h3 class="mb-0">{{ \DB::table('password_reset_tokens')->count() }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Valid OTPs</h6>
                            <h3 class="mb-0">
                                {{ \DB::table('password_reset_tokens')
                                    ->where('created_at', '>=', now()->subMinutes(15))
                                    ->count() }}
                            </h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Expired OTPs</h6>
                            <h3 class="mb-0">
                                {{ \DB::table('password_reset_tokens')
                                    ->where('created_at', '<', now()->subMinutes(15))
                                    ->count() }}
                            </h3>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today's Requests</h6>
                            <h3 class="mb-0">
                                {{ \DB::table('password_reset_tokens')
                                    ->whereDate('created_at', today())
                                    ->count() }}
                            </h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Reset Requests Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Recent Password Reset Requests
                    </h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by phone or email...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="resetTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>OTP</th>
                            <th>Status</th>
                            <th>Time Remaining</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resets as $reset)
                            @php
                                $createdAt = \Carbon\Carbon::parse($reset->created_at);
                                $expiresAt = $createdAt->copy()->addMinutes(15);
                                $isExpired = now()->greaterThan($expiresAt);
                                $timeRemaining = !$isExpired ? now()->diffInMinutes($expiresAt) : 0;
                            @endphp
                            <tr class="{{ $isExpired ? 'table-danger' : '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <i class="fab fa-whatsapp text-success"></i>
                                    {{ $reset->phone ?? '-' }}
                                </td>
                                <td>
                                    <i class="fas fa-envelope text-muted"></i>
                                    {{ $reset->email ?? '-' }}
                                </td>
                                <td>
                                    @if(config('app.debug'))
                                        <code class="text-primary">{{ $reset->otp }}</code>
                                    @else
                                        <span class="text-muted">Hidden</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isExpired)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle"></i> Expired
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Valid
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$isExpired)
                                        <span class="badge bg-info">
                                            {{ $timeRemaining }} min left
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $createdAt->format('Y-m-d H:i:s') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $createdAt->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($isExpired)
                                        <form action="{{ route('admin.password-resets.clear-expired') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-warning" disabled title="Cannot delete valid OTP">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No password reset requests found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $resets->firstItem() ?? 0 }} to {{ $resets->lastItem() ?? 0 }} of {{ $resets->total() }} requests
                </div>
                <div>
                    {{ $resets->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-history"></i> Recent Password Reset Activity
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Phone</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recentActivity = \Spatie\Activitylog\Models\Activity::where('description', 'LIKE', '%password reset%')
                                ->orWhere('description', 'LIKE', '%Password was reset%')
                                ->latest()
                                ->limit(10)
                                ->get();
                        @endphp
                        @forelse($recentActivity as $activity)
                            <tr>
                                <td>
                                    @if($activity->causer)
                                        <strong>{{ $activity->causer->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $activity->causer->email }}</small>
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td>
                                    @if(str_contains($activity->description, 'sent'))
                                        <span class="badge bg-info">
                                            <i class="fas fa-paper-plane"></i> OTP Sent
                                        </span>
                                    @elseif(str_contains($activity->description, 'reset'))
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Password Reset
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $activity->description }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($activity->properties && isset($activity->properties['phone']))
                                        <code>{{ $activity->properties['phone'] }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $activity->properties['ip'] ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $activity->created_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No recent activity</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#resetTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Auto-refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);

    // Countdown timer for valid OTPs
    updateCountdowns();
    setInterval(updateCountdowns, 60000); // Update every minute

    function updateCountdowns() {
        $('.badge.bg-info').each(function() {
            const text = $(this).text();
            const minutes = parseInt(text);
            if (minutes > 0) {
                $(this).text((minutes - 1) + ' min left');
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    margin-bottom: 1.5rem;
}

.card-header {
    border-bottom: 2px solid #f0f0f0;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    padding: 0.4em 0.6em;
    font-weight: 500;
}

code {
    padding: 0.2em 0.4em;
    font-size: 90%;
    border-radius: 3px;
}

.table-danger {
    background-color: #f8d7da;
}

.input-group-text {
    background-color: #fff;
    border-right: none;
}

.input-group .form-control {
    border-left: none;
}

.input-group .form-control:focus {
    border-left: none;
    box-shadow: none;
}
</style>
@endpush
