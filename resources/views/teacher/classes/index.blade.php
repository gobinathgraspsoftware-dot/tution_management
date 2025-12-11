@extends('layouts.app')

@section('title', 'My Classes')
@section('page-title', 'My Classes')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-school me-2"></i> My Classes</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Classes</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_classes'] }}</h3>
                <p class="text-muted mb-0">Total Classes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['active_classes'] }}</h3>
                <p class="text-muted mb-0">Active Classes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $stats['classes_today'] }}</h3>
                <p class="text-muted mb-0">Classes Today</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('teacher.classes.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search class name or code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Classes List -->
<div class="row">
    @forelse($classes as $class)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">{{ $class->code }}</span>
                    @if($class->status == 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($class->status == 'completed')
                        <span class="badge bg-info">Completed</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($class->status) }}</span>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $class->name }}</h5>
                    <p class="card-text">
                        <i class="fas fa-book text-primary me-2"></i>
                        {{ $class->subject->name ?? 'N/A' }}
                    </p>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Students</span>
                            <span class="badge bg-info">
                                {{ $class->enrollments->where('status', 'active')->count() }} / {{ $class->capacity }}
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $enrollmentPercentage = $class->capacity > 0
                                    ? ($class->enrollments->where('status', 'active')->count() / $class->capacity) * 100
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-{{ $enrollmentPercentage >= 90 ? 'danger' : ($enrollmentPercentage >= 70 ? 'warning' : 'success') }}"
                                 style="width: {{ $enrollmentPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Schedule Preview -->
                    @if($class->schedules->count() > 0)
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Schedule:</small>
                            @foreach($class->schedules->take(2) as $schedule)
                                <small class="d-block">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ ucfirst($schedule->day_of_week) }}:
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </small>
                            @endforeach
                            @if($class->schedules->count() > 2)
                                <small class="text-muted">+{{ $class->schedules->count() - 2 }} more</small>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $class->type == 'online' ? 'Online Class' : ($class->location ?? 'Location TBD') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100">
                        <a href="{{ route('teacher.classes.show', $class) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-1"></i> View
                        </a>
                        <a href="{{ route('teacher.classes.students', $class) }}" class="btn btn-outline-info">
                            <i class="fas fa-users me-1"></i> Students
                        </a>
                        <a href="{{ route('teacher.classes.schedule', $class) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-calendar me-1"></i> Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-school fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Classes Found</h4>
                    <p class="text-muted">You don't have any classes assigned yet or no classes match your filter criteria.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($classes->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $classes->links() }}
    </div>
@endif
@endsection

@push('styles')
<style>
.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 15px;
}
.stat-details h3 {
    font-size: 1.5rem;
    font-weight: 700;
}
</style>
@endpush
