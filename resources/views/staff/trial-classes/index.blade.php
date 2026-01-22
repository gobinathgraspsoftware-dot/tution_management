@extends('layouts.app')

@section('title', 'Trial Classes')

@section('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    .stat-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card.danger { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
    .stat-card.dark { background: linear-gradient(135deg, #434343 0%, #000000 100%); }
    .stat-card h3 { font-size: 2rem; font-weight: bold; margin-bottom: 5px; }
    .stat-card p { margin: 0; opacity: 0.9; }
    .stat-card .icon { font-size: 2.5rem; opacity: 0.3; position: absolute; right: 20px; top: 20px; }
    
    .trial-card {
        border-radius: 10px;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .trial-card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    .trial-card.status-pending { border-left: 4px solid #ffc107; }
    .trial-card.status-approved { border-left: 4px solid #17a2b8; }
    .trial-card.status-attended { border-left: 4px solid #28a745; }
    .trial-card.status-no_show { border-left: 4px solid #dc3545; }
    .trial-card.status-converted { border-left: 4px solid #6f42c1; }
    .trial-card.status-cancelled { border-left: 4px solid #6c757d; }
    
    .conversion-rate {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .today-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 0.7rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-chalkboard text-primary me-2"></i>Trial Classes
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Trial Classes</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card primary">
                <i class="fas fa-calendar-alt icon"></i>
                <h3>{{ number_format($stats['total']) }}</h3>
                <p>Total Trials</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card warning">
                <i class="fas fa-clock icon"></i>
                <h3>{{ number_format($stats['pending']) }}</h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card info">
                <i class="fas fa-check icon"></i>
                <h3>{{ number_format($stats['approved']) }}</h3>
                <p>Approved</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card success">
                <i class="fas fa-user-check icon"></i>
                <h3>{{ number_format($stats['attended']) }}</h3>
                <p>Attended</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card danger">
                <i class="fas fa-user-times icon"></i>
                <h3>{{ number_format($stats['no_show']) }}</h3>
                <p>No Show</p>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="stat-card dark">
                <i class="fas fa-exchange-alt icon"></i>
                <h3>{{ $stats['conversion_rate'] }}%</h3>
                <p>Conversion Rate</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-light border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-primary p-3 rounded-circle">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </span>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $stats['today'] }} Trial{{ $stats['today'] != 1 ? 's' : '' }} Today</h4>
                        <small class="text-muted">Scheduled for {{ now()->format('l, d M Y') }}</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-info p-3 rounded-circle">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </span>
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $stats['upcoming'] }} Upcoming Trials</h4>
                        <small class="text-muted">Pending or approved, starting from today</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('staff.trial-classes.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Student name, phone, email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="attended" {{ request('status') == 'attended' ? 'selected' : '' }}>Attended</option>
                            <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                            <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Conversion</label>
                        <select name="conversion_status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" {{ request('conversion_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="converted" {{ request('conversion_status') == 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="declined" {{ request('conversion_status') == 'declined' ? 'selected' : '' }}>Declined</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} ({{ $class->subject->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9">
                        @if(request()->hasAny(['search', 'status', 'conversion_status', 'date_from', 'date_to', 'class_id']))
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <a href="{{ route('staff.trial-classes.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Trial Classes List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Trial Classes List
            </h5>
            <span class="badge bg-primary">{{ $trialClasses->total() }} total</span>
        </div>
        <div class="card-body">
            @forelse($trialClasses as $trial)
                <div class="card trial-card status-{{ $trial->status }} position-relative">
                    @if($trial->scheduled_date && $trial->scheduled_date->isToday())
                        <span class="badge bg-danger today-badge">TODAY</span>
                    @endif
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="mb-1">
                                    @if($trial->student && $trial->student->user)
                                        {{ $trial->student->user->name }}
                                    @else
                                        {{ $trial->student_name ?? 'N/A' }}
                                    @endif
                                </h6>
                                <small class="text-muted d-block">
                                    <i class="fas fa-user me-1"></i>{{ $trial->parent_name ?? 'N/A' }}
                                </small>
                                <small class="text-muted d-block">
                                    <i class="fas fa-phone me-1"></i>{{ $trial->parent_phone ?? 'N/A' }}
                                </small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Class</small>
                                <span class="fw-semibold">{{ $trial->class->name ?? 'N/A' }}</span>
                                <br>
                                <small class="text-muted">
                                    {{ $trial->class->subject->name ?? 'N/A' }}
                                    @if($trial->class && $trial->class->teacher && $trial->class->teacher->user)
                                        | {{ $trial->class->teacher->user->name }}
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Schedule</small>
                                <span class="fw-semibold">
                                    {{ $trial->scheduled_date ? $trial->scheduled_date->format('d M Y') : 'N/A' }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    @if($trial->scheduled_time)
                                        <i class="fas fa-clock me-1"></i>{{ $trial->scheduled_time->format('h:i A') }}
                                    @else
                                        Time TBD
                                    @endif
                                </small>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted d-block">Status</small>
                                @switch($trial->status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-info">Approved</span>
                                        @break
                                    @case('attended')
                                        <span class="badge bg-success">Attended</span>
                                        @break
                                    @case('no_show')
                                        <span class="badge bg-danger">No Show</span>
                                        @break
                                    @case('converted')
                                        <span class="badge bg-primary">Converted</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-secondary">Cancelled</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($trial->status) }}</span>
                                @endswitch
                                <br>
                                <small class="text-muted">
                                    Conversion: 
                                    @switch($trial->conversion_status)
                                        @case('converted')
                                            <span class="text-success">✓ Converted</span>
                                            @break
                                        @case('declined')
                                            <span class="text-danger">✗ Declined</span>
                                            @break
                                        @default
                                            <span class="text-muted">Pending</span>
                                    @endswitch
                                </small>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="{{ route('staff.trial-classes.show', $trial) }}" 
                                   class="btn btn-sm btn-outline-primary me-1" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if(in_array($trial->status, ['pending', 'approved']))
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#attendanceModal{{ $trial->id }}"
                                            title="Mark Attendance">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                        @if($trial->feedback)
                            <hr class="my-2">
                            <small class="text-muted">
                                <i class="fas fa-comment me-1"></i>{{ Str::limit($trial->feedback, 100) }}
                            </small>
                        @endif
                    </div>
                </div>

                <!-- Attendance Modal -->
                @if(in_array($trial->status, ['pending', 'approved']))
                <div class="modal fade" id="attendanceModal{{ $trial->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('staff.trial-classes.mark-attendance', $trial) }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Mark Attendance</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Mark attendance for <strong>{{ $trial->student_name ?? ($trial->student->user->name ?? 'N/A') }}</strong></p>
                                    <p class="text-muted small">
                                        Class: {{ $trial->class->name ?? 'N/A' }}<br>
                                        Date: {{ $trial->scheduled_date?->format('d M Y') ?? 'N/A' }}
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label">Did the student attend?</label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attended" value="1" id="attended{{ $trial->id }}" required>
                                                <label class="form-check-label" for="attended{{ $trial->id }}">
                                                    <i class="fas fa-check text-success me-1"></i>Yes, Attended
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="attended" value="0" id="noShow{{ $trial->id }}">
                                                <label class="form-check-label" for="noShow{{ $trial->id }}">
                                                    <i class="fas fa-times text-danger me-1"></i>No Show
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Feedback (Optional)</label>
                                        <textarea name="feedback" class="form-control" rows="3" 
                                                  placeholder="Any notes or feedback about the trial class...">{{ $trial->feedback }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Attendance
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-chalkboard fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No trial classes found</h5>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                </div>
            @endforelse
        </div>

        @if($trialClasses->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $trialClasses->firstItem() }} to {{ $trialClasses->lastItem() }} of {{ $trialClasses->total() }} entries
                </small>
                {{ $trialClasses->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on select change
    $('select[name="status"], select[name="conversion_status"], select[name="class_id"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
