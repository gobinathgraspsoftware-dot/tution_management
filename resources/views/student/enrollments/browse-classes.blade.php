@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Browse Available Classes</h4>
                <div class="page-title-right">
                    <a href="{{ route('student.enrollments.my-enrollments') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to My Enrollments
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.enrollments.browse-classes') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
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

                            <div class="col-md-4">
                                <label class="form-label">Day of Week</label>
                                <select name="day" class="form-select">
                                    <option value="">All Days</option>
                                    <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Teacher</label>
                                <select name="teacher_id" class="form-select">
                                    <option value="">All Teachers</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->user->name ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Classes Grid --}}
    <div class="row">
        @forelse($classes as $class)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $class->name }}</h5>
                                @if($class->subject)
                                    <span class="badge bg-soft-primary text-primary">
                                        {{ $class->subject->name }}
                                    </span>
                                @endif
                            </div>
                            <span class="badge bg-soft-info text-info">
                                {{ ucfirst($class->type) }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-chalkboard-teacher text-muted me-2"></i>
                                <span>{{ $class->teacher->user->name ?? 'N/A' }}</span>
                            </div>

                            @if($class->schedules->isNotEmpty())
                                <div class="mb-2">
                                    <i class="fas fa-clock text-muted me-2"></i>
                                    <small>
                                        @foreach($class->schedules->take(2) as $schedule)
                                            {{ ucfirst($schedule->day_of_week) }}: {{ date('g:i A', strtotime($schedule->start_time)) }}
                                            @if(!$loop->last), @endif
                                        @endforeach
                                        @if($class->schedules->count() > 2)
                                            <br><span class="ms-4">+{{ $class->schedules->count() - 2 }} more</span>
                                        @endif
                                    </small>
                                </div>
                            @endif

                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-muted me-2"></i>
                                <span>{{ $class->current_enrollment }}/{{ $class->capacity }} students</span>
                            </div>
                        </div>

                        @php
                            $availableSeats = $class->capacity - $class->current_enrollment;
                            $capacityPercent = ($class->current_enrollment / $class->capacity) * 100;
                            $progressColor = $capacityPercent >= 90 ? 'danger' : ($capacityPercent >= 70 ? 'warning' : 'success');
                        @endphp

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Capacity</small>
                                <small class="text-muted">{{ $availableSeats }} seats available</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $progressColor }}"
                                     role="progressbar"
                                     style="width: {{ $capacityPercent }}%;"
                                     aria-valuenow="{{ $capacityPercent }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0 text-primary">RM {{ number_format($class->monthly_fee, 2) }}</h4>
                                <small class="text-muted">per month</small>
                            </div>
                            <a href="{{ route('student.enrollments.enroll-class', $class) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Enroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="avatar-lg mx-auto mb-4">
                            <div class="avatar-title bg-soft-warning text-warning rounded-circle fs-1">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <h5>No Classes Found</h5>
                        <p class="text-muted mb-4">No available classes match your search criteria. Try adjusting your filters or browse our packages instead.</p>
                        <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-success">
                            <i class="fas fa-box me-1"></i> Browse Packages
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
