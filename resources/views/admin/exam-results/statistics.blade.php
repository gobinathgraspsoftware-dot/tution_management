@extends('layouts.app')

@section('title', 'Exam Statistics - ' . $exam->name)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-chart-pie me-2"></i>Exam Statistics</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.exams.show', $exam) }}">{{ $exam->name }}</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if(Route::has('admin.exam-results.export'))
                <a href="{{ route('admin.exam-results.export', $exam) }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i> Export CSV
                </a>
            @endif
            <a href="{{ route('admin.exam-results.index', $exam) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Results
            </a>
        </div>
    </div>

    {{-- Exam Info Banner --}}
    <div class="alert alert-light border mb-4">
        <div class="row align-items-center">
            <div class="col-md-3"><strong>Exam:</strong> {{ $exam->name }}</div>
            <div class="col-md-3"><strong>Class:</strong> {{ $exam->class->name ?? 'N/A' }}</div>
            <div class="col-md-3"><strong>Subject:</strong> {{ $exam->subject->name ?? 'N/A' }}</div>
            <div class="col-md-3"><strong>Date:</strong> {{ $exam->exam_date ? $exam->exam_date->format('d M Y') : 'N/A' }}</div>
        </div>
    </div>

    {{-- Overview Stats --}}
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-primary">{{ $stats['total_students'] ?? 0 }}</h3>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-info">{{ $stats['results_entered'] ?? 0 }}</h3>
                    <small class="text-muted">Results Entered</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['average_marks'] ?? 0 }}</h3>
                    <small class="text-muted">Avg Marks</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['highest_marks'] ?? 0 }}</h3>
                    <small class="text-muted">Highest</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-danger">{{ $stats['lowest_marks'] ?? 0 }}</h3>
                    <small class="text-muted">Lowest</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['pass_percentage'] ?? 0 }}%</h3>
                    <small class="text-muted">Pass Rate</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Grade Distribution Chart --}}
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Grade Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="gradeChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Pass/Fail Ratio --}}
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Pass / Fail Ratio</h6>
                </div>
                <div class="card-body">
                    <canvas id="passFailChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Grade Distribution Table --}}
    @if(isset($stats['grade_distribution']))
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-table me-2"></i>Grade Breakdown</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Grade</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Bar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalResults = array_sum($stats['grade_distribution']);
                                $gradeColors = [
                                    'A+' => 'success', 'A' => 'success',
                                    'B+' => 'info', 'B' => 'info',
                                    'C' => 'warning', 'D' => 'warning',
                                    'F' => 'danger',
                                ];
                            @endphp
                            @foreach($stats['grade_distribution'] as $grade => $count)
                                <tr>
                                    <td><span class="badge bg-{{ $gradeColors[$grade] ?? 'secondary' }}">{{ $grade }}</span></td>
                                    <td>{{ $count }}</td>
                                    <td>{{ $totalResults > 0 ? number_format(($count / $totalResults) * 100, 1) : 0 }}%</td>
                                    <td style="width: 40%;">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $gradeColors[$grade] ?? 'secondary' }}" 
                                                 style="width: {{ $totalResults > 0 ? ($count / $totalResults) * 100 : 0 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Top Performers --}}
        @if(isset($stats['top_performers']) && count($stats['top_performers']) > 0)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top Performers</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Student</th>
                                <th>Marks</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['top_performers'] as $performer)
                                <tr>
                                    <td>
                                        @if($performer['rank'] <= 3)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-trophy me-1"></i>#{{ $performer['rank'] }}
                                            </span>
                                        @else
                                            #{{ $performer['rank'] }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $performer['student_name'] }}</span>
                                        <br><small class="text-muted">{{ $performer['student_id'] }}</small>
                                    </td>
                                    <td>{{ $performer['marks'] }} <small class="text-muted">({{ number_format($performer['percentage'], 1) }}%)</small></td>
                                    <td>
                                        <span class="badge bg-{{ $gradeColors[$performer['grade']] ?? 'secondary' }}">{{ $performer['grade'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Grade Distribution Chart
    @if(isset($stats['grade_distribution']))
    var gradeCtx = document.getElementById('gradeChart').getContext('2d');
    new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($stats['grade_distribution'])) !!},
            datasets: [{
                label: 'Number of Students',
                data: {!! json_encode(array_values($stats['grade_distribution'])) !!},
                backgroundColor: [
                    '#198754', '#198754',  // A+, A - green
                    '#0dcaf0', '#0dcaf0',  // B+, B - cyan
                    '#ffc107',             // C - yellow
                    '#fd7e14',             // D - orange
                    '#dc3545'              // F - red
                ],
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    @endif

    // Pass/Fail Pie Chart
    var passFailCtx = document.getElementById('passFailChart').getContext('2d');
    new Chart(passFailCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pass', 'Fail'],
            datasets: [{
                data: [{{ $stats['pass_count'] ?? 0 }}, {{ $stats['fail_count'] ?? 0 }}],
                backgroundColor: ['#198754', '#dc3545'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '60%'
        }
    });
});
</script>
@endpush
@endsection
