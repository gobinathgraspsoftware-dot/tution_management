@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards - Row 1 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $total_students }}</h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $pending_approvals }}</h3>
                <p class="text-muted mb-0">Pending Approvals</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $total_teachers }}</h3>
                <p class="text-muted mb-0">Active Teachers</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($total_revenue_month, 2) }}</h3>
                <p class="text-muted mb-0">Revenue (This Month)</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - Row 2: Online/Offline Counts + Active Classes + Pending Payments -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e0f7fa; color: #00bcd4;">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $online_students_count }}</h3>
                <p class="text-muted mb-0">Online Students</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fce4ec; color: #e91e63;">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $offline_students_count }}</h3>
                <p class="text-muted mb-0">Offline Students</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e1f5fe; color: #03a9f4;">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $active_classes }}</h3>
                <p class="text-muted mb-0">Active Classes</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $pending_payments }}</h3>
                <p class="text-muted mb-0">Pending Payments</p>
            </div>
        </div>
    </div>
</div>

<!-- Online & Offline Students Lists -->
<div class="row">
    <!-- Online Students -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-globe me-2 text-info"></i> Online Students
                    <span class="badge bg-info ms-2">{{ $online_students_count }}</span>
                </span>
                @if(Route::has('admin.students.index'))
                <a href="{{ route('admin.students.index', ['registration_type' => 'online']) }}" class="btn btn-sm btn-outline-info">View All</a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($online_students as $student)
                    <div class="d-flex align-items-center px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="user-avatar me-3" style="background: linear-gradient(135deg, #00bcd4 0%, #0097a7 100%);">
                            {{ substr($student->user->name ?? 'N', 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $student->user->name ?? 'Unknown Student' }}</h6>
                            <small class="text-muted">{{ $student->student_id }}</small>
                            @if($student->user && $student->user->phone)
                                <small class="text-muted"> &bull; {{ $student->user->phone }}</small>
                            @endif
                            <div>
                                @if($student->enrollments->count() > 0)
                                    @foreach($student->enrollments->take(2) as $enrollment)
                                        <span class="badge bg-light text-dark border me-1" style="font-size: 0.7rem;">
                                            @if($enrollment->package)
                                                {{ $enrollment->package->name }}
                                            @elseif($enrollment->class)
                                                {{ $enrollment->class->subject->name ?? $enrollment->class->name }}
                                            @endif
                                        </span>
                                    @endforeach
                                    @if($student->enrollments->count() > 2)
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">+{{ $student->enrollments->count() - 2 }} more</span>
                                    @endif
                                @else
                                    <small class="text-muted fst-italic">No active enrollments</small>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-info">Online</span>
                            <br>
                            <small class="text-muted">{{ $student->registration_date ? $student->registration_date->format('d M Y') : 'N/A' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-globe fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No online students found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Offline Students -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-building me-2 text-danger"></i> Offline Students
                    <span class="badge bg-danger ms-2">{{ $offline_students_count }}</span>
                </span>
                @if(Route::has('admin.students.index'))
                <a href="{{ route('admin.students.index', ['registration_type' => 'offline']) }}" class="btn btn-sm btn-outline-danger">View All</a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($offline_students as $student)
                    <div class="d-flex align-items-center px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="user-avatar me-3" style="background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);">
                            {{ substr($student->user->name ?? 'N', 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $student->user->name ?? 'Unknown Student' }}</h6>
                            <small class="text-muted">{{ $student->student_id }}</small>
                            @if($student->user && $student->user->phone)
                                <small class="text-muted"> &bull; {{ $student->user->phone }}</small>
                            @endif
                            <div>
                                @if($student->enrollments->count() > 0)
                                    @foreach($student->enrollments->take(2) as $enrollment)
                                        <span class="badge bg-light text-dark border me-1" style="font-size: 0.7rem;">
                                            @if($enrollment->package)
                                                {{ $enrollment->package->name }}
                                            @elseif($enrollment->class)
                                                {{ $enrollment->class->subject->name ?? $enrollment->class->name }}
                                            @endif
                                        </span>
                                    @endforeach
                                    @if($student->enrollments->count() > 2)
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">+{{ $student->enrollments->count() - 2 }} more</span>
                                    @endif
                                @else
                                    <small class="text-muted fst-italic">No active enrollments</small>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary">Offline</span>
                            <br>
                            <small class="text-muted">{{ $student->registration_date ? $student->registration_date->format('d M Y') : 'N/A' }}</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No offline students found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
