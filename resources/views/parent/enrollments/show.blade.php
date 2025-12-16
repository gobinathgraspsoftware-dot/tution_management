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
            <a href="{{ route('parent.enrollments.edit', $enrollment) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('parent.enrollments.index') }}" class="btn btn-secondary">
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
                    @if($enrollment->status == 'active')
                        <span class="badge badge-success badge-pill">Active</span>
                    @elseif($enrollment->status == 'suspended')
                        <span class="badge badge-warning badge-pill">Suspended</span>
                    @elseif($enrollment->status == 'expired')
                        <span class="badge badge-danger badge-pill">Expired</span>
                    @elseif($enrollment->status == 'cancelled')
                        <span class="badge badge-dark badge-pill">Cancelled</span>
                    @elseif($enrollment->status == 'trial')
                        <span class="badge badge-info badge-pill">Trial</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Student Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $enrollment->student->user->name }}</p>
                            <p class="mb-1"><strong>Student ID:</strong> {{ $enrollment->student->student_id }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $enrollment->student->user->email }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $enrollment->student->user->phone ?? '-' }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Enrollment Type</h6>
                            @if($enrollment->package)
                                <p class="mb-1">
                                    <span class="badge badge-info">Package</span><br>
                                    <strong>{{ $enrollment->package->name }}</strong>
                                </p>
                                <p class="mb-1"><strong>Duration:</strong> {{ $enrollment->package->duration_months }} months</p>
                                <p class="mb-1"><strong>Subjects:</strong></p>
                                <ul class="small mb-0">
                                    @foreach($enrollment->package->subjects as $subject)
                                        <li>{{ $subject->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mb-1">
                                    <span class="badge badge-secondary">Single Class</span><br>
                                    <strong>{{ $enrollment->class->name }}</strong>
                                </p>
                                <p class="mb-1"><strong>Subject:</strong> {{ $enrollment->class->subject->name }}</p>
                                <p class="mb-1"><strong>Teacher:</strong> {{ $enrollment->class->teacher->user->name }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">Duration</h6>
                            <p class="mb-1"><strong>Start Date:</strong><br>{{ $enrollment->start_date->format('d M Y') }}</p>
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
                                <p class="mb-1"><strong>Cancelled On:</strong><br>{{ $enrollment->cancelled_at->format('d M Y g:i A') }}</p>
                            @endif
                            <p class="mb-1"><strong>Last Updated:</strong><br>{{ $enrollment->updated_at->format('d M Y g:i A') }}</p>
                        </div>
                    </div>

                    @if($enrollment->class_id)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary">Class Schedule</h6>
                            @if($enrollment->class->schedules->isNotEmpty())
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
                                                <td>{{ $schedule->day_of_week }}</td>
                                                <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</td>
                                                <td>{{ $schedule->venue ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No schedule available</p>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($enrollment->cancellation_reason)
                    <hr>
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-info-circle"></i> Cancellation Reason:</strong><br>
                        {{ $enrollment->cancellation_reason }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Attendance Summary -->
            @if($attendanceSummary && $attendanceSummary->total_sessions > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary">{{ $attendanceSummary->total_sessions }}</h4>
                            <small class="text-muted">Total Sessions</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">{{ $attendanceSummary->present_count }}</h4>
                            <small class="text-muted">Present</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-danger">{{ $attendanceSummary->absent_count }}</h4>
                            <small class="text-muted">Absent</small>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning">{{ $attendanceSummary->late_count }}</h4>
                            <small class="text-muted">Late</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5>Attendance Rate:
                            @php
                                $rate = $attendanceSummary->total_sessions > 0 ? ($attendanceSummary->present_count / $attendanceSummary->total_sessions) * 100 : 0;
                            @endphp
                            <span class="@if($rate >= 80) text-success @elseif($rate >= 60) text-warning @else text-danger @endif">
                                {{ number_format($rate, 1) }}%
                            </span>
                        </h5>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $rate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Invoices -->
            @if($enrollment->invoices->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->invoices as $invoice)
                                <tr>
                                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                    <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                    <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                    <td>RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td>
                                        @if($invoice->status == 'paid')
                                            <span class="badge badge-success">Paid</span>
                                        @elseif($invoice->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($invoice->status == 'overdue')
                                            <span class="badge badge-danger">Overdue</span>
                                        @elseif($invoice->status == 'cancelled')
                                            <span class="badge badge-secondary">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(Route::has('admin.invoices.show'))
                                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-info">
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

            <!-- Fee Change History -->
            @if($enrollment->feeHistory->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fee Change History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Old Fee</th>
                                    <th>New Fee</th>
                                    <th>Changed By</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->feeHistory as $history)
                                <tr>
                                    <td>{{ $history->change_date->format('d M Y') }}</td>
                                    <td>RM {{ number_format($history->old_fee, 2) }}</td>
                                    <td>RM {{ number_format($history->new_fee, 2) }}</td>
                                    <td>{{ $history->changedBy->name ?? 'System' }}</td>
                                    <td>{{ $history->reason }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
                <div class="card-header py-3">
                <div class="card-body">
                    <h6 class="text-primary">Enrollment Statistics</h6>
                    <p class="mb-2"><strong>Total Invoices:</strong> {{ $enrollment->invoices->count() }}</p>
                    <p class="mb-2"><strong>Paid Invoices:</strong> {{ $enrollment->invoices->where('status', 'paid')->count() }}</p>
                    <p class="mb-2"><strong>Outstanding:</strong>
                        RM {{ number_format($enrollment->invoices->where('status', '!=', 'paid')->sum('total_amount') - $enrollment->invoices->sum('paid_amount'), 2) }}
                    </p>
                    @if($attendanceSummary && $attendanceSummary->total_sessions > 0)
                        <p class="mb-0"><strong>Attendance Rate:</strong>
                            {{ number_format(($attendanceSummary->present_count / $attendanceSummary->total_sessions) * 100, 1) }}%
                        </p>
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
            <form action="{{ route('parent.enrollments.suspend', $enrollment) }}" method="POST">
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
            <form action="{{ route('parent.enrollments.cancel', $enrollment) }}" method="POST">
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
            <form action="{{ route('parent.enrollments.renew', $enrollment) }}" method="POST">
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
