@extends('layouts.app')

@section('title', 'My Schedule')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i> My Schedule</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Schedule</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('student.schedule.export', ['view' => $view, 'date' => $date, 'format' => 'pdf']) }}"
               class="btn btn-outline-primary">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('student.schedule.print', ['view' => $view, 'date' => $date]) }}"
               target="_blank"
               class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('student.schedule.icalendar') }}"
               class="btn btn-outline-info">
                <i class="fas fa-calendar-plus me-1"></i> Sync Calendar
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-chalkboard text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Classes</h6>
                            <h4 class="mb-0">{{ $stats['total_classes'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-clock text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Hours</h6>
                            <h4 class="mb-0">{{ $stats['total_hours'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    @if(isset($stats['upcoming_class']))
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded p-3">
                                    <i class="fas fa-arrow-right text-warning fa-2x"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Next Class</h6>
                                <h5 class="mb-0">{{ $stats['upcoming_class']['class_name'] ?? '' }}</h5>
                                <small class="text-muted">
                                    {{ isset($stats['upcoming_class']['full_date']) ? $stats['upcoming_class']['full_date']->format('d M, h:i A') : '' }}
                                </small>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-2">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">No upcoming classes</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- View Controls & Date Navigation -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <!-- View Type Selector -->
                <div class="col-md-4">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('student.schedule.index', ['view' => 'daily', 'date' => $date]) }}"
                           class="btn btn-{{ $view === 'daily' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-calendar-day me-1"></i> Daily
                        </a>
                        <a href="{{ route('student.schedule.index', ['view' => 'weekly', 'date' => $date]) }}"
                           class="btn btn-{{ $view === 'weekly' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-calendar-week me-1"></i> Weekly
                        </a>
                        <a href="{{ route('student.schedule.index', ['view' => 'monthly', 'date' => $date]) }}"
                           class="btn btn-{{ $view === 'monthly' ? 'primary' : 'outline-primary' }}">
                            <i class="fas fa-calendar me-1"></i> Monthly
                        </a>
                    </div>
                </div>

                <!-- Date Navigation -->
                <div class="col-md-5">
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" onclick="navigateDate('prev')">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <input type="date"
                               id="dateSelector"
                               class="form-control text-center"
                               value="{{ $date }}"
                               onchange="navigateToDate(this.value)">
                        <button type="button" class="btn btn-outline-secondary" onclick="navigateDate('next')">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Today Button -->
                <div class="col-md-3">
                    <a href="{{ route('student.schedule.index', ['view' => $view, 'date' => now()->format('Y-m-d')]) }}"
                       class="btn btn-outline-primary w-100">
                        <i class="fas fa-calendar-day me-1"></i> Today
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Timetable Display -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($view === 'daily')
                @include('student.schedule.partials.daily-view', ['timetableData' => $timetableData])
            @elseif($view === 'weekly')
                @include('student.schedule.partials.weekly-view', ['timetableData' => $timetableData])
            @else
                @include('student.schedule.partials.monthly-view', ['timetableData' => $timetableData])
            @endif
        </div>
    </div>

    <!-- Enrolled Classes Reference -->
    @if($enrolledClasses->count() > 0)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> My Enrolled Classes</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($enrolledClasses as $class)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <h6 class="mb-2">{{ $class->name }}</h6>
                                    <p class="text-muted mb-2 small">
                                        <i class="fas fa-book me-1"></i> {{ $class->subject->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-muted mb-2 small">
                                        <i class="fas fa-user me-1"></i> {{ $class->teacher->user->name ?? 'N/A' }}
                                    </p>
                                    @if($class->type)
                                        <p class="text-muted mb-0 small">
                                            <i class="fas fa-{{ $class->type === 'online' ? 'video' : 'map-marker-alt' }} me-1"></i>
                                            {{ ucfirst($class->type) }} Class
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function navigateDate(direction) {
    const currentDate = document.getElementById('dateSelector').value;
    const view = '{{ $view }}';
    const date = new Date(currentDate);

    if (direction === 'prev') {
        if (view === 'daily') {
            date.setDate(date.getDate() - 1);
        } else if (view === 'weekly') {
            date.setDate(date.getDate() - 7);
        } else {
            date.setMonth(date.getMonth() - 1);
        }
    } else {
        if (view === 'daily') {
            date.setDate(date.getDate() + 1);
        } else if (view === 'weekly') {
            date.setDate(date.getDate() + 7);
        } else {
            date.setMonth(date.getMonth() + 1);
        }
    }

    const newDate = date.toISOString().split('T')[0];
    window.location.href = `{{ route('student.schedule.index') }}?view=${view}&date=${newDate}`;
}

function navigateToDate(selectedDate) {
    const view = '{{ $view }}';
    window.location.href = `{{ route('student.schedule.index') }}?view=${view}&date=${selectedDate}`;
}
</script>
@endpush
@endsection
