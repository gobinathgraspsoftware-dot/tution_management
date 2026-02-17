@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Enrollment Details</h4>
                <div class="page-title-right">
                    <a href="{{ route('student.enrollments.my-enrollments') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Class Information --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book-reader me-2"></i>Class Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Class Name</label>
                            <h5>{{ $enrollment->class->name ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Subject</label>
                            <h5>
                                @if($enrollment->class && $enrollment->class->subject)
                                    <span class="badge bg-soft-primary text-primary fs-6">
                                        {{ $enrollment->class->subject->name }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Teacher</label>
                            <h5>
                                @if($enrollment->class && $enrollment->class->teacher)
                                    {{ $enrollment->class->teacher->user->name ?? 'N/A' }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Class Type</label>
                            <h5>
                                @if($enrollment->class)
                                    <span class="badge bg-soft-info text-info">
                                        {{ ucfirst($enrollment->class->type) }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </h5>
                        </div>
                    </div>

                    @if($enrollment->package)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-muted">Package</label>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-box me-2"></i>
                                    <strong>{{ $enrollment->package->name }}</strong>
                                    <p class="mb-0 mt-2 small">{{ $enrollment->package->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($enrollment->class && $enrollment->class->schedules->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <label class="text-muted mb-2">Class Schedule</label>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Day</th>
                                                <th>Time</th>
                                                <th>Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($enrollment->class->schedules as $schedule)
                                                <tr>
                                                    <td>{{ ucfirst($schedule->day_of_week) }}</td>
                                                    <td>
                                                        {{ date('g:i A', strtotime($schedule->start_time)) }} -
                                                        {{ date('g:i A', strtotime($schedule->end_time)) }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $start = \Carbon\Carbon::parse($schedule->start_time);
                                                            $end = \Carbon\Carbon::parse($schedule->end_time);
                                                            $duration = $start->diffInMinutes($end);
                                                        @endphp
                                                        {{ $duration }} minutes
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Attendance Summary --}}
            @if($attendanceSummary && $attendanceSummary->total_sessions > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Attendance Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="p-3 bg-soft-primary rounded">
                                    <h4 class="mb-1 text-primary">{{ $attendanceSummary->total_sessions }}</h4>
                                    <p class="text-muted mb-0 small">Total Sessions</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 bg-soft-success rounded">
                                    <h4 class="mb-1 text-success">{{ $attendanceSummary->present_count }}</h4>
                                    <p class="text-muted mb-0 small">Present</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 bg-soft-danger rounded">
                                    <h4 class="mb-1 text-danger">{{ $attendanceSummary->absent_count }}</h4>
                                    <p class="text-muted mb-0 small">Absent</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-3 bg-soft-warning rounded">
                                    <h4 class="mb-1 text-warning">{{ $attendanceSummary->late_count }}</h4>
                                    <p class="text-muted mb-0 small">Late</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            @php
                                $attendanceRate = $attendanceSummary->total_sessions > 0
                                    ? round(($attendanceSummary->present_count / $attendanceSummary->total_sessions) * 100, 1)
                                    : 0;
                                $progressColor = $attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger');
                            @endphp
                            <label class="text-muted mb-2">Attendance Rate</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progressColor }}"
                                     role="progressbar"
                                     style="width: {{ $attendanceRate }}%;"
                                     aria-valuenow="{{ $attendanceRate }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    {{ $attendanceRate }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Recent Invoices --}}
            @if($enrollment->invoices && $enrollment->invoices->isNotEmpty())
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice me-2"></i>Recent Invoices
                        </h5>
                        <a href="{{ route('student.invoices.index') }}" class="btn btn-sm btn-primary">
                            View All Invoices
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrollment->invoices as $invoice)
                                        <tr>
                                            <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                            <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                            <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'paid' => 'success',
                                                        'pending' => 'warning',
                                                        'overdue' => 'danger',
                                                        'cancelled' => 'secondary',
                                                    ];
                                                    $color = $statusColors[$invoice->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(Route::has('student.invoices.show'))
                                                    <a href="{{ route('student.invoices.show', $invoice) }}"
                                                       class="btn btn-sm btn-soft-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Enrollment Status Card --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Enrollment Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        @php
                            $statusColors = [
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                'cancelled' => 'secondary',
                                'completed' => 'info',
                            ];
                            $color = $statusColors[$enrollment->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Enrollment Date</label>
                        <p class="mb-0">{{ $enrollment->start_date ? \Carbon\Carbon::parse($enrollment->start_date)->format('d M Y') : 'N/A' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Monthly Fee</label>
                        <h4 class="mb-0 text-primary">RM {{ number_format($enrollment->monthly_fee, 2) }}</h4>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Payment Cycle Day</label>
                        <p class="mb-0">{{ $enrollment->payment_cycle_day }}{{ $enrollment->payment_cycle_day == 1 ? 'st' : ($enrollment->payment_cycle_day == 2 ? 'nd' : ($enrollment->payment_cycle_day == 3 ? 'rd' : 'th')) }} of each month</p>
                    </div>

                    @if($enrollment->status_change_reason)
                        <div class="alert alert-info">
                            <label class="text-muted small">Status Reason</label>
                            <p class="mb-0">{{ $enrollment->status_change_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(Route::has('student.classes.show') && $enrollment->class)
                            <a href="{{ route('student.classes.show', $enrollment->class->id) }}" class="btn btn-primary">
                                <i class="fas fa-chalkboard-teacher me-2"></i> View Class Details
                            </a>
                        @endif

                        @if(Route::has('student.attendance.index'))
                            <a href="{{ route('student.attendance.index') }}" class="btn btn-success">
                                <i class="fas fa-calendar-check me-2"></i> View Attendance
                            </a>
                        @endif

                        @if(Route::has('student.invoices.index'))
                            <a href="{{ route('student.invoices.index') }}" class="btn btn-info">
                                <i class="fas fa-file-invoice me-2"></i> View Invoices
                            </a>
                        @endif

                        @if(Route::has('student.materials.index'))
                            <a href="{{ route('student.materials.index') }}" class="btn btn-warning">
                                <i class="fas fa-book me-2"></i> Learning Materials
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
