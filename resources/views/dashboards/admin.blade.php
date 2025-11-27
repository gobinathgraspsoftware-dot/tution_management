@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e3f2fd; color: #2196f3;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $total_students }}</h3>
                <p class="text-muted mb-0">Total Students</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #fff3e0; color: #ff9800;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $pending_approvals }}</h3>
                <p class="text-muted mb-0">Pending Approvals</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $total_teachers }}</h3>
                <p class="text-muted mb-0">Active Teachers</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e8f5e9; color: #4caf50;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">RM {{ number_format($total_revenue_month, 2) }}</h3>
                <p class="text-muted mb-0">Revenue (This Month)</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row 2 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #e1f5fe; color: #03a9f4;">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $active_classes }}</h3>
                <p class="text-muted mb-0">Active Classes</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #ffebee; color: #f44336;">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $pending_payments }}</h3>
                <p class="text-muted mb-0">Pending Payments</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="d-grid gap-2 d-md-flex">
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i> Add Student
                    </a>
                    <a href="#" class="btn btn-success">
                        <i class="fas fa-check me-2"></i> Process Payment
                    </a>
                    <a href="#" class="btn btn-info">
                        <i class="fas fa-bullhorn me-2"></i> New Announcement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-graduate me-2"></i> Recent Enrollments</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recent_enrollments as $enrollment)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="user-avatar me-3">
                            {{ substr($enrollment->student->user->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $enrollment->student->user->name }}</h6>
                            <small class="text-muted">
                                {{ $enrollment->package->name }} - {{ $enrollment->enrollment_date->format('d M Y') }}
                            </small>
                        </div>
                        <span class="badge bg-success">{{ $enrollment->status }}</span>
                    </div>
                @empty
                    <p class="text-muted text-center">No recent enrollments</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-money-bill-wave me-2"></i> Recent Payments</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recent_payments as $payment)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="user-avatar me-3">
                            {{ substr($payment->student->user->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ $payment->student->user->name }}</h6>
                            <small class="text-muted">
                                {{ $payment->payment_date->format('d M Y, h:i A') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <strong class="text-success">RM {{ number_format($payment->amount, 2) }}</strong>
                            <br>
                            <small class="text-muted">{{ $payment->payment_method }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No recent payments</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
