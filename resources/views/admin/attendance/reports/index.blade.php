@extends('layouts.app')

@section('title', 'Attendance Reports')
@section('page-title', 'Attendance Reports Dashboard')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chart-bar me-2"></i> Attendance Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.attendance.reports.student') }}" class="btn btn-outline-primary">
            <i class="fas fa-user me-1"></i> Student Report
        </a>
        <a href="{{ route('admin.attendance.reports.class') }}" class="btn btn-outline-primary">
            <i class="fas fa-chalkboard me-1"></i> Class Report
        </a>
        <a href="{{ route('admin.attendance.reports.low-attendance') }}" class="btn btn-outline-warning">
            <i class="fas fa-exclamation-triangle me-1"></i> Low Attendance
        </a>
    </div>
</div>

<!-- Period Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.attendance.reports.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Report Period</label>
                <select name="period" class="form-select" onchange="this.form.submit()">
                    <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                    <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                </select>
            </div>
            <div class="col-md-6">
                <span class="text-muted">
                    <i class="fas fa-calendar me-1"></i>
                    {{ \Carbon\Carbon::parse($stats['date_range']['start'])->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($stats['date_range']['end'])->format('d M Y') }}
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Sessions</h6>
                    <h2 class="mb-0">{{ $stats['sessions']['total'] }}</h2>
                    <small class="text-success">{{ $stats['sessions']['completed'] }} completed</small>
                </div>
                <div class="stat-icon bg-primary-light">
                    <i class="fas fa-calendar-check text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Student Attendance</h6>
                    <h2 class="mb-0">{{ $stats['student']['percentage'] }}%</h2>
                    <small class="text-muted">{{ $stats['student']['present'] }}/{{ $stats['student']['total'] }} present</small>
                </div>
                <div class="stat-icon bg-success-light">
                    <i class="fas fa-user-check text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Teacher Attendance</h6>
                    <h2 class="mb-0">{{ $stats['teacher']['percentage'] }}%</h2>
                    <small class="text-muted">{{ $stats['teacher']['present'] }}/{{ $stats['teacher']['total'] }} present</small>
                </div>
                <div class="stat-icon bg-info-light">
                    <i class="fas fa-chalkboard-teacher text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Notifications Sent</h6>
                    <h2 class="mb-0">{{ $stats['student']['notified'] }}</h2>
                    <small class="text-muted">Parent notifications</small>
                </div>
                <div class="stat-icon bg-warning-light">
                    <i class="fas fa-bell text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Attendance Trends Chart -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Attendance Trends</h5>
            </div>
            <div class="card-body">
                <canvas id="attendanceTrendsChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Student Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Present</span>
                        <span class="text-success">{{ $stats['student']['present'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['student']['total'] > 0 ? ($stats['student']['present'] / $stats['student']['total'] * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Absent</span>
                        <span class="text-danger">{{ $stats['student']['absent'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" style="width: {{ $stats['student']['total'] > 0 ? ($stats['student']['absent'] / $stats['student']['total'] * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Late</span>
                        <span class="text-warning">{{ $stats['student']['late'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: {{ $stats['student']['total'] > 0 ? ($stats['student']['late'] / $stats['student']['total'] * 100) : 0 }}%"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Excused</span>
                        <span class="text-info">{{ $stats['student']['excused'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: {{ $stats['student']['total'] > 0 ? ($stats['student']['excused'] / $stats['student']['total'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Low Attendance Students -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Low Attendance Students</h5>
                <a href="{{ route('admin.attendance.reports.low-attendance') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                @if($lowAttendanceStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Attendance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowAttendanceStudents as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->student->user->name ?? 'N/A' }}</strong>
                                    <br><small class="text-muted">{{ $item->student->student_id ?? '' }}</small>
                                </td>
                                <td>{{ $item->class->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $item->attendance_percentage < 60 ? 'bg-danger' : 'bg-warning' }}">
                                        {{ $item->attendance_percentage }}%
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.attendance.reports.send-alert') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $item->student_id }}">
                                        <input type="hidden" name="class_id" value="{{ $item->class_id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Send Alert">
                                            <i class="fas fa-bell"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <p class="text-muted mb-0">All students have good attendance!</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Class Comparison -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Class Attendance Comparison</h5>
            </div>
            <div class="card-body p-0">
                @if(count($classComparison) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Students</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($classComparison, 0, 8) as $class)
                            <tr>
                                <td><strong>{{ $class['name'] }}</strong></td>
                                <td>{{ $class['subject'] }}</td>
                                <td>{{ $class['student_count'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar {{ $class['percentage'] >= 75 ? 'bg-success' : ($class['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width: {{ $class['percentage'] }}%"></div>
                                        </div>
                                        <span class="small">{{ $class['percentage'] }}%</span>
                                    </div>
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
</div>

<!-- Recent Alerts -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Alerts</h5>
        <a href="{{ route('admin.attendance.reports.low-attendance') }}" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
        @if($recentAlerts->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Attendance %</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAlerts as $alert)
                    <tr>
                        <td>{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $alert->student->user->name ?? 'N/A' }}</td>
                        <td>{{ $alert->class->name ?? 'N/A' }}</td>
                        <td><span class="badge bg-danger">{{ $alert->attendance_percentage }}%</span></td>
                        <td>
                            <span class="badge bg-{{ $alert->status_badge }}">
                                {{ ucfirst($alert->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <p class="text-muted mb-0">No recent alerts.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-light { background-color: rgba(25, 135, 84, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const trendsData = @json($attendanceTrends);

    const ctx = document.getElementById('attendanceTrendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.map(d => d.date),
            datasets: [{
                label: 'Attendance %',
                data: trendsData.map(d => d.percentage),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Total Records',
                data: trendsData.map(d => d.total),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: false,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Attendance %'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Records'
                    }
                }
            }
        }
    });
});
</script>
@endpush
