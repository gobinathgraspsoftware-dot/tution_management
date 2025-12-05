@extends('layouts.app')

@section('title', 'Student Attendance Report')
@section('page-title', 'Student Attendance Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i> Student Attendance Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attendance.reports.index') }}">Reports</a></li>
                <li class="breadcrumb-item active">Student Report</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i> Generate Report</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.attendance.reports.student') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Select Student <span class="text-danger">*</span></label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }} ({{ $student->student_id }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Class (Optional)</label>
                <select name="class_id" class="form-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control"
                       value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control"
                       value="{{ request('date_to', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedStudent && $reportData)
<!-- Student Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-1">
                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
                    {{ substr($selectedStudent->user->name, 0, 1) }}
                </div>
            </div>
            <div class="col-md-4">
                <h4 class="mb-1">{{ $selectedStudent->user->name }}</h4>
                <span class="badge bg-secondary">{{ $selectedStudent->student_id }}</span>
                @if($selectedStudent->parent)
                    <p class="text-muted mb-0 mt-1">
                        <small><i class="fas fa-user-friends me-1"></i> Parent: {{ $selectedStudent->parent->user->name }}</small>
                    </p>
                @endif
            </div>
            <div class="col-md-5">
                <div class="row text-center">
                    <div class="col-3">
                        <h3 class="mb-0">{{ $reportData['summary']['total_sessions'] }}</h3>
                        <small class="text-muted">Total</small>
                    </div>
                    <div class="col-3">
                        <h3 class="mb-0 text-success">{{ $reportData['summary']['present'] }}</h3>
                        <small class="text-muted">Present</small>
                    </div>
                    <div class="col-3">
                        <h3 class="mb-0 text-danger">{{ $reportData['summary']['absent'] }}</h3>
                        <small class="text-muted">Absent</small>
                    </div>
                    <div class="col-3">
                        <h3 class="mb-0 {{ $reportData['summary']['percentage'] >= 75 ? 'text-success' : 'text-warning' }}">
                            {{ $reportData['summary']['percentage'] }}%
                        </h3>
                        <small class="text-muted">Rate</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2 text-end">
                <div class="btn-group-vertical">
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.attendance.reports.export-student', [
                                    'student_id' => $selectedStudent->id,
                                    'date_from' => request('date_from'),
                                    'date_to' => request('date_to'),
                                    'class_id' => request('class_id'),
                                    'format' => 'csv'
                                ]) }}">
                                    <i class="fas fa-file-csv me-2"></i> Export CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.attendance.reports.export-student', [
                                    'student_id' => $selectedStudent->id,
                                    'date_from' => request('date_from'),
                                    'date_to' => request('date_to'),
                                    'class_id' => request('class_id'),
                                    'format' => 'xlsx'
                                ]) }}">
                                    <i class="fas fa-file-excel me-2"></i> Export Excel
                                </a>
                            </li>
                        </ul>
                    </div>
                    @if($selectedStudent->parent)
                    <form action="{{ route('admin.attendance.reports.email-parent') }}" method="POST" class="mt-2">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-1"></i> Email Report
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Class-wise Breakdown -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i> Class-wise Breakdown</h5>
            </div>
            <div class="card-body p-0">
                @if($reportData['by_class']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Total</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['by_class'] as $classData)
                            <tr>
                                <td><strong>{{ $classData['class_name'] }}</strong></td>
                                <td>{{ $classData['subject'] }}</td>
                                <td>{{ $classData['total'] }}</td>
                                <td class="text-success">{{ $classData['present'] }}</td>
                                <td class="text-danger">{{ $classData['absent'] }}</td>
                                <td>
                                    <span class="badge {{ $classData['percentage'] >= 75 ? 'bg-success' : 'bg-warning' }}">
                                        {{ $classData['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No class data available.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Monthly Breakdown</h5>
            </div>
            <div class="card-body p-0">
                @if($reportData['by_month']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Total</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['by_month'] as $monthData)
                            <tr>
                                <td><strong>{{ $monthData['month'] }}</strong></td>
                                <td>{{ $monthData['total'] }}</td>
                                <td class="text-success">{{ $monthData['present'] }}</td>
                                <td class="text-danger">{{ $monthData['absent'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                                            <div class="progress-bar {{ $monthData['percentage'] >= 75 ? 'bg-success' : 'bg-warning' }}"
                                                 style="width: {{ $monthData['percentage'] }}%"></div>
                                        </div>
                                        <span class="small">{{ $monthData['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No monthly data available.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Detailed Records -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Detailed Attendance Records</h5>
        <span class="badge bg-secondary">{{ $reportData['records']->count() }} records</span>
    </div>
    <div class="card-body p-0">
        @if($reportData['records']->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Check-in</th>
                        <th>Remarks</th>
                        <th>Notified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['records'] as $record)
                    <tr>
                        <td>{{ $record->classSession->session_date->format('d/m/Y') }}</td>
                        <td>{{ $record->classSession->class->name ?? 'N/A' }}</td>
                        <td>{{ $record->classSession->class->subject->name ?? 'N/A' }}</td>
                        <td>{{ $record->classSession->start_time->format('H:i') }}</td>
                        <td>
                            @switch($record->status)
                                @case('present')
                                    <span class="badge bg-success">Present</span>
                                    @break
                                @case('absent')
                                    <span class="badge bg-danger">Absent</span>
                                    @break
                                @case('late')
                                    <span class="badge bg-warning">Late</span>
                                    @break
                                @case('excused')
                                    <span class="badge bg-info">Excused</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $record->check_in_time ? $record->check_in_time->format('H:i') : '-' }}</td>
                        <td>{{ $record->remarks ?? '-' }}</td>
                        <td>
                            @if($record->parent_notified)
                                <span class="text-success"><i class="fas fa-check-circle"></i></span>
                            @else
                                <span class="text-muted"><i class="fas fa-times-circle"></i></span>
                            @endif
                        </td>
                        <td>
                            @if(!$record->parent_notified && $selectedStudent->parent)
                            <form action="{{ route('admin.attendance.reports.resend-notification', $record->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Resend Notification">
                                    <i class="fas fa-bell"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">No attendance records found for the selected period.</p>
        </div>
        @endif
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-search fa-4x text-muted mb-3"></i>
        <h4>Select a Student</h4>
        <p class="text-muted">Choose a student from the dropdown above to generate their attendance report.</p>
    </div>
</div>
@endif
@endsection
