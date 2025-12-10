@extends('layouts.app')

@section('title', 'Aging Analysis')
@section('page-title', 'Arrears Aging Analysis')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.arrears.index') }}">Arrears</a></li>
            <li class="breadcrumb-item active">Aging Analysis</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-chart-pie me-2"></i> Arrears Aging Analysis</h4>
            <p class="text-muted mb-0">Breakdown of outstanding amounts by days overdue</p>
        </div>
        <div>
            <a href="{{ route('admin.arrears.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Arrears
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        @foreach($arrearsByAge as $range => $data)
            @php
                $bgClass = match($range) {
                    '0-30' => 'bg-info',
                    '31-60' => 'bg-warning',
                    '61-90' => 'bg-orange',
                    '90+' => 'bg-danger',
                    default => 'bg-secondary'
                };
                $textClass = $range === '31-60' ? 'text-dark' : 'text-white';
            @endphp
            <div class="col-md-3">
                <div class="card border-0 shadow-sm {{ $bgClass }} {{ $textClass }}">
                    <div class="card-body">
                        <h6 class="mb-1 {{ $textClass === 'text-dark' ? '' : 'opacity-75' }}">{{ $range }} Days</h6>
                        <h3 class="mb-0">RM {{ number_format($data['amount'], 2) }}</h3>
                        <small>{{ $data['count'] }} invoices</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Amount Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="agingPieChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Invoice Count by Age</h5>
                </div>
                <div class="card-body">
                    <canvas id="agingBarChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Breakdown by Age Range -->
    @foreach($detailedAging as $range => $invoices)
        @php
            $badgeClass = match($range) {
                '0-30' => 'bg-info',
                '31-60' => 'bg-warning text-dark',
                '61-90' => 'bg-orange',
                '90+' => 'bg-danger',
                default => 'bg-secondary'
            };
            $headerClass = match($range) {
                '0-30' => 'bg-info text-white',
                '31-60' => 'bg-warning text-dark',
                '61-90' => 'bg-orange text-white',
                '90+' => 'bg-danger text-white',
                default => 'bg-secondary text-white'
            };
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header {{ $headerClass }} d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i> {{ $range }} Days Overdue
                </h5>
                <div>
                    <span class="badge bg-white text-dark me-2">{{ $invoices->count() }} invoices</span>
                    <span class="badge bg-white text-dark">RM {{ number_format($invoices->sum('balance'), 2) }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Student</th>
                                <th>Parent Contact</th>
                                <th>Package</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Balance</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice) }}">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <strong>{{ $invoice->student?->user?->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $invoice->student?->student_id ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($invoice->student?->parent)
                                            {{ $invoice->student->parent->user->name ?? 'N/A' }}
                                            <br>
                                            <small>
                                                <i class="fab fa-whatsapp text-success"></i>
                                                {{ $invoice->student->parent->user->phone ?? 'N/A' }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $invoice->enrollment?->package?->name ?? 'N/A' }}</td>
                                    <td>{{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $invoice->days_overdue }} days</span>
                                    </td>
                                    <td>
                                        <strong class="text-danger">RM {{ number_format($invoice->balance, 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.arrears.student', $invoice->student) }}" class="btn btn-outline-primary" title="Student Details">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            <a href="{{ route('admin.payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-outline-success" title="Record Payment">
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No invoices in this aging category
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Analysis Summary -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Analysis Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Risk Assessment</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Low Risk (0-30 days):</td>
                            <td class="text-end">
                                <span class="badge bg-info">{{ $arrearsByAge['0-30']['count'] ?? 0 }} invoices</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Medium Risk (31-60 days):</td>
                            <td class="text-end">
                                <span class="badge bg-warning text-dark">{{ $arrearsByAge['31-60']['count'] ?? 0 }} invoices</span>
                            </td>
                        </tr>
                        <tr>
                            <td>High Risk (61-90 days):</td>
                            <td class="text-end">
                                <span class="badge bg-orange">{{ $arrearsByAge['61-90']['count'] ?? 0 }} invoices</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Critical (90+ days):</td>
                            <td class="text-end">
                                <span class="badge bg-danger">{{ $arrearsByAge['90+']['count'] ?? 0 }} invoices</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Recommended Actions</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-info me-2"></i>
                            <strong>0-30 days:</strong> Standard reminder process
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-exclamation-circle text-warning me-2"></i>
                            <strong>31-60 days:</strong> Personal follow-up call
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone text-orange me-2"></i>
                            <strong>61-90 days:</strong> Management escalation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-gavel text-danger me-2"></i>
                            <strong>90+ days:</strong> Final notice / Collection action
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-orange {
    background-color: #fd7e14 !important;
}
.text-orange {
    color: #fd7e14 !important;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var arrearsByAge = @json($arrearsByAge);

    var labels = Object.keys(arrearsByAge).map(k => k + ' days');
    var amounts = Object.values(arrearsByAge).map(v => v.amount);
    var counts = Object.values(arrearsByAge).map(v => v.count);

    var colors = [
        'rgba(23, 162, 184, 0.8)',   // 0-30: info
        'rgba(255, 193, 7, 0.8)',    // 31-60: warning
        'rgba(253, 126, 20, 0.8)',   // 61-90: orange
        'rgba(220, 53, 69, 0.8)'     // 90+: danger
    ];

    // Pie Chart - Amount Distribution
    var pieCtx = document.getElementById('agingPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: amounts,
                backgroundColor: colors,
                borderWidth: 2
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
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = ((context.raw / total) * 100).toFixed(1);
                            return 'RM ' + context.raw.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Bar Chart - Invoice Count
    var barCtx = document.getElementById('agingBarChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Invoices',
                data: counts,
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
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
