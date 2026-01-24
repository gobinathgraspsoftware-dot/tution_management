@extends('layouts.app')

@section('title', 'Daily Cash Reports')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Daily Cash Reports</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.pos.index') }}">POS</a></li>
                    <li class="breadcrumb-item active">Daily Reports</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.daily-cash-reports.index') }}" class="btn btn-primary">
                <i class="fas fa-calendar-day me-1"></i> Today's Report
            </a>
            <a href="{{ route('admin.daily-cash-reports.summary') }}" class="btn btn-outline-primary ms-2">
                <i class="fas fa-chart-bar me-1"></i> Monthly Summary
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-file-alt text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Reports</h6>
                            <h3 class="mb-0">{{ number_format($summary['total_reports'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-money-bill-wave text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">This Month Revenue</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['month_revenue'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-shopping-cart text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">This Month Transactions</h6>
                            <h3 class="mb-0">{{ number_format($summary['month_transactions'] ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-balance-scale text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Avg Daily Sales</h6>
                            <h3 class="mb-0">RM {{ number_format($summary['avg_daily_sales'] ?? 0, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.daily-cash-reports.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control"
                           value="{{ request('month', now()->format('Y-m')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cashier</label>
                    <select name="cashier_id" class="form-select">
                        <option value="">All Cashiers</option>
                        @foreach($cashiers ?? [] as $cashier)
                            <option value="{{ $cashier->id }}" {{ request('cashier_id') == $cashier->id ? 'selected' : '' }}>
                                {{ $cashier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.daily-cash-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cash Reports</h5>
                <a href="{{ route('admin.daily-cash-reports.export', request()->all()) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Cashier</th>
                            <th class="text-end">Opening Cash</th>
                            <th class="text-end">Cash Sales</th>
                            <th class="text-end">QR Sales</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Expected Cash</th>
                            <th class="text-end">Actual Cash</th>
                            <th class="text-center">Variance</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <strong>{{ $report->report_date->format('d M Y') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $report->report_date->format('l') }}</small>
                                </td>
                                <td>{{ $report->openedBy->name ?? 'N/A' }}</td>
                                <td class="text-end">RM {{ number_format($report->opening_cash, 2) }}</td>
                                <td class="text-end">RM {{ number_format($report->cash_sales, 2) }}</td>
                                <td class="text-end">RM {{ number_format($report->qr_sales, 2) }}</td>
                                <td class="text-end">
                                    <strong>RM {{ number_format($report->total_sales, 2) }}</strong>
                                </td>
                                <td class="text-end">RM {{ number_format($report->expected_cash, 2) }}</td>
                                <td class="text-end">
                                    @if($report->status == 'closed')
                                        RM {{ number_format($report->actual_cash, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($report->status == 'closed')
                                        @php
                                            $variance = $report->actual_cash - $report->expected_cash;
                                        @endphp
                                        @if($variance == 0)
                                            <span class="badge bg-success">Balanced</span>
                                        @elseif($variance > 0)
                                            <span class="badge bg-info">+RM {{ number_format($variance, 2) }}</span>
                                        @else
                                            <span class="badge bg-danger">-RM {{ number_format(abs($variance), 2) }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($report->status == 'open')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-unlock me-1"></i> Open
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-lock me-1"></i> Closed
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.daily-cash-reports.show', $report) }}"
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($report->status == 'open' && $report->report_date->isToday())
                                            <a href="{{ route('admin.daily-cash-reports.close', $report) }}"
                                               class="btn btn-outline-success" title="Close Day">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.daily-cash-reports.download', $report) }}"
                                           class="btn btn-outline-secondary" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <p class="mb-0">No reports found for the selected period.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reports->hasPages())
            <div class="card-footer bg-white">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
