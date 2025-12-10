@extends('layouts.app')

@section('title', 'Arrears by Subject')
@section('page-title', 'Arrears by Subject')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
            <li class="breadcrumb-item active">By Subject</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-book me-2"></i> Arrears by Subject</h4>
            <p class="text-muted mb-0">Breakdown of outstanding payments by subject</p>
        </div>
        <div>
            <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Arrears
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">RM {{ number_format($totalArrears, 2) }}</h3>
                            <small>Total Arrears Across All Subjects</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book-open fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ count($arrearsBySubject) }}</h3>
                            <small>Subjects with Arrears</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0">{{ collect($arrearsBySubject)->sum('students_with_arrears') }}</h3>
                            <small>Total Students with Arrears</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Arrears by Subject Chart -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Arrears Distribution by Subject</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <canvas id="arrearsBySubjectPieChart" height="200"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="arrearsBySubjectBarChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Arrears by Subject Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i> Detailed Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Students with Arrears</th>
                            <th>Total Arrears</th>
                            <th>% of Total</th>
                            <th>Avg. per Student</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arrearsBySubject as $subjectData)
                            @php
                                $percentage = $totalArrears > 0 ? ($subjectData->total_arrears / $totalArrears) * 100 : 0;
                                $avgPerStudent = $subjectData->students_with_arrears > 0
                                    ? $subjectData->total_arrears / $subjectData->students_with_arrears
                                    : 0;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $subjectData->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $subjectData->code ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">{{ $subjectData->students_with_arrears }} students</span>
                                </td>
                                <td>
                                    <strong class="text-danger">RM {{ number_format($subjectData->total_arrears, 2) }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar"
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small>{{ number_format($percentage, 1) }}%</small>
                                    </div>
                                </td>
                                <td>
                                    RM {{ number_format($avgPerStudent, 2) }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.arrears.index', ['subject_id' => $subjectData->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <p>No arrears found by subject!</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($arrearsBySubject) > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th>{{ collect($arrearsBySubject)->sum('students_with_arrears') }} students</th>
                                <th class="text-danger">RM {{ number_format($totalArrears, 2) }}</th>
                                <th>100%</th>
                                <th>
                                    @php
                                        $totalStudents = collect($arrearsBySubject)->sum('students_with_arrears');
                                    @endphp
                                    RM {{ number_format($totalStudents > 0 ? $totalArrears / $totalStudents : 0, 2) }}
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var subjectNames = {!! json_encode(collect($arrearsBySubject)->pluck('name')) !!};
    var arrearAmounts = {!! json_encode(collect($arrearsBySubject)->pluck('total_arrears')) !!};

    // Color palette
    var colors = [
        'rgba(220, 53, 69, 0.7)',
        'rgba(255, 193, 7, 0.7)',
        'rgba(23, 162, 184, 0.7)',
        'rgba(40, 167, 69, 0.7)',
        'rgba(111, 66, 193, 0.7)',
        'rgba(253, 126, 20, 0.7)',
        'rgba(32, 201, 151, 0.7)',
        'rgba(102, 16, 242, 0.7)'
    ];

    // Pie Chart
    var pieCtx = document.getElementById('arrearsBySubjectPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: subjectNames,
            datasets: [{
                data: arrearAmounts,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'RM ' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Bar Chart
    var barCtx = document.getElementById('arrearsBySubjectBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: subjectNames,
            datasets: [{
                label: 'Arrears Amount (RM)',
                data: arrearAmounts,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
