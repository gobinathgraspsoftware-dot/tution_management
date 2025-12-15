@extends('layouts.app')

@section('title', 'Payment Gateway Details')

@section('page-title', $gatewayInfo['name'] ?? ucfirst($paymentGateway->gateway_name))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
        <li class="breadcrumb-item active">{{ $gatewayInfo['name'] ?? ucfirst($paymentGateway->gateway_name) }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        @php
                            $icons = [
                                'toyyibpay' => 'fas fa-money-bill-wave text-primary',
                                'senangpay' => 'fas fa-credit-card text-success',
                                'billplz' => 'fas fa-file-invoice-dollar text-info',
                                'eghl' => 'fas fa-globe text-warning',
                            ];
                            $icon = $icons[$paymentGateway->gateway_name] ?? 'fas fa-credit-card text-secondary';
                        @endphp
                        <i class="{{ $icon }} fa-3x me-3"></i>
                        <div>
                            <h3 class="mb-1">{{ $gatewayInfo['name'] ?? ucfirst($paymentGateway->gateway_name) }}</h3>
                            <p class="text-muted mb-0">{{ $gatewayInfo['description'] ?? 'Malaysian Payment Gateway' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge {{ $paymentGateway->is_active ? 'bg-success' : 'bg-secondary' }} fs-6 me-2">
                        {{ $paymentGateway->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="badge {{ $paymentGateway->is_sandbox ? 'bg-warning' : 'bg-primary' }} fs-6">
                        {{ $paymentGateway->is_sandbox ? 'Sandbox' : 'Production' }}
                    </span>
                    <div class="mt-3">
                        <a href="{{ route('admin.payment-gateways.edit', $paymentGateway) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.payment-gateways.transactions', $paymentGateway) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-list me-1"></i> Transactions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h6 class="mb-0">Successful Payments</h6>
                    <h3 class="mb-0">{{ $statistics['completed'] ?? 0 }}</h3>
                    <small>This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h6 class="mb-0">Pending</h6>
                    <h3 class="mb-0">{{ $statistics['pending'] ?? 0 }}</h3>
                    <small>This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h6 class="mb-0">Failed</h6>
                    <h3 class="mb-0">{{ $statistics['failed'] ?? 0 }}</h3>
                    <small>This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h6 class="mb-0">Total Collected</h6>
                    <h3 class="mb-0">RM {{ number_format($statistics['total_amount'] ?? 0, 2) }}</h3>
                    <small>This Month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gateway Configuration -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuration</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Gateway</th>
                            <td>{{ $gatewayInfo['name'] ?? ucfirst($paymentGateway->gateway_name) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge {{ $paymentGateway->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $paymentGateway->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Mode</th>
                            <td>
                                <span class="badge {{ $paymentGateway->is_sandbox ? 'bg-warning' : 'bg-primary' }}">
                                    {{ $paymentGateway->is_sandbox ? 'Sandbox' : 'Production' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Merchant ID</th>
                            <td>{{ $paymentGateway->merchant_id ?: 'Not Set' }}</td>
                        </tr>
                        <tr>
                            <th>API Key</th>
                            <td>{{ $paymentGateway->api_key ? '••••••••' : 'Not Set' }}</td>
                        </tr>
                        <tr>
                            <th>Fee</th>
                            <td>{{ number_format($paymentGateway->transaction_fee_percentage, 2) }}% + RM {{ number_format($paymentGateway->transaction_fee_fixed, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Currencies</th>
                            <td>{{ implode(', ', $paymentGateway->supported_currencies ?? ['MYR']) }}</td>
                        </tr>
                        <tr>
                            <th>Payment Methods</th>
                            <td>{{ implode(', ', array_map('ucfirst', $gatewayInfo['supported_methods'] ?? [])) }}</td>
                        </tr>
                    </table>

                    @if($paymentGateway->configuration)
                    <h6 class="mt-4 mb-3">Additional Configuration</h6>
                    <table class="table table-borderless table-sm">
                        @foreach($paymentGateway->configuration as $key => $value)
                        <tr>
                            <th width="50%">{{ ucwords(str_replace('_', ' ', $key)) }}</th>
                            <td>{{ $value }}</td>
                        </tr>
                        @endforeach
                    </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly Statistics</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Transactions</h5>
            <a href="{{ route('admin.payment-gateways.transactions', $paymentGateway) }}" class="btn btn-outline-primary btn-sm">
                View All <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body">
            @if($recentTransactions->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No transactions yet</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Invoice</th>
                                <th>Student</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>
                                    <code>{{ Str::limit($transaction->transaction_id, 20) }}</code>
                                </td>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $transaction->invoice) }}">
                                        {{ $transaction->invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $transaction->invoice->student->user->name ?? 'N/A' }}</td>
                                <td>RM {{ number_format($transaction->amount, 2) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'refunded' => 'secondary',
                                            'cancelled' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$transaction->status] ?? 'secondary' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    var ctx = document.getElementById('monthlyChart').getContext('2d');

    var monthlyStats = @json($monthlyStats);
    var labels = Object.keys(monthlyStats);
    var successData = labels.map(function(month) { return monthlyStats[month].completed || 0; });
    var failedData = labels.map(function(month) { return monthlyStats[month].failed || 0; });
    var amountData = labels.map(function(month) { return monthlyStats[month].total_amount || 0; });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Successful',
                    data: successData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Failed',
                    data: failedData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
@endsection
