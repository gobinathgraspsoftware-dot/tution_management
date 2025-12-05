@extends('layouts.app')

@section('title', 'Timetable Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Timetable Management</h1>
            <p class="text-muted mb-0">Manage and view class schedules</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('timetable.export', ['format' => 'pdf', 'view' => $view, 'date' => $date]) }}">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="{{ route('timetable.export', ['format' => 'csv', 'view' => $view, 'date' => $date]) }}">Export as CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('timetable.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- View Type -->
                    <div class="col-md-3">
                        <label class="form-label">View Type</label>
                        <select name="view" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="daily" {{ $view == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $view == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $view == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>

                    <!-- Date Selector -->
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="document.getElementById('filterForm').submit()">
                    </div>

                    <!-- Class Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Filter by Class</label>
                        <select name="class_id" class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Teacher Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Filter by Teacher</label>
                        <select name="teacher_id" class="form-select" id="teacherFilter">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Quick Navigation -->
                <div class="mt-3 d-flex gap-2">
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
            </form>
        </div>
    </div>

    <!-- Timetable Display -->
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

.print-hide {
    display: block;
}

@media print {
    .print-hide {
        display: none !important;
    }
    .card {
        border: none;
        box-shadow: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Navigate date based on view type
function navigateDate(direction) {
    const dateInput = document.querySelector('input[name="date"]');
    const viewType = document.querySelector('select[name="view"]').value;
    const currentDate = new Date(dateInput.value);

    if (direction === 'today') {
        dateInput.value = new Date().toISOString().split('T')[0];
    } else {
        let daysToAdd = 0;

        if (viewType === 'daily') {
            daysToAdd = direction === 'next' ? 1 : -1;
        } else if (viewType === 'weekly') {
            daysToAdd = direction === 'next' ? 7 : -7;
        } else if (viewType === 'monthly') {
            const newDate = new Date(currentDate);
            newDate.setMonth(newDate.getMonth() + (direction === 'next' ? 1 : -1));
            dateInput.value = newDate.toISOString().split('T')[0];
            document.getElementById('filterForm').submit();
            return;
        }

        currentDate.setDate(currentDate.getDate() + daysToAdd);
        dateInput.value = currentDate.toISOString().split('T')[0];
    }

    document.getElementById('filterForm').submit();
}

// Filter by class (AJAX)
document.getElementById('classFilter').addEventListener('change', function() {
    if (this.value) {
        filterTimetable('class', this.value);
    } else {
        document.getElementById('filterForm').submit();
    }
});

// Filter by teacher (AJAX)
document.getElementById('teacherFilter').addEventListener('change', function() {
    if (this.value) {
        filterTimetable('teacher', this.value);
    } else {
        document.getElementById('filterForm').submit();
    }
});

function filterTimetable(type, id) {
    const view = document.querySelector('select[name="view"]').value;
    const date = document.querySelector('input[name="date"]').value;
    const url = type === 'class'
        ? '{{ route("timetable.filter.class") }}'
        : '{{ route("timetable.filter.teacher") }}';

    fetch(url + '?view=' + view + '&date=' + date + '&' + type + '_id=' + id)
        .then(response => response.json())
        .then(data => {
            // Update timetable display with filtered data
            console.log('Filtered data:', data);
            // You can implement custom rendering logic here
        })
        .catch(error => {
            console.error('Error filtering timetable:', error);
        });
}
</script>
@endpush
