@extends('layouts.app')

@section('title', 'Browse Classes')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-search"></i> Browse Available Classes
        </h1>
        <div>
            <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-info">
                <i class="fas fa-box"></i> Browse Packages
            </a>
            <a href="{{ route('student.enrollments.my-enrollments') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> My Enrollments
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Classes</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('student.enrollments.browse-classes') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="subject_id">Subject</label>
                        <select class="form-control" id="subject_id" name="subject_id">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="day">Day of Week</label>
                        <select class="form-control" id="day" name="day">
                            <option value="">All Days</option>
                            <option value="Monday" {{ request('day') == 'Monday' ? 'selected' : '' }}>Monday</option>
                            <option value="Tuesday" {{ request('day') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                            <option value="Wednesday" {{ request('day') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                            <option value="Thursday" {{ request('day') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                            <option value="Friday" {{ request('day') == 'Friday' ? 'selected' : '' }}>Friday</option>
                            <option value="Saturday" {{ request('day') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                            <option value="Sunday" {{ request('day') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="teacher_id">Teacher</label>
                        <select class="form-control" id="teacher_id" name="teacher_id">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Classes Grid -->
    <div class="row">
        @forelse($classes as $class)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ $class->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge badge-info">{{ $class->subject->name }}</span>
                    </div>

                    <div class="mb-2">
                        <strong><i class="fas fa-chalkboard-teacher"></i> Teacher:</strong>
                        {{ $class->teacher->user->name }}
                    </div>

                    @if($class->description)
                    <div class="mb-2">
                        <strong><i class="fas fa-info-circle"></i> Description:</strong>
                        <p class="small mb-0">{{ Str::limit($class->description, 100) }}</p>
                    </div>
                    @endif

                    <div class="mb-2">
                        <strong><i class="fas fa-calendar-alt"></i> Schedule:</strong>
                        @if($class->schedules->isNotEmpty())
                            @foreach($class->schedules as $schedule)
                                <div class="small">
                                    {{ $schedule->day_of_week }}:
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                </div>
                            @endforeach
                        @else
                            <div class="small text-muted">Schedule to be announced</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-money-bill-wave"></i> Monthly Fee:</strong>
                        <span class="h5 text-success mb-0">RM {{ number_format($class->monthly_fee, 2) }}</span>
                    </div>

                    @if($class->max_students)
                    <div class="mb-2">
                        <strong><i class="fas fa-users"></i> Capacity:</strong>
                        {{ $class->enrollments()->active()->count() }} / {{ $class->max_students }} students
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ ($class->enrollments()->active()->count() / $class->max_students) * 100 }}%">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('student.enrollments.enroll-class', $class) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Enroll Now
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No classes available at the moment. Please check back later or browse our packages.
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
