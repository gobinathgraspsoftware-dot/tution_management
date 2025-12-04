@extends('layouts.app')

@section('title', 'Parent Dashboard')
@section('page-title', 'Parent Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-friends me-2"></i> Parent Dashboard
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Welcome Message -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0" style="background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);">
            <div class="card-body text-white">
                <h4 class="mb-2">Welcome back, {{ auth()->user()->name }}!</h4>
                <p class="mb-0 opacity-75">Monitor your children's progress and stay updated with their learning journey.</p>
            </div>
        </div>
    </div>
</div>

<!-- My Children Overview -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-child me-2"></i> My Children</h5>
    </div>
    @forelse($children as $child)
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            {{ substr($child->user->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0">{{ $child->user->name }}</h5>
                            <p class="text-muted mb-0">Student ID: {{ $child->student_id }}</p>
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-envelope me-1"></i> {{ $child->user->email }}
                            </p>
                        </div>
                        <span class="badge bg-success">Active</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <h6 class="mb-0">{{ $child->enrollments->count() }}</h6>
                                <small class="text-muted">Classes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <h6 class="mb-0">{{ $child->attendance->where('status', 'present')->count() }}</h6>
                                <small class="text-muted">Attendance</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="mb-2">Enrolled Classes:</h6>
                        @forelse($child->enrollments as $enrollment)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div>
                                    <small class="fw-bold">{{ $enrollment->package->name }}</small>
                                    <br>
                                    <small class="text-muted">{{ $enrollment->class->name ?? 'Not assigned' }}</small>
                                </div>
                                <span class="badge bg-primary">{{ ucfirst($enrollment->status) }}</span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No active enrollments</p>
                        @endforelse
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <a href="#" class="btn btn-sm btn-primary">
                            <i class="fas fa-calendar-check me-1"></i> Attendance
                        </a>
                        <a href="#" class="btn btn-sm btn-success">
                            <i class="fas fa-file-invoice me-1"></i> Payments
                        </a>
                        <a href="#" class="btn btn-sm btn-info">
                            <i class="fas fa-chart-line me-1"></i> Progress
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No children registered yet. Please register your child to get started.
            </div>
        </div>
    @endforelse
</div>

<!-- Financial Overview -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice-dollar me-2"></i> Pending Invoices</span>
                <span class="badge bg-white text-danger">{{ $pending_invoices->count() }}</span>
            </div>
            <div class="card-body">
                @forelse($pending_invoices as $invoice)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $invoice->student->user->name }}</h6>
                            <small class="text-muted">
                                Invoice #{{ $invoice->invoice_number }} • Due: {{ $invoice->due_date->format('d M Y') }}
                            </small>
                            @if($invoice->due_date->isPast())
                                <br><span class="badge bg-danger small">Overdue</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0 text-danger">RM {{ number_format($invoice->total_amount, 2) }}</h6>
                            <a href="#" class="btn btn-sm btn-success mt-1">
                                <i class="fas fa-credit-card"></i> Pay Now
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-success py-4">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <p class="mb-0">All invoices paid! Great job!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i> Recent Payments</span>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($recent_payments as $payment)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $payment->student->user->name }}</h6>
                            <small class="text-muted">
                                {{ $payment->payment_date->format('d M Y, h:i A') }}
                            </small>
                            <br>
                            <span class="badge bg-success small">{{ ucfirst($payment->payment_method) }}</span>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-0 text-success">RM {{ number_format($payment->amount, 2) }}</h6>
                            <a href="#" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-receipt"></i> Receipt
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No payment history yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt me-2"></i> Quick Actions
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="#" class="btn btn-primary w-100">
                            <i class="fas fa-credit-card me-2"></i> Make Payment
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-success w-100">
                            <i class="fas fa-calendar-check me-2"></i> View Attendance
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-info w-100">
                            <i class="fas fa-book me-2"></i> Learning Materials
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-warning w-100">
                            <i class="fas fa-comment-alt me-2"></i> Contact Teacher
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Announcements & Events -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-bullhorn me-2"></i> Latest Announcements</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">Year-End Examination Schedule</h6>
                            <small class="text-muted">2 days ago</small>
                        </div>
                        <p class="mb-1 small">Year-end examinations will be conducted from 15-20 December 2025...</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">School Holiday Notice</h6>
                            <small class="text-muted">1 week ago</small>
                        </div>
                        <p class="mb-1 small">The centre will be closed from 25-31 December 2025 for the holiday season...</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">New Learning Materials Available</h6>
                            <small class="text-muted">2 weeks ago</small>
                        </div>
                        <p class="mb-1 small">New revision materials for Form 5 students are now available in the portal...</p>
                        <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt me-2"></i> Upcoming Events</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="text-center p-2 rounded" style="background-color: #e3f2fd; min-width: 60px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #2196f3;">15</div>
                                    <div style="font-size: 0.75rem; color: #666;">DEC</div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Mid-Term Examination</h6>
                                <p class="mb-0 small text-muted">All levels • 9:00 AM - 12:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="text-center p-2 rounded" style="background-color: #e8f5e9; min-width: 60px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #4caf50;">20</div>
                                    <div style="font-size: 0.75rem; color: #666;">DEC</div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Parent-Teacher Meeting</h6>
                                <p class="mb-0 small text-muted">All parents • 3:00 PM - 6:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="text-center p-2 rounded" style="background-color: #fff3e0; min-width: 60px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #ff9800;">05</div>
                                    <div style="font-size: 0.75rem; color: #666;">JAN</div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">New Term Begins</h6>
                                <p class="mb-0 small text-muted">All students • Regular schedule resumes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
