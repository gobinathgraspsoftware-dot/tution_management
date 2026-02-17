@extends('layouts.app')

@section('title', 'Class Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $class->name }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('student.classes.index') }}">My Classes</a></li>
                    <li class="breadcrumb-item active">{{ $class->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('student.classes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Classes
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Class Information -->
        <div class="col-lg-8">
            <!-- Class Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Class Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-book me-2 text-primary"></i>Subject:</strong>
                            <p class="mb-0">{{ $class->subject->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-chalkboard-teacher me-2 text-info"></i>Teacher:</strong>
                            <p class="mb-0">{{ $class->teacher->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar me-2 text-success"></i>Schedule:</strong>
                            <p class="mb-0">
                                @if($class->schedule_days && $class->schedule_time)
                                    {{ $class->schedule_days }} @ {{ $class->schedule_time }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-map-marker-alt me-2 text-danger"></i>Location:</strong>
                            <p class="mb-0">{{ $class->location ?? 'TBA' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-users me-2 text-warning"></i>Capacity:</strong>
                            <p class="mb-0">{{ $class->current_students ?? 0 }} / {{ $class->max_students ?? 'Unlimited' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-tag me-2 text-secondary"></i>Status:</strong>
                            <p class="mb-0">
                                @if($class->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($class->status) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($class->description)
                        <div class="row">
                            <div class="col-12">
                                <strong><i class="fas fa-align-left me-2 text-primary"></i>Description:</strong>
                                <p class="mb-0 mt-2">{{ $class->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Enrollment Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>My Enrollment</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Enrollment Date:</strong>
                            <p class="mb-0">{{ $enrollment->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong>
                            <p class="mb-0">
                                @switch($enrollment->status)
                                    @case('active')
                                        <span class="badge bg-success">Active</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-info">Completed</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge bg-warning">Suspended</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($enrollment->status) }}</span>
                                @endswitch
                            </p>
                        </div>
                        @if($enrollment->package)
                            <div class="col-md-6 mb-3">
                                <strong>Package:</strong>
                                <p class="mb-0">{{ $enrollment->package->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Sessions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Recent Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Topic</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSessions as $session)
                                    <tr>
                                        <td>
                                            {{ $session->session_date->format('d M Y') }}
                                            <br>
                                            <small class="text-muted">{{ $session->session_date->format('l') }}</small>
                                        </td>
                                        <td>{{ $session->topic ?? 'N/A' }}</td>
                                        <td>{{ $session->duration ?? 'N/A' }} mins</td>
                                        <td>
                                            @if($session->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($session->status == 'scheduled')
                                                <span class="badge bg-primary">Scheduled</span>
                                            @elseif($session->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($session->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No sessions yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if(Route::has('timetable.index'))
                        <a href="{{ route('timetable.index') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-calendar-week me-1"></i> View Timetable
                        </a>
                    @endif

                    @if(Route::has('student.attendance.index'))
                        <a href="{{ route('student.attendance.index', ['class_id' => $class->id]) }}" class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-check-square me-1"></i> View Attendance
                        </a>
                    @endif

                    @if(Route::has('student.results.index'))
                        <a href="{{ route('student.results.index', ['class_id' => $class->id]) }}" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-chart-line me-1"></i> View Results
                        </a>
                    @endif

                    @if(Route::has('student.materials.index'))
                        <a href="{{ route('student.materials.index') }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-folder me-1"></i> Class Materials
                        </a>
                    @endif
                </div>
            </div>

            <!-- Classmates -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Classmates ({{ $classmates->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($classmates->take(5) as $mate)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                {{ substr($mate->student->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $mate->student->user->name ?? 'Unknown' }}</strong>
                                <br>
                                <small class="text-muted">
                                    Enrolled: {{ $mate->created_at->format('M Y') }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No other students yet</p>
                    @endforelse

                    @if($classmates->count() > 5)
                        <small class="text-muted">
                            And {{ $classmates->count() - 5 }} more...
                        </small>
                    @endif
                </div>
            </div>

            <!-- Teacher Contact -->
            @if($class->teacher)
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Teacher Contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                                {{ substr($class->teacher->user->name ?? 'T', 0, 1) }}
                            </div>
                            <h6 class="mb-1">{{ $class->teacher->user->name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $class->subject->name ?? 'Subject' }} Teacher</small>
                        </div>

                        @if($class->teacher->user->email)
                            <div class="mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <small>{{ $class->teacher->user->email }}</small>
                            </div>
                        @endif

                        @if($class->teacher->phone)
                            <div class="mb-2">
                                <i class="fas fa-phone me-2 text-success"></i>
                                <small>{{ $class->teacher->phone }}</small>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
