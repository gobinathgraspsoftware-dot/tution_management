@extends('layouts.app')

@section('title', 'Payment Gateways')

@section('page-title', 'Payment Gateway Configuration')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Payment Gateways</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Overview Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total Gateways</h6>
                            <h3 class="mb-0">{{ $gateways->count() }}</h3>
                        </div>
                        <i class="fas fa-credit-card fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Active Gateways</h6>
                            <h3 class="mb-0">{{ $gateways->where('is_active', true)->count() }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">This Month</h6>
                            <h3 class="mb-0">RM {{ number_format(collect($statistics)->sum('total_amount'), 2) }}</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Transactions</h6>
                            <h3 class="mb-0">{{ collect($statistics)->sum('total_transactions') }}</h3>
                        </div>
                        <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configured Gateways -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configured Gateways</h5>
            @if(count($unconfiguredGateways) > 0)
            <a href="{{ route('admin.payment-gateways.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Gateway
            </a>
            @endif
        </div>
        <div class="card-body">
            @if($gateways->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No Payment Gateways Configured</h5>
                    <p class="text-muted">Configure a payment gateway to enable online payments.</p>
                    <a href="{{ route('admin.payment-gateways.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add First Gateway
                    </a>
                </div>
            @else
                <div class="row">
                    @foreach($gateways as $gateway)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 {{ $gateway->is_active ? 'border-success' : 'border-secondary' }}">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    @php
                                        $icons = [
                                            'toyyibpay' => 'fas fa-money-bill-wave',
                                            'senangpay' => 'fas fa-credit-card',
                                            'billplz' => 'fas fa-file-invoice-dollar',
                                            'eghl' => 'fas fa-globe',
                                        ];
                                        $icon = $icons[$gateway->gateway_name] ?? 'fas fa-credit-card';
                                    @endphp
                                    <i class="{{ $icon }} fa-lg me-2 text-primary"></i>
                                    <h6 class="mb-0">{{ $availableGateways[$gateway->gateway_name]['name'] ?? ucfirst($gateway->gateway_name) }}</h6>
                                </div>
                                <span class="badge {{ $gateway->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Mode:</small>
                                    <span class="badge {{ $gateway->is_sandbox ? 'bg-warning' : 'bg-primary' }}">
                                        {{ $gateway->is_sandbox ? 'Sandbox' : 'Production' }}
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">This Month Statistics:</small>
                                    <div class="row text-center mt-2">
                                        <div class="col-4">
                                            <h5 class="mb-0 text-success">{{ $statistics[$gateway->id]['completed'] ?? 0 }}</h5>
                                            <small class="text-muted">Success</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="mb-0 text-warning">{{ $statistics[$gateway->id]['pending'] ?? 0 }}</h5>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="mb-0 text-danger">{{ $statistics[$gateway->id]['failed'] ?? 0 }}</h5>
                                            <small class="text-muted">Failed</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Total Collected:</small>
                                    <h5 class="text-success mb-0">RM {{ number_format($statistics[$gateway->id]['total_amount'] ?? 0, 2) }}</h5>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Fees:</small>
                                    <span>{{ number_format($gateway->transaction_fee_percentage, 2) }}% + RM {{ number_format($gateway->transaction_fee_fixed, 2) }}</span>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.payment-gateways.show', $gateway) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('admin.payment-gateways.edit', $gateway) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.payment-gateways.toggle-status', $gateway) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-{{ $gateway->is_active ? 'warning' : 'success' }} btn-sm">
                                            <i class="fas fa-{{ $gateway->is_active ? 'pause' : 'play' }}"></i>
                                            {{ $gateway->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Available Gateways to Add -->
    @if(count($unconfiguredGateways) > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Available Gateways</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($unconfiguredGateways as $gatewayName)
                @php
                    $gatewayInfo = $availableGateways[$gatewayName] ?? [];
                    $icons = [
                        'toyyibpay' => 'fas fa-money-bill-wave text-primary',
                        'senangpay' => 'fas fa-credit-card text-success',
                        'billplz' => 'fas fa-file-invoice-dollar text-info',
                        'eghl' => 'fas fa-globe text-warning',
                    ];
                    $icon = $icons[$gatewayName] ?? 'fas fa-credit-card text-secondary';
                @endphp
                <div class="col-md-4 mb-3">
                    <div class="card border-dashed h-100">
                        <div class="card-body text-center">
                            <i class="{{ $icon }} fa-3x mb-3"></i>
                            <h5>{{ $gatewayInfo['name'] ?? ucfirst($gatewayName) }}</h5>
                            <p class="text-muted small">{{ $gatewayInfo['description'] ?? '' }}</p>
                            <a href="{{ route('admin.payment-gateways.create', ['type' => $gatewayName]) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-cog me-1"></i> Configure
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.border-dashed {
    border-style: dashed !important;
}
</style>
@endsection
