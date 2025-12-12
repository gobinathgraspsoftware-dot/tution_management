@extends('layouts.app')

@section('title', 'My Performance')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">My Performance</h1>
            <p class="text-muted mb-0">Track your teaching performance and progress</p>
        </div>
        <div>
            <a href="{{ route('teacher.performance.analytics') }}" class="btn btn-primary">
                <i class="fas fa-chart-line"></i> View Analytics
            </a>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('teacher.performance.index') }}" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Period:</label>
                    <input type="date" name="start_date" class="form-control form-control-sm mr-2" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">to</label>
                    <input type="date" name="end_date" class="form-control form-control-sm mr-2" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Update</button>
            </form>
        </div>
    </div>

    <!-- Performance Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Classes Taught</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['classes_conducted'] }}</div>
                            @if($summary['trends']['classes']['direction'] !== 'neutral')
                            <small class="text-{{ $summary['trends']['classes']['direction'] === 'up' ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $summary['trends']['classes']['direction'] }}"></i>
                                {{ $summary['trends']['classes']['percentage'] }}% from last month
                            </small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Teaching Hours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($metrics['total_hours'], 1) }}h</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Materials Uploaded</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['materials_uploaded'] }}</div>
                            @if($summary['trends']['materials']['direction'] !== 'neutral')
                            <small class="text-{{ $summary['trends']['materials']['direction'] === 'up' ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $summary['trends']['materials']['direction'] }}"></i>
                                {{ $summary['trends']['materials']['percentage'] }}% from last month
                            </small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-upload fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Student Rating</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($metrics['average_rating'], 2) }}/5</div>
                            <div class="mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($metrics['average_rating']))
                                        <i class="fas fa-star text-warning"></i>
                                    @elseif($i - 0.5 <= $metrics['average_rating'])
                                        <i class="fas fa-star-half-alt text-warning"></i>
                                    @else
                                        <i class="far fa-star text-warning"></i>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Score & Attendance -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Overall Performance Score</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="h1 font-weight-bold text-{{ $metrics['performance_score'] >= 80 ? 'success' : ($metrics['performance_score'] >= 60 ? 'warning' : 'danger') }}">
                            {{ number_format($metrics['performance_score'], 1) }}%
                        </div>
                        <div class="h5">
                            @if($metrics['performance_score'] >= 90)
                                <span class="badge badge-success">Excellent</span>
                            @elseif($metrics['performance_score'] >= 80)
                                <span class="badge badge-success">Very Good</span>
                            @elseif($metrics['performance_score'] >= 70)
                                <span class="badge badge-info">Good</span>
                            @elseif($metrics['performance_score'] >= 60)
                                <span class="badge badge-warning">Average</span>
                            @else
                                <span class="badge badge-danger">Needs Improvement</span>
                            @endif
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-{{ $metrics['performance_score'] >= 80 ? 'success' : ($metrics['performance_score'] >= 60 ? 'warning' : 'danger') }}"
                             role="progressbar"
                             style="width: {{ $metrics['performance_score'] }}%">
                        </div>
                    </div>
                    @if($summary['trends']['score']['direction'] !== 'neutral')
                    <div class="text-{{ $summary['trends']['score']['direction'] === 'up' ? 'success' : 'danger' }}">
                        <i class="fas fa-arrow-{{ $summary['trends']['score']['direction'] }}"></i>
                        {{ $summary['trends']['score']['percentage'] }}% from last month
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">Attendance Rate</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="h2 font-weight-bold text-success">{{ number_format($metrics['attendance_rate'], 1) }}%</div>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-{{ $metrics['attendance_rate'] >= 90 ? 'success' : ($metrics['attendance_rate'] >= 75 ? 'warning' : 'danger') }}"
                             role="progressbar"
                             style="width: {{ $metrics['attendance_rate'] }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Punctuality Rate</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="h2 font-weight-bold text-info">{{ number_format($metrics['punctuality_rate'], 1) }}%</div>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-{{ $metrics['punctuality_rate'] >= 90 ? 'success' : ($metrics['punctuality_rate'] >= 75 ? 'warning' : 'danger') }}"
                             role="progressbar"
                             style="width: {{ $metrics['punctuality_rate'] }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trend -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">6-Month Performance Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Student Feedback</h6>
                </div>
                <div class="card-body">
                    @forelse($reviews as $review)
                    <div class="media mb-3 pb-3 border-bottom">
                        <div class="media-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="mt-0">{{ $review['student_name'] }}</h6>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $review['rating'] ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <small class="text-muted">{{ $review['class_name'] }} - {{ $review['date'] }}</small>
                            <p class="mt-2 mb-0">{{ $review['review'] }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">No reviews available for this period</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Performance Trend Chart
const trendData = @json($trend);
const ctx = document.getElementById('performanceTrendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: trendData.map(d => d.month),
        datasets: [
            {
                label: 'Performance Score',
                data: trendData.map(d => d.score),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4
            },
            {
                label: 'Classes',
                data: trendData.map(d => d.classes),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4
            },
            {
                label: 'Materials',
                data: trendData.map(d => d.materials),
                borderColor: 'rgb(255, 206, 86)',
                backgroundColor: 'rgba(255, 206, 86, 0.1)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
