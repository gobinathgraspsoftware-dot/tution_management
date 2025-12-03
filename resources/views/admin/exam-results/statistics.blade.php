@extends('layouts.app')

@section('title', 'Exam Statistics')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.exam-results.index', $exam) }}">Results</a></li>
                <li class="breadcrumb-item active">Statistics</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Exam Statistics</h1>
                <p class="text-muted mb-0">{{ $exam->name }} - {{ $exam->class->name }}</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h2 class="mb-0 text-primary">{{ $stats['average_marks'] }}</h2>
                    <p class="text-muted mb-0">Average Marks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h2 class="mb-0 text-success">{{ $stats['pass_percentage'] }}%</h2>
                    <p class="text-muted mb-0">Pass Percentage</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h2 class="mb-0 text-info">{{ $stats['highest_marks'] }}</h2>
                    <p class="text-muted mb-0">Highest Marks</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h2 class="mb-0 text-warning">{{ $stats['lowest_marks'] }}</h2>
                    <p class="text-muted mb-0">Lowest Marks</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pass/Fail Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="passFailChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Grade Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="gradeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Top 10 Performers</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student</th>
                            <th>Marks</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['top_performers'] as $result)
                            <tr>
                                <td><span class="badge bg-warning">{{ $result->rank }}</span></td>
                                <td>{{ $result->student->user->name }}</td>
                                <td>{{ $result->marks_obtained }} / {{ $exam->max_marks }}</td>
                                <td>{{ number_format($result->percentage, 2) }}%</td>
                                <td><span class="badge bg-primary">{{ $result->grade }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Pass/Fail Chart
const passFailCtx = document.getElementById('passFailChart').getContext('2d');
new Chart(passFailCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pass', 'Fail'],
        datasets: [{
            data: [{{ $stats['pass_count'] }}, {{ $stats['fail_count'] }}],
            backgroundColor: ['#10b981', '#ef4444']
        }]
    }
});

// Grade Distribution Chart
const gradeCtx = document.getElementById('gradeChart').getContext('2d');
new Chart(gradeCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($stats['grade_distribution'])) !!},
        datasets: [{
            label: 'Number of Students',
            data: {!! json_encode(array_values($stats['grade_distribution'])) !!},
            backgroundColor: '#6366f1'
        }]
    },
    options: {
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
</script>
@endpush
