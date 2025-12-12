@extends('layouts.app')

@section('title', 'Teacher Performance')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Teacher Performance</h1>
            <p class="text-muted mb-0">Monitor and analyze teacher performance metrics</p>
        </div>
        <div>
            <a href="{{ route('admin.teacher-performance.reports') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-alt"></i> View Reports
            </a>
            <a href="{{ route('admin.teacher-performance.comparison') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar"></i> Compare Teachers
            </a>
            <a href="{{ route('admin.teacher-performance.export', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Teachers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_teachers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Avg Performance Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['avg_score'], 1) }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Classes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_classes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Rating</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['avg_rating'], 2) }}/5</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star-half-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-trophy"></i> Top 5 Performers</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Teacher</th>
                                    <th>Type</th>
                                    <th>Classes</th>
                                    <th>Materials</th>
                                    <th>Rating</th>
                                    <th>Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPerformers as $index => $performer)
                                <tr>
                                    <td>
                                        @if($index === 0)
                                            <i class="fas fa-trophy text-warning"></i> #1
                                        @elseif($index === 1)
                                            <i class="fas fa-medal text-secondary"></i> #2
                                        @elseif($index === 2)
                                            <i class="fas fa-medal text-bronze"></i> #3
                                        @else
                                            #{{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>{{ $performer['teacher_name'] }}</td>
                                    <td><span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $performer['employment_type'])) }}</span></td>
                                    <td>{{ $performer['classes'] }}</td>
                                    <td>{{ $performer['materials'] }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="mr-1">{{ number_format($performer['rating'], 1) }}</span>
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $performer['score'] >= 80 ? 'success' : ($performer['score'] >= 60 ? 'warning' : 'danger') }}"
                                                 role="progressbar"
                                                 style="width: {{ $performer['score'] }}%">
                                                {{ number_format($performer['score'], 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.teacher-performance.show', $performer['teacher_id']) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teacher-performance.index') }}">
                <div class="row">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Employment Type</label>
                            <select name="employment_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="full_time" {{ request('employment_type') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ request('employment_type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="contract" {{ request('employment_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Search Teacher</label>
                            <input type="text" name="search" class="form-control" placeholder="Teacher name..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="{{ route('admin.teacher-performance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </a>
            </form>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Teachers Performance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="performanceTable">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($performanceData as $index => $data)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $data['teacher_name'] }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-{{ $data['employment_type'] === 'full_time' ? 'success' : ($data['employment_type'] === 'part_time' ? 'info' : 'warning') }}">
                                    {{ ucwords(str_replace('_', ' ', $data['employment_type'])) }}
                                </span>
                            </td>
                            <td>{{ $data['classes'] }}</td>
                            <td>{{ number_format($data['hours'], 1) }}h</td>
                            <td>{{ $data['materials'] }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($data['rating']))
                                            <i class="fas fa-star text-warning"></i>
                                        @elseif($i - 0.5 <= $data['rating'])
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        @else
                                            <i class="far fa-star text-warning"></i>
                                        @endif
                                    @endfor
                                    <span class="ml-1">({{ number_format($data['rating'], 1) }})</span>
                                </div>
                            </td>
                            <td>{{ $data['reviews'] }}</td>
                            <td>
                                <span class="badge badge-{{ $data['attendance'] >= 90 ? 'success' : ($data['attendance'] >= 75 ? 'warning' : 'danger') }}">
                                    {{ number_format($data['attendance'], 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $data['punctuality'] >= 90 ? 'success' : ($data['punctuality'] >= 75 ? 'warning' : 'danger') }}">
                                    {{ number_format($data['punctuality'], 1) }}%
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px; min-width: 80px;">
                                    <div class="progress-bar bg-{{ $data['score'] >= 80 ? 'success' : ($data['score'] >= 60 ? 'warning' : 'danger') }}"
                                         role="progressbar"
                                         style="width: {{ $data['score'] }}%">
                                        {{ number_format($data['score'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.teacher-performance.show', $data['teacher_id']) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center">No performance data available for selected period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable for better sorting/searching
    $('#performanceTable').DataTable({
        "pageLength": 25,
        "order": [[10, "desc"]], // Sort by score by default
        "columnDefs": [
            { "orderable": false, "targets": 11 } // Action column not sortable
        ]
    });
});
</script>
@endpush
