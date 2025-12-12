@extends('layouts.app')

@section('title', 'Teacher Performance Comparison')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.teacher-performance.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back to Performance
            </a>
            <h1 class="h3 mb-0 text-gray-800">Performance Comparison</h1>
            <p class="text-muted mb-0">Compare up to 5 teachers side-by-side</p>
        </div>
    </div>

    <!-- Selection & Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Select Teachers & Period</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teacher-performance.comparison') }}">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Select Teachers (up to 5)</label>
                            <select name="teachers[]" class="form-control" multiple size="10" required>
                                @foreach($allTeachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ in_array($teacher->id, $teacherIds) ? 'selected' : '' }}>
                                    {{ $teacher->user->name }} - {{ ucwords(str_replace('_', ' ', $teacher->employment_type)) }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl (Cmd on Mac) to select multiple teachers</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-chart-bar"></i> Compare Selected Teachers
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($comparisonData))
    <!-- Comparison Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Classes Conducted</h6>
                </div>
                <div class="card-body">
                    <canvas id="classesChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Materials Uploaded</h6>
                </div>
                <div class="card-body">
                    <canvas id="materialsChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Average Ratings</h6>
                </div>
                <div class="card-body">
                    <canvas id="ratingsChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Overall Performance Score</h6>
                </div>
                <div class="card-body">
                    <canvas id="scoresChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Comparison Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detailed Metrics Comparison</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Metric</th>
                            @foreach($comparisonData as $data)
                            <th class="text-center">{{ $data['teacher']->user->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Employment Type</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">
                                <span class="badge badge-{{ $data['teacher']->employment_type === 'full_time' ? 'success' : ($data['teacher']->employment_type === 'part_time' ? 'info' : 'warning') }}">
                                    {{ ucwords(str_replace('_', ' ', $data['teacher']->employment_type)) }}
                                </span>
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Classes Conducted</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">{{ $data['metrics']['classes_conducted'] }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Total Hours</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">{{ number_format($data['metrics']['total_hours'], 1) }}h</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Materials Uploaded</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">{{ $data['metrics']['materials_uploaded'] }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Average Rating</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">
                                {{ number_format($data['metrics']['average_rating'], 2) }}/5
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($data['metrics']['average_rating']))
                                            <i class="fas fa-star text-warning"></i>
                                        @elseif($i - 0.5 <= $data['metrics']['average_rating'])
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                </div>
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Total Reviews</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">{{ $data['metrics']['total_reviews'] }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Attendance Rate</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">
                                <span class="badge badge-{{ $data['metrics']['attendance_rate'] >= 90 ? 'success' : ($data['metrics']['attendance_rate'] >= 75 ? 'warning' : 'danger') }}">
                                    {{ number_format($data['metrics']['attendance_rate'], 1) }}%
                                </span>
                            </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Punctuality Rate</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">
                                <span class="badge badge-{{ $data['metrics']['punctuality_rate'] >= 90 ? 'success' : ($data['metrics']['punctuality_rate'] >= 75 ? 'warning' : 'danger') }}">
                                    {{ number_format($data['metrics']['punctuality_rate'], 1) }}%
                                </span>
                            </td>
                            @endforeach
                        </tr>
                        <tr class="bg-light">
                            <td><strong>Overall Performance Score</strong></td>
                            @foreach($comparisonData as $data)
                            <td class="text-center">
                                <div class="h5 mb-0 text-{{ $data['metrics']['performance_score'] >= 80 ? 'success' : ($data['metrics']['performance_score'] >= 60 ? 'warning' : 'danger') }}">
                                    {{ number_format($data['metrics']['performance_score'], 1) }}%
                                </div>
                            </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Please select teachers to compare above.
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
@if(!empty($chartData))
const chartData = @json($chartData);

// Classes Chart
new Chart(document.getElementById('classesChart'), {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Classes Conducted',
            data: chartData.classes,
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
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

// Materials Chart
new Chart(document.getElementById('materialsChart'), {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Materials Uploaded',
            data: chartData.materials,
            backgroundColor: 'rgba(255, 206, 86, 0.5)',
            borderColor: 'rgba(255, 206, 86, 1)',
            borderWidth: 1
        }]
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

// Ratings Chart
new Chart(document.getElementById('ratingsChart'), {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Average Rating',
            data: chartData.ratings,
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 5
            }
        }
    }
});

// Scores Chart
new Chart(document.getElementById('scoresChart'), {
    type: 'bar',
    data: {
        labels: chartData.labels,
        datasets: [{
            label: 'Performance Score (%)',
            data: chartData.scores,
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
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
@endif
</script>
@endpush
