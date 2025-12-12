@extends('layouts.app')

@section('title', 'Performance Analytics')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('teacher.performance.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back to Performance
            </a>
            <h1 class="h3 mb-0 text-gray-800">Detailed Analytics</h1>
            <p class="text-muted mb-0">Comprehensive view of your performance trends and insights</p>
        </div>
        <div>
            <form method="GET" action="{{ route('teacher.performance.analytics') }}" class="form-inline">
                <div class="form-group mr-2">
                    <label class="mr-2">View:</label>
                    <select name="months" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="6" {{ $months == 6 ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="12" {{ $months == 12 ? 'selected' : '' }}>Last 12 Months</option>
                        <option value="24" {{ $months == 24 ? 'selected' : '' }}>Last 24 Months</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Current vs Previous Month Comparison -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Current Month vs Previous Month</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-center mb-3">Current Month</h6>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="h4 text-primary">{{ $currentMetrics['classes_conducted'] }}</div>
                                    <small>Classes</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-success">{{ $currentMetrics['materials_uploaded'] }}</div>
                                    <small>Materials</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-warning">{{ number_format($currentMetrics['average_rating'], 2) }}</div>
                                    <small>Avg Rating</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-info">{{ number_format($currentMetrics['performance_score'], 1) }}%</div>
                                    <small>Score</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-center mb-3">Previous Month</h6>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="h4 text-muted">{{ $previousMetrics['classes_conducted'] }}</div>
                                    <small>Classes</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-muted">{{ $previousMetrics['materials_uploaded'] }}</div>
                                    <small>Materials</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-muted">{{ number_format($previousMetrics['average_rating'], 2) }}</div>
                                    <small>Avg Rating</small>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 text-muted">{{ number_format($previousMetrics['performance_score'], 1) }}%</div>
                                    <small>Score</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Year-to-Date Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">YTD Classes</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ytdMetrics['classes_conducted'] }}</div>
                    <div class="text-xs text-muted">Year-to-Date</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">YTD Hours</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ytdMetrics['total_hours'], 1) }}h</div>
                    <div class="text-xs text-muted">Year-to-Date</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">YTD Materials</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $ytdMetrics['materials_uploaded'] }}</div>
                    <div class="text-xs text-muted">Year-to-Date</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">YTD Avg Rating</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ytdMetrics['average_rating'], 2) }}/5</div>
                    <div class="text-xs text-muted">Year-to-Date</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Classes & Materials Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="classesMateria lsChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rating & Attendance Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="ratingAttendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Score Over Time -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Score Over Time</h6>
                </div>
                <div class="card-body">
                    <canvas id="scoreChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Performance Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Performance Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Classes</th>
                                    <th>Materials</th>
                                    <th>Rating</th>
                                    <th>Attendance %</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trend as $month)
                                <tr>
                                    <td><strong>{{ $month['month'] }}</strong></td>
                                    <td>{{ $month['classes'] }}</td>
                                    <td>{{ $month['materials'] }}</td>
                                    <td>
                                        {{ number_format($month['rating'], 1) }}/5
                                        <div>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= floor($month['rating']) ? 'text-warning' : 'text-muted' }}" style="font-size: 0.75rem;"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $month['attendance'] >= 90 ? 'success' : ($month['attendance'] >= 75 ? 'warning' : 'danger') }}">
                                            {{ number_format($month['attendance'], 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px; min-width: 60px;">
                                            <div class="progress-bar bg-{{ $month['score'] >= 80 ? 'success' : ($month['score'] >= 60 ? 'warning' : 'danger') }}"
                                                 role="progressbar"
                                                 style="width: {{ $month['score'] }}%">
                                                {{ number_format($month['score'], 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $grade = $month['score'] >= 90 ? 'A+' :
                                                    ($month['score'] >= 85 ? 'A' :
                                                    ($month['score'] >= 80 ? 'A-' :
                                                    ($month['score'] >= 75 ? 'B+' :
                                                    ($month['score'] >= 70 ? 'B' :
                                                    ($month['score'] >= 65 ? 'B-' :
                                                    ($month['score'] >= 60 ? 'C+' :
                                                    ($month['score'] >= 55 ? 'C' : 'C-')))))));
                                        @endphp
                                        <span class="badge badge-{{ $month['score'] >= 80 ? 'success' : ($month['score'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ $grade }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const chartData = @json($chartData);

// Classes & Materials Chart
new Chart(document.getElementById('classesMaterialsChart'), {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [
            {
                label: 'Classes',
                data: chartData.classes,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Materials',
                data: chartData.materials,
                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Rating & Attendance Chart
new Chart(document.getElementById('ratingAttendanceChart'), {
    type: 'line',
    data: {
        labels: chartData.labels,
        datasets: [
            {
                label: 'Rating (x20)',
                data: chartData.ratings.map(r => r * 20),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            },
            {
                label: 'Attendance %',
                data: chartData.attendance,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                yAxisID: 'y'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Performance Score Chart
new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Performance Score (%)',
            data: chartData.scores,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>
@endpush
