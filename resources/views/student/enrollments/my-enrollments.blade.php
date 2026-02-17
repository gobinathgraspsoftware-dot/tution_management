@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">My Enrollments</h4>
                <div class="page-title-right">
                    <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-warning me-2">
                        <i class="fas fa-plus me-1"></i> Enroll in Class
                    </a>
                    <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-success">
                        <i class="fas fa-box me-1"></i> Browse Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm rounded-circle bg-primary bg-soft d-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                                <i class="fas fa-book-reader text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Total Enrollments</p>
                            <h4 class="mb-0">{{ $stats['total_enrollments'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm rounded-circle bg-success bg-soft d-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Active Classes</p>
                            <h4 class="mb-0">{{ $stats['active_enrollments'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm rounded-circle bg-warning bg-soft d-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                                <i class="fas fa-clock text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Pending</p>
                            <h4 class="mb-0">{{ $stats['pending_enrollments'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm rounded-circle bg-info bg-soft d-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
                                <i class="fas fa-dollar-sign text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1 small">Monthly Fee</p>
                            <h4 class="mb-0">RM {{ number_format($stats['total_monthly_fee'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Enrollments List --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($enrollments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="avatar-lg mx-auto rounded-circle bg-primary bg-soft d-flex align-items-center justify-content-center" style="width: 5rem; height: 5rem;">
                                    <i class="fas fa-book-open text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                            </div>
                            <h5>No Enrollments Yet</h5>
                            <p class="text-muted mb-4">You haven't enrolled in any classes yet. Browse available classes or packages to get started!</p>
                            <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> Browse Classes
                            </a>
                            <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-success">
                                <i class="fas fa-box me-1"></i> Browse Packages
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class / Package</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Schedule</th>
                                        <th>Monthly Fee</th>
                                        <th>Status</th>
                                        <th>Enrolled Date</th>
                                        <th class="text-center" style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollments as $enrollment)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $enrollment->class->name ?? 'N/A' }}</strong>
                                                    @if($enrollment->package)
                                                        <br><small class="text-muted">
                                                            <i class="fas fa-box me-1"></i>{{ $enrollment->package->name }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($enrollment->class && $enrollment->class->subject)
                                                    <span class="badge bg-primary bg-soft text-primary">
                                                        {{ $enrollment->class->subject->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->class && $enrollment->class->teacher)
                                                    {{ $enrollment->class->teacher->user->name ?? 'N/A' }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($enrollment->class && $enrollment->class->schedules->isNotEmpty())
                                                    @foreach($enrollment->class->schedules->take(2) as $schedule)
                                                        <small>{{ ucfirst($schedule->day_of_week) }}: {{ date('g:i A', strtotime($schedule->start_time)) }}</small>
                                                        @if(!$loop->last)<br>@endif
                                                    @endforeach
                                                    @if($enrollment->class->schedules->count() > 2)
                                                        <br><small class="text-muted">+{{ $enrollment->class->schedules->count() - 2 }} more</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No schedule</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>RM {{ number_format($enrollment->monthly_fee, 2) }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'active' => 'success',
                                                        'pending' => 'warning',
                                                        'suspended' => 'danger',
                                                        'cancelled' => 'secondary',
                                                        'completed' => 'info',
                                                    ];
                                                    $color = $statusColors[$enrollment->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($enrollment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $enrollment->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('student.enrollments.show', $enrollment) }}"
                                                   class="btn btn-sm btn-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Fix for soft background colors if not defined in your CSS */
    .bg-soft {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .bg-primary.bg-soft {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }
    .bg-success.bg-soft {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    .bg-warning.bg-soft {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
    .bg-info.bg-soft {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }

    /* Ensure icons are centered */
    .avatar-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Table improvements */
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Action button styling */
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    /* Badge styling */
    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection
