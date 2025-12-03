@extends('layouts.app')

@section('title', 'My Timetable')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Teaching Schedule</h1>
            <p class="text-muted mb-0">View your class timetable</p>
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
                    <li><a class="dropdown-item" href="{{ route('admin.timetable.export', ['format' => 'pdf', 'view' => $view, 'date' => $date]) }}">Export as PDF</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.timetable.export', ['format' => 'csv', 'view' => $view, 'date' => $date]) }}">Export as CSV</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- View Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher.timetable.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">View Type</label>
                        <select name="view" class="form-select" onchange="this.form.submit()">
                            <option value="daily" {{ $view == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $view == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $view == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="navigateDate('prev')">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary flex-grow-1" onclick="navigateDate('today')">
                                Today
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="navigateDate('next')">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Timetable -->
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

@push('scripts')
<script>
function navigateDate(direction) {
    const dateInput = document.querySelector('input[name="date"]');
    const viewType = document.querySelector('select[name="view"]').value;
    const currentDate = new Date(dateInput.value);

    if (direction === 'today') {
        dateInput.value = new Date().toISOString().split('T')[0];
    } else {
        let daysToAdd = viewType === 'daily' ? (direction === 'next' ? 1 : -1) : (direction === 'next' ? 7 : -7);
        currentDate.setDate(currentDate.getDate() + daysToAdd);
        dateInput.value = currentDate.toISOString().split('T')[0];
    }
    document.getElementById('filterForm').submit();
}
</script>
@endpush
