@extends('layouts.app')

@section('title', 'Class Attendance Report')
@section('page-title', 'Class Attendance Report')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chalkboard me-2"></i> Class Attendance Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attendance.reports.index') }}">Reports</a></li>
                <li class="breadcrumb-item active">Class Report</li>
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
        <form action="{{ route('admin.attendance.reports.class') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Select Class <span class="text-danger">*</span></label>
                <select name="class_id" class="form-select" required>
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} - {{ $class->subject->name ?? 'No Subject' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control"
                       value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
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

@if($selectedClass && $reportData)
<!-- Class Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-5">
                <h4 class="mb-1">{{ $selectedClass->name }}</h4>
                <span class="badge bg-primary">{{ $selectedClass->code }}</span>
                <span class="badge bg-secondary">{{ $selectedClass->subject->name ?? 'N/A' }}</span>
                <p class="text-muted mb-0 mt-2">
                    <i class="fas fa-chalkboard-teacher me-1"></i>
                    Teacher: {{ $selectedClass->teacher->user->name ?? 'Not Assigned' }}
                </p>
                <p class="text-muted mb-0">
                    <i class="fas fa-users me-1"></i>
                    Enrolled: {{ $reportData['summary']['enrolled_students'] }} students
                </p>
            </div>
            <div class="col-md-5">
                <div class="row text-center">
                    <div class="col-3">
                        <h3 class="mb-0">{{ $reportData['summary']['total_sessions'] }}</h3>
                        <small class="text-muted">Sessions</small>
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
                <div class="dropdown">
                    <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.attendance.reports.export-class', [
                                'class_id' => $selectedClass->id,
                                'date_from' => request('date_from'),
                                'date_to' => request('date_to'),
                                'format' => 'csv'
                            ]) }}">
                                <i class="fas fa-file-csv me-2"></i> Export CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.attendance.reports.export-class', [
                                'class_id' => $selectedClass->id,
                                'date_from' => request('date_from'),
                                'date_to' => request('date_to'),
                                'format' => 'xlsx'
                            ]) }}">
                                <i class="fas fa-file-excel me-2"></i> Export Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Student-wise Attendance -->
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i> Student-wise Attendance</h5>
                @if($reportData['student_stats']->where('percentage', '<', 75)->count() > 0)
                <form action="{{ route('admin.attendance.reports.bulk-alerts') }}" method="POST">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                    @foreach($reportData['student_stats']->where('percentage', '<', 75) as $stat)
                        <input type="hidden" name="student_ids[]" value="{{ $stat['student_id'] }}">
                    @endforeach
                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Send alerts to all low attendance students?')">
                        <i class="fas fa-bell me-1"></i> Alert Low Attendance
                    </button>
                </form>
                @endif
            </div>
            <div class="card-body p-0">
                @if($reportData['student_stats']->count() > 0)
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Student</th>
                                <th>Total</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Rate</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['student_stats'] as $stat)
                            <tr class="{{ $stat['percentage'] < 75 ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $stat['student_name'] }}</strong>
                                </td>
                                <td>{{ $stat['total'] }}</td>
                                <td class="text-success">{{ $stat['present'] }}</td>
                                <td class="text-danger">{{ $stat['absent'] }}</td>
                                <td class="text-warning">{{ $stat['late'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                                            <div class="progress-bar {{ $stat['percentage'] >= 75 ? 'bg-success' : ($stat['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width: {{ $stat['percentage'] }}%"></div>
                                        </div>
                                        <span class="small">{{ $stat['percentage'] }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('admin.attendance.reports.student', ['student_id' => $stat['student_id'], 'class_id' => $selectedClass->id, 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
                                       class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($stat['percentage'] < 75)
                                    <form action="{{ route('admin.attendance.reports.send-alert') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $stat['student_id'] }}">
                                        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Send Alert">
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
                    <p class="text-muted mb-0">No student data available.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Session-wise Attendance -->
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Session-wise Attendance</h5>
            </div>
            <div class="card-body p-0">
                @if($reportData['session_stats']->count() > 0)
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Date</th>
                                <th>Topic</th>
                                <th>Present</th>
                                <th>Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['session_stats'] as $session)
                            <tr>
                                <td>
                                    <strong>{{ $session['date'] }}</strong>
                                    <br><small class="text-muted">{{ $session['time'] }}</small>
                                </td>
                                <td>{{ Str::limit($session['topic'] ?? 'No Topic', 20) }}</td>
                                <td>
                                    <span class="text-success">{{ $session['present'] }}</span>/{{ $session['total'] }}
                                </td>
                                <td>
                                    <span class="badge {{ $session['percentage'] >= 75 ? 'bg-success' : 'bg-warning' }}">
                                        {{ $session['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No session data available.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Attendance Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Attendance Distribution</h5>
    </div>
    <div class="card-body">
        <canvas id="attendanceChart" height="100"></canvas>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-chalkboard fa-4x text-muted mb-3"></i>
        <h4>Select a Class</h4>
        <p class="text-muted">Choose a class from the dropdown above to generate the attendance report.</p>
    </div>
</div>
@endif
@endsection

@if($selectedClass && $reportData)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Excused'],
            datasets: [{
                label: 'Count',
                data: [
                    {{ $reportData['summary']['present'] }},
                    {{ $reportData['summary']['absent'] }},
                    {{ $reportData['summary']['late'] }},
                    {{ $reportData['summary']['excused'] }}
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(23, 162, 184, 0.8)'
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(220, 53, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(23, 162, 184)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif
