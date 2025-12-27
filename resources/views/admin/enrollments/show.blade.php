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
            <a href="{{ route('admin.enrollments.edit', $enrollment) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcan
            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
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
                        <span class="badge bg-success">Active</span>
                    @elseif($enrollment->status == 'suspended')
                        <span class="badge bg-warning">Suspended</span>
                    @elseif($enrollment->status == 'expired')
                        <span class="badge bg-danger">Expired</span>
                    @elseif($enrollment->status == 'cancelled')
                        <span class="badge bg-dark">Cancelled</span>
                    @elseif($enrollment->status == 'trial')
                        <span class="badge bg-info">Trial</span>
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
                                    <span class="badge bg-info">Package</span><br>
                                    <strong>{{ $enrollment->package->name }}</strong>
                                </p>
                                <p class="mb-1"><strong>Duration:</strong> {{ $enrollment->package->duration_months }} months</p>
                                <p class="mb-1"><strong>Package Type:</strong> {{ ucfirst($enrollment->package->type) }}</p>
                            @else
                                <p class="mb-1">
                                    <span class="badge bg-secondary">Single Class</span><br>
                                    <strong>{{ $enrollment->class->name }}</strong>
                                </p>
                                <p class="mb-1"><strong>Subject:</strong> {{ $enrollment->class->subject->name }}</p>
                                @if($enrollment->class->teacher)
                                    <p class="mb-1"><strong>Teacher:</strong> {{ $enrollment->class->teacher->user->name }}</p>
                                @endif
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

                    @if($enrollment->cancellation_reason)
                    <hr>
                    <div class="alert alert-warning mb-0">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Cancellation Reason:</strong><br>
                        {{ $enrollment->cancellation_reason }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Package Classes (for package enrollments) -->
            @if($enrollment->package && isset($relatedEnrollments) && (count($relatedEnrollments) > 0 || $enrollment->class))
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book-open me-2"></i>Package Classes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Teacher</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Current enrollment's class -->
                                @if($enrollment->class)
                                <tr class="table-primary">
                                    <td>
                                        <i class="fas fa-book text-primary me-1"></i>
                                        {{ $enrollment->class->subject->name }}
                                    </td>
                                    <td>
                                        <strong>{{ $enrollment->class->name }}</strong>
                                        <span class="badge bg-primary ms-1">Current</span>
                                    </td>
                                    <td>
                                        @if($enrollment->class->teacher)
                                            {{ $enrollment->class->teacher->user->name }}
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $enrollment->class->type == 'online' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($enrollment->class->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($enrollment->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($enrollment->status == 'suspended')
                                            <span class="badge bg-warning">Suspended</span>
                                        @elseif($enrollment->status == 'expired')
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($enrollment->status == 'cancelled')
                                            <span class="badge bg-dark">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($enrollment->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif

                                <!-- Related enrollments (other classes in same package) -->
                                @foreach($relatedEnrollments as $related)
                                <tr>
                                    <td>
                                        <i class="fas fa-book text-muted me-1"></i>
                                        {{ $related->class->subject->name ?? '-' }}
                                    </td>
                                    <td>{{ $related->class->name ?? '-' }}</td>
                                    <td>
                                        @if($related->class && $related->class->teacher)
                                            {{ $related->class->teacher->user->name }}
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($related->class)
                                            <span class="badge bg-{{ $related->class->type == 'online' ? 'info' : 'secondary' }}">
                                                {{ ucfirst($related->class->type) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($related->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($related->status == 'suspended')
                                            <span class="badge bg-warning">Suspended</span>
                                        @elseif($related->status == 'expired')
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($related->status == 'cancelled')
                                            <span class="badge bg-dark">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($related->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Total {{ 1 + count($relatedEnrollments) }} classes enrolled in this package
                    </div>
                </div>
            </div>
            @endif

            <!-- Class Schedule (for single class enrollment) -->
            @if(!$enrollment->package && $enrollment->class_id)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Class Schedule</h6>
                </div>
                <div class="card-body">
                    @if($enrollment->class->schedules && $enrollment->class->schedules->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
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
                    @else
                        <p class="text-muted mb-0">No schedule available</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Invoices -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Invoice History</h6>
                </div>
                <div class="card-body">
                    @if($enrollment->invoices->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
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
                                    @foreach($enrollment->invoices->sortByDesc('created_at') as $invoice)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.invoices.show', $invoice) }}">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ $invoice->billing_period ?? '-' }}</td>
                                        <td>RM {{ number_format($invoice->total_amount, 2) }}</td>
                                        <td>RM {{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td>
                                            @if($invoice->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($invoice->status == 'partial')
                                                <span class="badge bg-warning">Partial</span>
                                            @elseif($invoice->status == 'pending')
                                                <span class="badge bg-secondary">Pending</span>
                                            @elseif($invoice->status == 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @else
                                                <span class="badge bg-dark">{{ ucfirst($invoice->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No invoices yet</p>
                    @endif
                </div>
            </div>

            <!-- Fee History -->
            @if($enrollment->feeHistory && $enrollment->feeHistory->isNotEmpty())
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fee Change History</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Old Fee</th>
                                    <th>New Fee</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->feeHistory->sortByDesc('change_date') as $history)
                                <tr>
                                    <td>{{ $history->change_date->format('d M Y') }}</td>
                                    <td>RM {{ number_format($history->old_fee, 2) }}</td>
                                    <td class="text-success">RM {{ number_format($history->new_fee, 2) }}</td>
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

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            @can('cancel-enrollments')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($enrollment->status == 'active')
                        <button type="button" class="btn btn-warning w-100 mb-2" onclick="suspendEnrollment()">
                            <i class="fas fa-pause me-2"></i>Suspend Enrollment
                        </button>
                        <button type="button" class="btn btn-danger w-100 mb-2" onclick="cancelEnrollment()">
                            <i class="fas fa-times me-2"></i>Cancel Enrollment
                        </button>
                        <button type="button" class="btn btn-success w-100" onclick="renewEnrollment()">
                            <i class="fas fa-redo me-2"></i>Renew Enrollment
                        </button>
                    @elseif($enrollment->status == 'suspended')
                        <form action="{{ route('admin.enrollments.resume', $enrollment) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-play me-2"></i>Resume Enrollment
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger w-100" onclick="cancelEnrollment()">
                            <i class="fas fa-times me-2"></i>Cancel Enrollment
                        </button>
                    @elseif($enrollment->status == 'expired')
                        <button type="button" class="btn btn-success w-100" onclick="renewEnrollment()">
                            <i class="fas fa-redo me-2"></i>Renew Enrollment
                        </button>
                    @endif
                </div>
            </div>
            @endcan

            <!-- Statistics -->
            <div class="card shadow mb-4 border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-primary mb-3"><i class="fas fa-chart-bar me-2"></i>Enrollment Statistics</h6>
                    <p class="mb-2"><strong>Total Invoices:</strong> {{ $enrollment->invoices->count() }}</p>
                    <p class="mb-2"><strong>Paid Invoices:</strong> {{ $enrollment->invoices->where('status', 'paid')->count() }}</p>
                    <p class="mb-2"><strong>Outstanding:</strong>
                        <span class="{{ $enrollment->invoices->where('status', '!=', 'paid')->sum('total_amount') - $enrollment->invoices->sum('paid_amount') > 0 ? 'text-danger' : 'text-success' }}">
                            RM {{ number_format(max(0, $enrollment->invoices->where('status', '!=', 'paid')->sum('total_amount') - $enrollment->invoices->where('status', '!=', 'paid')->sum('paid_amount')), 2) }}
                        </span>
                    </p>
                    @if(isset($attendanceSummary) && $attendanceSummary && $attendanceSummary->total_sessions > 0)
                        <p class="mb-0"><strong>Attendance Rate:</strong>
                            <span class="{{ ($attendanceSummary->present_count / $attendanceSummary->total_sessions) * 100 >= 80 ? 'text-success' : 'text-warning' }}">
                                {{ number_format(($attendanceSummary->present_count / $attendanceSummary->total_sessions) * 100, 1) }}%
                            </span>
                            <small class="text-muted">({{ $attendanceSummary->present_count }}/{{ $attendanceSummary->total_sessions }} sessions)</small>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Package Info (if applicable) -->
            @if($enrollment->package)
            <div class="card shadow mb-4 border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-info mb-3"><i class="fas fa-box me-2"></i>Package Information</h6>
                    <p class="mb-2"><strong>Package:</strong> {{ $enrollment->package->name }}</p>
                    <p class="mb-2"><strong>Duration:</strong> {{ $enrollment->package->duration_months }} months</p>
                    <p class="mb-2"><strong>Package Price:</strong> RM {{ number_format($enrollment->package->price, 2) }}/month</p>
                    <p class="mb-2"><strong>Subjects Included:</strong></p>
                    <ul class="mb-0 ps-3">
                        @foreach($enrollment->package->subjects as $subject)
                            <li>{{ $subject->name }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.enrollments.suspend', $enrollment) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Suspension</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            <form action="{{ route('admin.enrollments.cancel', $enrollment) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Cancel Enrollment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> Cancelling this enrollment will stop all future invoices and revoke material access.
                    </div>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="cancellation_reason" rows="3" required
                                  placeholder="Enter reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            <form action="{{ route('admin.enrollments.renew', $enrollment) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Renew Enrollment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="months" class="form-label">Extension Duration (Months)</label>
                        <select class="form-select" name="months">
                            <option value="">Default (Package Duration)</option>
                            <option value="1">1 Month</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                        <small class="text-muted">Leave default to use package duration</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    new bootstrap.Modal(document.getElementById('suspendModal')).show();
}

function cancelEnrollment() {
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function renewEnrollment() {
    new bootstrap.Modal(document.getElementById('renewModal')).show();
}
</script>
@endpush
