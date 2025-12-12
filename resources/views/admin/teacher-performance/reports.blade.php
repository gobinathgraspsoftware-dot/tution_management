@extends('layouts.app')

@section('title', 'Teacher Performance Reports')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.teacher-performance.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back to Performance
            </a>
            <h1 class="h3 mb-0 text-gray-800">Performance Reports</h1>
            <p class="text-muted mb-0">Generate and export various performance reports</p>
        </div>
        <div>
            <a href="{{ route('admin.teacher-performance.export', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export Current Report
            </a>
        </div>
    </div>

    <!-- Report Type Selection -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Report Configuration</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teacher-performance.reports') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Report Type</label>
                            <select name="report_type" class="form-control" id="reportType">
                                <option value="summary" {{ $reportType === 'summary' ? 'selected' : '' }}>Summary Report</option>
                                <option value="top_performers" {{ $reportType === 'top_performers' ? 'selected' : '' }}>Top Performers</option>
                                <option value="detailed" {{ $reportType === 'detailed' ? 'selected' : '' }}>Individual Detailed</option>
                                <option value="monthly_trend" {{ $reportType === 'monthly_trend' ? 'selected' : '' }}>Monthly Trends</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" id="teacherSelection" style="display: {{ $reportType === 'detailed' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label>Select Teacher</label>
                            <select name="teacher_id" class="form-control">
                                <option value="">Choose...</option>
                                @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Generate Report
                </button>
            </form>
        </div>
    </div>

    <!-- Report Display -->
    @if($reportData)
        @if($reportType === 'summary' || $reportType === 'top_performers')
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    {{ $reportType === 'summary' ? 'All Teachers Summary' : 'Top 10 Performers' }}
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Rank</th>
                                <th>Teacher</th>
                                <th>Type</th>
                                <th>Classes</th>
                                <th>Hours</th>
                                <th>Materials</th>
                                <th>Rating</th>
                                <th>Reviews</th>
                                <th>Attendance</th>
                                <th>Punctuality</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $index => $data)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $data['teacher_name'] }}</td>
                                <td><span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $data['employment_type'])) }}</span></td>
                                <td>{{ $data['classes'] }}</td>
                                <td>{{ number_format($data['hours'], 1) }}h</td>
                                <td>{{ $data['materials'] }}</td>
                                <td>{{ number_format($data['rating'], 2) }}/5</td>
                                <td>{{ $data['reviews'] }}</td>
                                <td>{{ number_format($data['attendance'], 1) }}%</td>
                                <td>{{ number_format($data['punctuality'], 1) }}%</td>
                                <td>
                                    <span class="badge badge-{{ $data['score'] >= 80 ? 'success' : ($data['score'] >= 60 ? 'warning' : 'danger') }}">
                                        {{ number_format($data['score'], 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @elseif($reportType === 'detailed' && isset($reportData['teacher']))
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    Detailed Report: {{ $reportData['teacher']['name'] }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Teacher Information</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td>{{ $reportData['teacher']['name'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Teacher ID:</strong></td>
                                <td>{{ $reportData['teacher']['teacher_id'] }}</td>
                            </tr>
                            <tr>
                                <td><strong>Employment Type:</strong></td>
                                <td>{{ ucwords(str_replace('_', ' ', $reportData['teacher']['employment_type'])) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Join Date:</strong></td>
                                <td>{{ $reportData['teacher']['join_date'] }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="font-weight-bold">Report Period</h6>
                        <p>{{ $reportData['period']['start'] }} to {{ $reportData['period']['end'] }}</p>
                    </div>
                </div>

                <h6 class="font-weight-bold mb-3">Performance Metrics</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-primary">{{ $reportData['metrics']['classes_conducted'] }}</h4>
                                <small>Classes Conducted</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-success">{{ number_format($reportData['metrics']['total_hours'], 1) }}h</h4>
                                <small>Total Hours</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-info">{{ $reportData['metrics']['materials_uploaded'] }}</h4>
                                <small>Materials Uploaded</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h4 class="text-warning">{{ number_format($reportData['metrics']['average_rating'], 2) }}/5</h4>
                                <small>Average Rating</small>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="font-weight-bold mb-3 mt-4">Recent Reviews</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['reviews'] as $review)
                            <tr>
                                <td>{{ $review['student_name'] }}</td>
                                <td>{{ $review['class_name'] }}</td>
                                <td>{{ $review['rating'] }}/5</td>
                                <td>{{ $review['review'] }}</td>
                                <td>{{ $review['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @elseif($reportType === 'monthly_trend')
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">6-Month Performance Trends</h6>
            </div>
            <div class="card-body">
                @foreach($reportData as $teacherData)
                <div class="mb-4 pb-3 border-bottom">
                    <h6 class="font-weight-bold">{{ $teacherData['teacher'] }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Classes</th>
                                    <th>Materials</th>
                                    <th>Rating</th>
                                    <th>Attendance</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teacherData['trend'] as $month)
                                <tr>
                                    <td>{{ $month['month'] }}</td>
                                    <td>{{ $month['classes'] }}</td>
                                    <td>{{ $month['materials'] }}</td>
                                    <td>{{ number_format($month['rating'], 1) }}/5</td>
                                    <td>{{ number_format($month['attendance'], 1) }}%</td>
                                    <td>
                                        <span class="badge badge-{{ $month['score'] >= 80 ? 'success' : ($month['score'] >= 60 ? 'warning' : 'danger') }}">
                                            {{ number_format($month['score'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @else
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Configure report options above and click "Generate Report" to view data.
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#reportType').change(function() {
        if ($(this).val() === 'detailed') {
            $('#teacherSelection').show();
        } else {
            $('#teacherSelection').hide();
        }
    });
});
</script>
@endpush
