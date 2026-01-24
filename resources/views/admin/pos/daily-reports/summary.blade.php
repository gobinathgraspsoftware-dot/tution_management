@extends('layouts.app')

@section('title', 'Sales Summary')
@section('page-title', 'Sales Summary')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Sales Summary</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.daily-cash-reports.index') }}">Daily Reports</a></li>
                    <li class="breadcrumb-item active">Summary</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.daily-cash-reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <!-- Period Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.daily-cash-reports.summary') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Total Revenue</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['total_revenue'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Total Transactions</h6>
                            <h3 class="mb-0">{{ number_format($summary['total_transactions'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Avg Daily Sales</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['avg_daily_sales'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-receipt fa-2x opacity-75"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Avg Transaction</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['avg_transaction'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Payment Methods Breakdown -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-wallet text-primary me-2"></i>Payment Methods</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h4 class="text-success mb-1">RM {{ number_format($summary['cash_total'] ?? 0, 2) }}</h4>
                                <small class="text-muted">Cash Sales</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-qrcode fa-2x text-primary mb-2"></i>
                                <h4 class="text-primary mb-1">RM {{ number_format($summary['qr_total'] ?? 0, 2) }}</h4>
                                <small class="text-muted">QR Sales</small>
                            </div>
                        </div>
                    </div>

                    @php
                        $total = ($summary['cash_total'] ?? 0) + ($summary['qr_total'] ?? 0);
                        $cashPercent = $total > 0 ? (($summary['cash_total'] ?? 0) / $total) * 100 : 0;
                        $qrPercent = $total > 0 ? (($summary['qr_total'] ?? 0) / $total) * 100 : 0;
                    @endphp

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cash ({{ number_format($cashPercent, 1) }}%)</span>
                            <span>QR ({{ number_format($qrPercent, 1) }}%)</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" style="width: {{ $cashPercent }}%"></div>
                            <div class="progress-bar bg-primary" style="width: {{ $qrPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variance Summary -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-balance-scale text-primary me-2"></i>Cash Variance</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="p-2">
                                <h3 class="text-success mb-1">{{ $summary['balanced_days'] ?? 0 }}</h3>
                                <small class="text-muted">Balanced</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2">
                                <h3 class="text-info mb-1">{{ $summary['over_days'] ?? 0 }}</h3>
                                <small class="text-muted">Over</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2">
                                <h3 class="text-danger mb-1">{{ $summary['short_days'] ?? 0 }}</h3>
                                <small class="text-muted">Short</small>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Total Over</td>
                            <td class="text-end text-info">+ RM {{ number_format($summary['total_over'] ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Total Short</td>
                            <td class="text-end text-danger">- RM {{ number_format($summary['total_short'] ?? 0, 2) }}</td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Net Variance</strong></td>
                            <td class="text-end">
                                @php
                                    $netVariance = ($summary['total_over'] ?? 0) - ($summary['total_short'] ?? 0);
                                @endphp
                                @if($netVariance >= 0)
                                    <strong class="text-success">+ RM {{ number_format($netVariance, 2) }}</strong>
                                @else
                                    <strong class="text-danger">- RM {{ number_format(abs($netVariance), 2) }}</strong>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Days -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>Top Performing Days</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-center">Trans.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDays ?? [] as $index => $day)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <span class="badge bg-warning">🥇</span>
                                        @elseif($index == 1)
                                            <span class="badge bg-secondary">🥈</span>
                                        @elseif($index == 2)
                                            <span class="badge bg-danger">🥉</span>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.daily-cash-reports.show', $day->id) }}" class="text-decoration-none">
                                            {{ $day->report_date->format('d M Y') }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $day->report_date->format('l') }}</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>RM {{ number_format($day->total_sales, 2) }}</strong>
                                    </td>
                                    <td class="text-center">{{ $day->total_transactions }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                        No data available for this period
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Reports List -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Daily Reports</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-end">Variance</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports ?? [] as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.daily-cash-reports.show', $report->id) }}" class="text-decoration-none">
                                            {{ $report->report_date->format('d M') }}
                                        </a>
                                    </td>
                                    <td class="text-end">RM {{ number_format($report->total_cash_sales + $report->total_qr_sales, 2) }}</td>
                                    <td class="text-end">
                                        @if($report->variance > 0)
                                            <span class="text-info">+{{ number_format($report->variance, 2) }}</span>
                                        @elseif($report->variance < 0)
                                            <span class="text-danger">{{ number_format($report->variance, 2) }}</span>
                                        @else
                                            <span class="text-success">0.00</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($report->status == 'closed')
                                            <span class="badge bg-success">Closed</span>
                                        @else
                                            <span class="badge bg-warning">Open</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                        No reports for this period
                                    </td>
                                </tr>
                                @endforelse
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
<script>
$(document).ready(function() {
    // Nothing complex - just basic page functionality
});
</script>
@endpush
