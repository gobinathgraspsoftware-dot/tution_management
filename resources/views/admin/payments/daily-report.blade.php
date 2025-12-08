@extends('layouts.app')

@section('title', 'Daily Cash Report')
@section('page-title', 'Daily Cash Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
<li class="breadcrumb-item active">Daily Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Date Selection -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.payments.daily-report') }}" method="GET" class="row align-items-center g-3">
                <div class="col-auto">
                    <label class="form-label mb-0 fw-semibold">Select Date:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> View Report
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.payments.daily-report', ['date' => now()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">
                        Today
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Report Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day me-2"></i>
                        {{ $date->format('l, d M Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Report Status -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Status:</span>
                        <span class="badge bg-{{ $report->status === 'open' ? 'success' : 'secondary' }} fs-6">
                            {{ ucfirst($report->status) }}
                        </span>
                    </div>

                    <!-- Opening Cash -->
                    <div class="mb-4">
                        <label class="form-label text-muted small">Opening Cash</label>
                        @if($report->status === 'open')
                        <form action="{{ route('admin.payments.update-daily-report') }}" method="POST" class="input-group">
                            @csrf
                            <input type="hidden" name="report_date" value="{{ $date->format('Y-m-d') }}">
                            <span class="input-group-text">RM</span>
                            <input type="number" name="opening_cash" step="0.01" min="0"
                                class="form-control" value="{{ $report->opening_cash }}">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                        @else
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="text" class="form-control" value="{{ number_format($report->opening_cash, 2) }}" readonly>
                        </div>
                        @endif
                    </div>

                    <hr>

                    <!-- Collections Summary -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-money-bill text-success me-2"></i>Cash Sales</span>
                            <strong>RM {{ number_format($summary['by_method']['cash']['amount'], 2) }}</strong>
                        </div>
                        <small class="text-muted">{{ $summary['by_method']['cash']['count'] }} transactions</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-qrcode text-info me-2"></i>QR Payments</span>
                            <strong>RM {{ number_format($summary['by_method']['qr']['amount'], 2) }}</strong>
                        </div>
                        <small class="text-muted">{{ $summary['by_method']['qr']['count'] }} transactions</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-exchange-alt text-secondary me-2"></i>Other</span>
                            <strong>RM {{ number_format($summary['by_method']['other']['amount'], 2) }}</strong>
                        </div>
                        <small class="text-muted">{{ $summary['by_method']['other']['count'] }} transactions</small>
                    </div>

                    <hr>

                    <!-- Totals -->
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Collections</span>
                            <strong class="text-success">RM {{ number_format($summary['total_collected'], 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Transactions</span>
                            <strong>{{ $summary['total_transactions'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Expected Closing (Cash)</span>
                            <strong>RM {{ number_format($report->opening_cash + $summary['by_method']['cash']['amount'], 2) }}</strong>
                        </div>
                    </div>

                    @if($report->status === 'open')
                    <hr>
                    <!-- Close Report -->
                    <form action="{{ route('admin.payments.close-daily-report') }}" method="POST" id="closeReportForm">
                        @csrf
                        <input type="hidden" name="report_date" value="{{ $date->format('Y-m-d') }}">
                        <div class="mb-3">
                            <label class="form-label">Actual Closing Cash (RM)</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" name="actual_closing" step="0.01" min="0"
                                    class="form-control" placeholder="Count your drawer" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to close this report? This action cannot be undone.')">
                            <i class="fas fa-lock me-1"></i> Close Daily Report
                        </button>
                    </form>
                    @else
                    <!-- Closed Report Details -->
                    <div class="bg-secondary bg-opacity-10 rounded p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Actual Closing</span>
                            <strong>RM {{ number_format($report->actual_closing, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Variance</span>
                            <strong class="{{ $report->variance >= 0 ? 'text-success' : 'text-danger' }}">
                                RM {{ number_format($report->variance, 2) }}
                            </strong>
                        </div>
                        @if($report->closedBy)
                        <div class="d-flex justify-content-between">
                            <span>Closed By</span>
                            <strong>{{ $report->closedBy->name }}</strong>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            @if($previousReport)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Previous Day Reference</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">{{ $previousReport->report_date->format('d M Y') }}</small>
                    <div class="d-flex justify-content-between mt-2">
                        <span>Closing Cash</span>
                        <strong>RM {{ number_format($previousReport->actual_closing ?? $previousReport->expected_closing, 2) }}</strong>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Transactions List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Transactions ({{ $payments->count() }})</h5>
                    <a href="{{ route('admin.payments.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Record Payment
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Payment #</th>
                                    <th>Student</th>
                                    <th>Method</th>
                                    <th class="text-end">Amount</th>
                                    <th>By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('h:i A') }}</td>
                                    <td>
                                        <strong>{{ $payment->payment_number }}</strong>
                                        @if($payment->reference_number)
                                        <br><small class="text-muted">{{ $payment->reference_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $payment->student->user->name ?? 'N/A' }}
                                        <br><small class="text-muted">{{ $payment->student->student_id ?? '' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $methodBadges = [
                                                'cash' => 'success',
                                                'qr' => 'info',
                                                'bank_transfer' => 'secondary',
                                                'cheque' => 'dark',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $methodBadges[$payment->payment_method] ?? 'secondary' }}">
                                            {{ ucfirst($payment->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">RM {{ number_format($payment->amount, 2) }}</strong>
                                    </td>
                                    <td>{{ $payment->processedBy->name ?? 'System' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-success" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No transactions recorded for this day.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($payments->count() > 0)
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="4" class="text-end">Day Total:</th>
                                    <th class="text-end text-success">RM {{ number_format($payments->sum('amount'), 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
