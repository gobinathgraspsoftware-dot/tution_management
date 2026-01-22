@extends('layouts.app')

@section('title', 'Enrollment Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-graduate"></i> Enrollment Details
        </h1>
        <div>
            @can('edit-enrollments')
            <a href="{{ route('staff.enrollments.edit', $enrollment) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('staff.enrollments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Enrollment Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Enrollment Information</h6>
                    @switch($enrollment->status)
                        @case('active')
                            <span class="badge badge-success badge-pill">Active</span>
                            @break
                        @case('suspended')
                            <span class="badge badge-warning badge-pill">Suspended</span>
                            @break
                        @case('expired')
                            <span class="badge badge-danger badge-pill">Expired</span>
                            @break
                        @case('cancelled')
                            <span class="badge badge-dark badge-pill">Cancelled</span>
                            @break
                        @case('trial')
                            <span class="badge badge-info badge-pill">Trial</span>
                            @break
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Student Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $enrollment->student->user->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Student ID:</strong> {{ $enrollment->student->student_id ?? '-' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $enrollment->student->user->email ?? '-' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $enrollment->student->user->phone ?? '-' }}</p>
                            @if($enrollment->student->parent)
                            <p class="mb-1"><strong>Parent:</strong> {{ $enrollment->student->parent->user->name ?? '-' }}</p>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Enrollment Type</h6>
                            @if($enrollment->package)
                                <p class="mb-1">
                                    <span class="badge badge-info">Package</span><br>
                                    <strong>{{ $enrollment->package->name }}</strong>
                                </p>
                                <p class="mb-1"><strong>Duration:</strong> {{ $enrollment->package->duration_months }} months</p>
                                @if($enrollment->package->subjects && $enrollment->package->subjects->count() > 0)
                                <p class="mb-1"><strong>Subjects:</strong></p>
                                <ul class="small mb-0">
                                    @foreach($enrollment->package->subjects as $subject)
                                        <li>{{ $subject->name }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            @elseif($enrollment->class)
                                <p class="mb-1">
                                    <span class="badge badge-secondary">Single Class</span><br>
                                    <strong>{{ $enrollment->class->name }}</strong>
                                </p>
                                <p class="mb-1"><strong>Subject:</strong> {{ $enrollment->class->subject->name ?? '-' }}</p>
                                @if($enrollment->class->teacher)
                                <p class="mb-1"><strong>Teacher:</strong> {{ $enrollment->class->teacher->user->name ?? '-' }}</p>
                                @endif
                            @else
                                <p class="text-muted">No package or class assigned</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">Duration</h6>
                            <p class="mb-1"><strong>Start Date:</strong><br>{{ $enrollment->start_date ? $enrollment->start_date->format('d M Y') : '-' }}</p>
                            <p class="mb-1"><strong>End Date:</strong><br>
                                @if($enrollment->end_date)
                                    {{ $enrollment->end_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">Ongoing</span>
                                @endif
                            </p>
                            @if($enrollment->end_date && $enrollment->days_remaining !== null)
                                <p class="mb-1"><strong>Days Remaining:</strong><br>
                                    @if($enrollment->days_remaining > 30)
                                        <span class="text-success">{{ $enrollment->days_remaining }} days</span>
                                    @elseif($enrollment->days_remaining > 0)
                                        <span class="text-warning">{{ $enrollment->days_remaining }} days</span>
                                    @else
                                        <span class="text-danger">Expired</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">Payment Details</h6>
                            <p class="mb-1"><strong>Monthly Fee:</strong><br>
                                <span class="h5 text-success">RM {{ number_format($enrollment->monthly_fee, 2) }}</span>
                            </p>
                            <p class="mb-1"><strong>Payment Cycle:</strong><br>
                                Day {{ $enrollment->payment_cycle_day }} of each month
                            </p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">Timeline</h6>
                            <p class="mb-1"><strong>Enrolled On:</strong><br>{{ $enrollment->created_at->format('d M Y g:i A') }}</p>
                            @if($enrollment->cancelled_at)
                                <p class="mb-1"><strong>Cancelled On:</strong><br>
                                    <span class="text-danger">{{ $enrollment->cancelled_at->format('d M Y g:i A') }}</span>
                                </p>
                            @endif
                            <p class="mb-1"><strong>Last Updated:</strong><br>{{ $enrollment->updated_at->format('d M Y g:i A') }}</p>
                        </div>
                    </div>

                    @if($enrollment->cancellation_reason)
                    <hr>
                    <div class="alert alert-danger">
                        <strong>Cancellation Reason:</strong> {{ $enrollment->cancellation_reason }}
                    </div>
                    @endif

                    @if($enrollment->class && $enrollment->class->schedules && $enrollment->class->schedules->isNotEmpty())
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary">Class Schedule</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Venue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($enrollment->class->schedules as $schedule)
                                        <tr>
                                            <td>{{ ucfirst($schedule->day_of_week) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</td>
                                            <td>{{ $schedule->venue ?? '-' }}</td>
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

            <!-- Invoices Card -->
            @if($enrollment->invoices && $enrollment->invoices->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice History</h6>
                    <span class="badge badge-primary">{{ $enrollment->invoices->count() }} invoices</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Period</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->invoices->sortByDesc('created_at')->take(10) as $invoice)
                                <tr>
                                    <td>
                                        <a href="#" class="font-weight-bold">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $invoice->billing_start ? $invoice->billing_start->format('d M') : '-' }} -
                                            {{ $invoice->billing_end ? $invoice->billing_end->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td>
                                        @switch($invoice->status)
                                            @case('paid')
                                                <span class="badge badge-success">Paid</span>
                                                @break
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge badge-danger">Overdue</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-dark">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ ucfirst($invoice->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Fee History Card -->
            @if($enrollment->feeHistory && $enrollment->feeHistory->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fee Change History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Old Fee</th>
                                    <th>New Fee</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->feeHistory->sortByDesc('created_at') as $history)
                                <tr>
                                    <td>{{ $history->created_at->format('d M Y g:i A') }}</td>
                                    <td>RM {{ number_format($history->old_fee, 2) }}</td>
                                    <td>RM {{ number_format($history->new_fee, 2) }}</td>
                                    <td>{{ $history->reason ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            @can('edit-enrollments')
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($enrollment->status == 'active')
                        @can('suspend-enrollments')
                        <button type="button" class="btn btn-warning btn-block mb-2" onclick="suspendEnrollment()">
                            <i class="fas fa-pause"></i> Suspend Enrollment
                        </button>
                        @endcan
                        @can('cancel-enrollments')
                        <button type="button" class="btn btn-danger btn-block mb-2" onclick="cancelEnrollment()">
                            <i class="fas fa-times"></i> Cancel Enrollment
                        </button>
                        @endcan
                        <button type="button" class="btn btn-success btn-block" onclick="renewEnrollment()">
                            <i class="fas fa-redo"></i> Renew Enrollment
                        </button>
                    @elseif($enrollment->status == 'suspended')
                        @can('activate-enrollments')
                        <form action="{{ route('staff.enrollments.resume', $enrollment) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-play"></i> Resume Enrollment
                            </button>
                        </form>
                        @endcan
                        @can('cancel-enrollments')
                        <button type="button" class="btn btn-danger btn-block" onclick="cancelEnrollment()">
                            <i class="fas fa-times"></i> Cancel Enrollment
                        </button>
                        @endcan
                    @elseif($enrollment->status == 'expired')
                        <button type="button" class="btn btn-success btn-block" onclick="renewEnrollment()">
                            <i class="fas fa-redo"></i> Renew Enrollment
                        </button>
                    @elseif($enrollment->status == 'cancelled')
                        <div class="alert alert-secondary mb-0">
                            <i class="fas fa-info-circle"></i> This enrollment has been cancelled.
                        </div>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Statistics Card -->
            <div class="card shadow mb-4 border-left-info">
                <div class="card-body">
                    <h6 class="text-info">Enrollment Statistics</h6>
                    <p class="mb-2"><strong>Total Invoices:</strong> {{ $enrollment->invoices ? $enrollment->invoices->count() : 0 }}</p>
                    <p class="mb-2"><strong>Paid Invoices:</strong> {{ $enrollment->invoices ? $enrollment->invoices->where('status', 'paid')->count() : 0 }}</p>
                    <p class="mb-2"><strong>Outstanding:</strong>
                        @php
                            $outstanding = $enrollment->invoices
                                ? $enrollment->invoices->whereIn('status', ['pending', 'overdue'])->sum('total_amount')
                                  - $enrollment->invoices->whereIn('status', ['pending', 'overdue'])->sum('paid_amount')
                                : 0;
                        @endphp
                        RM {{ number_format($outstanding, 2) }}
                    </p>
                    @if($attendanceSummary && isset($attendanceSummary->total_sessions) && $attendanceSummary->total_sessions > 0)
                        <p class="mb-0"><strong>Attendance Rate:</strong>
                            {{ number_format(($attendanceSummary->present_count / $attendanceSummary->total_sessions) * 100, 1) }}%
                        </p>
                    @endif
                </div>
            </div>

            <!-- Student Enrollments Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Other Enrollments</h6>
                </div>
                <div class="card-body">
                    @php
                        $otherEnrollments = $enrollment->student->enrollments()
                            ->where('id', '!=', $enrollment->id)
                            ->with(['package', 'class.subject'])
                            ->latest()
                            ->limit(5)
                            ->get();
                    @endphp
                    @if($otherEnrollments->count() > 0)
                        <ul class="list-unstyled mb-0">
                            @foreach($otherEnrollments as $otherEnrollment)
                            <li class="mb-2 pb-2 border-bottom">
                                <a href="{{ route('staff.enrollments.show', $otherEnrollment) }}">
                                    @if($otherEnrollment->package)
                                        {{ $otherEnrollment->package->name }}
                                    @elseif($otherEnrollment->class)
                                        {{ $otherEnrollment->class->name }}
                                    @endif
                                </a>
                                <br>
                                <small class="text-muted">
                                    @switch($otherEnrollment->status)
                                        @case('active')
                                            <span class="badge badge-success">Active</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge badge-warning">Suspended</span>
                                            @break
                                        @case('expired')
                                            <span class="badge badge-danger">Expired</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-dark">Cancelled</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ ucfirst($otherEnrollment->status) }}</span>
                                    @endswitch
                                </small>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No other enrollments</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff.enrollments.suspend', $enrollment) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Enrollment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Reason for Suspension</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff.enrollments.cancel', $enrollment) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Cancel Enrollment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> Cancelling this enrollment will stop all future invoices and revoke material access.
                    </div>
                    <div class="form-group">
                        <label for="cancellation_reason">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="cancellation_reason" rows="3" required
                                  placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff.enrollments.renew', $enrollment) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Renew Enrollment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="months">Extension Duration (Months)</label>
                        <select class="form-control" name="months">
                            <option value="">Default (Package Duration)</option>
                            <option value="1">1 Month</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                        <small class="form-text text-muted">Leave default to use package duration</small>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="generate_invoice" name="generate_invoice" value="1" checked>
                        <label class="custom-control-label" for="generate_invoice">Generate renewal invoice</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Renew Enrollment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function suspendEnrollment() {
    $('#suspendModal').modal('show');
}

function cancelEnrollment() {
    $('#cancelModal').modal('show');
}

function renewEnrollment() {
    $('#renewModal').modal('show');
}
</script>
@endpush
