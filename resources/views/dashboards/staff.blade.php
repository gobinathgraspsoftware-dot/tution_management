@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')

@section('content')
<div class="page-header">
    <h1>
        <i class="fas fa-user-tie me-2"></i> Staff Dashboard
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
            <div class="stat-icon" style="background-color: #f3e5f5; color: #9c27b0;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-details">
                <h3 class="mb-0">{{ $today_sessions }}</h3>
                <p class="text-muted mb-0">Today's Sessions</p>
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
                            <i class="fas fa-user-plus me-2"></i> Register Student
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-success w-100">
                            <i class="fas fa-check-circle me-2"></i> Mark Attendance
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-info w-100">
                            <i class="fas fa-dollar-sign me-2"></i> Process Payment
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-warning w-100">
                            <i class="fas fa-clock me-2"></i> Trial Class
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Schedule & Tasks -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-day me-2"></i> Today's Schedule</span>
                <span class="badge bg-primary">{{ \Carbon\Carbon::now()->format('D, d M Y') }}</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-primary" style="width: 50px; padding: 0.5rem;">09:00 AM</div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Mathematics - Form 5</h6>
                                <small class="text-muted">Class A - Teacher: Ahmad</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-success">Attendance</a>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-success" style="width: 50px; padding: 0.5rem;">11:00 AM</div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Science - Form 4</h6>
                                <small class="text-muted">Class B - Teacher: Sarah</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-success">Attendance</a>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="badge bg-warning" style="width: 50px; padding: 0.5rem;">02:00 PM</div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">English - Form 3</h6>
                                <small class="text-muted">Class C - Teacher: Kumar</small>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-success">Attendance</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tasks me-2"></i> Pending Tasks</span>
                <span class="badge bg-danger">4 Pending</span>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="task1">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Approve 3 pending student registrations</h6>
                            <small class="text-muted">Urgent - Due today</small>
                        </div>
                        <span class="badge bg-danger">Urgent</span>
                    </div>

                    <div class="list-group-item px-0 d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="task2">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Follow up on payment reminders</h6>
                            <small class="text-muted">5 students with arrears</small>
                        </div>
                        <span class="badge bg-warning">Important</span>
                    </div>

                    <div class="list-group-item px-0 d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="task3">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Coordinate trial class for new students</h6>
                            <small class="text-muted">2 trial classes scheduled</small>
                        </div>
                        <span class="badge bg-info">Normal</span>
                    </div>

                    <div class="list-group-item px-0 d-flex align-items-center">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="task4">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">Update attendance records</h6>
                            <small class="text-muted">Last week's sessions</small>
                        </div>
                        <span class="badge bg-secondary">Low</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
