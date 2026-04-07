@extends('layouts.app')

@section('title', 'Timetable Management')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Timetable Management</h1>
            <p class="text-muted mb-0">Manage and view class schedules</p>
        </div>
        <div>
            @php
                $filterParams = array_filter([
                    'view'        => $view,
                    'date'        => $date,
                    'class_id'    => request('class_id'),
                    'teacher_id'  => request('teacher_id'),
                    'grade_level' => request('grade_level'),
                ]);
            @endphp
            <a href="{{ route('timetable.print', $filterParams) }}"
               target="_blank" class="btn btn-outline-primary me-2">
                <i class="fas fa-print"></i> Print
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('timetable.export', array_merge($filterParams, ['format' => 'pdf'])) }}">
                            Export as PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('timetable.export', array_merge($filterParams, ['format' => 'csv'])) }}">
                            Export as CSV
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('timetable.index') }}" id="filterForm">
                <div class="row g-3">
                    {{-- View Type --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">View Type</label>
                        <select name="view" class="form-select" onchange="submitFilter()">
                            <option value="daily"   {{ $view == 'daily'   ? 'selected' : '' }}>Daily</option>
                            <option value="weekly"  {{ $view == 'weekly'  ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $view == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Date</label>
                        <input type="date" name="date" class="form-control"
                               value="{{ $date }}" onchange="submitFilter()">
                    </div>

                    {{-- Class Filter --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Class</label>
                        <select name="class_id" class="form-select" onchange="submitFilter()">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} - {{ $class->subject->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Teacher Filter --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Filter by Teacher</label>
                        <select name="teacher_id" class="form-select" onchange="submitFilter()">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}"
                                    {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Grade Level Filter --}}
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Grade Level</label>
                        <select name="grade_level" class="form-select" onchange="submitFilter()">
                            <option value="">All Grades</option>
                            @foreach($gradeLevels as $grade)
                                <option value="{{ $grade }}"
                                    {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                    {{ $grade }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Navigation + Active Filters --}}
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    {{-- Quick Navigation --}}
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigateDate('prev')">
                            <i class="fas fa-chevron-left"></i> Previous
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="navigateDate('today')">
                            <i class="fas fa-calendar-day"></i> Today
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigateDate('next')">
                            Next <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    {{-- Active Filters Summary --}}
                    @if(request('class_id') || request('teacher_id') || request('grade_level'))
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small"><i class="fas fa-filter"></i> Active:</span>

                            @if(request('class_id'))
                                <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                                    <i class="fas fa-chalkboard"></i>
                                    {{ $classes->firstWhere('id', request('class_id'))->name ?? 'Class' }}
                                    <a href="javascript:void(0)" onclick="clearSingleFilter('class_id')"
                                       class="text-white ms-1" style="text-decoration:none; font-size:0.85rem;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            @if(request('teacher_id'))
                                <span class="badge bg-success d-inline-flex align-items-center gap-1">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    {{ $teachers->firstWhere('id', request('teacher_id'))->user->name ?? 'Teacher' }}
                                    <a href="javascript:void(0)" onclick="clearSingleFilter('teacher_id')"
                                       class="text-white ms-1" style="text-decoration:none; font-size:0.85rem;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            @if(request('grade_level'))
                                <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                                    <i class="fas fa-graduation-cap"></i>
                                    {{ request('grade_level') }}
                                    <a href="javascript:void(0)" onclick="clearSingleFilter('grade_level')"
                                       class="text-dark ms-1" style="text-decoration:none; font-size:0.85rem;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            @endif

                            <a href="{{ route('timetable.index', ['view' => $view, 'date' => $date]) }}"
                               class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times"></i> Clear All
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Timetable Display --}}
    <div class="card">
        <div class="card-body">
            @if($view == 'daily')
                @include('admin.timetable._daily', ['timetableData' => $timetableData])
            @elseif($view == 'weekly')
                @include('admin.timetable._weekly', ['timetableData' => $timetableData])
            @else
                @include('admin.timetable._monthly', ['timetableData' => $timetableData])
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.time-slot {
    min-height: 100px;
    border: 1px solid #e9ecef;
    padding: 10px;
    border-radius: 6px;
    background: #fff;
    transition: all 0.3s ease;
}
.time-slot:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.class-card {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 8px;
    border-left: 4px solid;
    cursor: pointer;
    transition: all 0.2s ease;
}
.class-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateX(4px);
}
.class-type-badge {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 12px;
}
@media print {
    .print-hide { display: none !important; }
    .card { border: none; box-shadow: none; }
}
</style>
@endpush

@push('scripts')
<script>
/**
 * Submit the filter form.
 * Strips empty values from URL to keep it clean.
 */
function submitFilter() {
    var form = document.getElementById('filterForm');

    // Disable empty selects so they don't appear as ?class_id=&teacher_id= in URL
    var selects = form.querySelectorAll('select, input');
    selects.forEach(function(el) {
        if (el.value === '' || el.value === null) {
            el.setAttribute('disabled', 'disabled');
        }
    });

    form.submit();
}

/**
 * Navigate date based on current view type.
 * Preserves all active filters.
 */
function navigateDate(direction) {
    var dateInput = document.querySelector('input[name="date"]');
    var viewType  = document.querySelector('select[name="view"]').value;
    var currentDate = new Date(dateInput.value);

    if (direction === 'today') {
        dateInput.value = new Date().toISOString().split('T')[0];
    } else {
        if (viewType === 'daily') {
            currentDate.setDate(currentDate.getDate() + (direction === 'next' ? 1 : -1));
        } else if (viewType === 'weekly') {
            currentDate.setDate(currentDate.getDate() + (direction === 'next' ? 7 : -7));
        } else if (viewType === 'monthly') {
            currentDate.setMonth(currentDate.getMonth() + (direction === 'next' ? 1 : -1));
        }
        dateInput.value = currentDate.toISOString().split('T')[0];
    }

    submitFilter();
}

/**
 * Clear a single filter while keeping all others intact.
 */
function clearSingleFilter(fieldName) {
    var el = document.querySelector('[name="' + fieldName + '"]');
    if (el) el.value = '';
    submitFilter();
}
</script>
@endpush
