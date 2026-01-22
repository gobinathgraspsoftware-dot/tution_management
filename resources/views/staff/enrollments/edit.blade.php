@extends('layouts.app')

@section('title', 'Edit Enrollment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit"></i> Edit Enrollment
        </h1>
        <div>
            <a href="{{ route('staff.enrollments.show', $enrollment) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('staff.enrollments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Edit Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Enrollment Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('staff.enrollments.update', $enrollment) }}">
                        @csrf
                        @method('PUT')

                        <!-- Student Info (Read Only) -->
                        <div class="form-group">
                            <label>Student</label>
                            <div class="form-control bg-light" readonly>
                                <strong>{{ $enrollment->student->user->name }}</strong>
                                <span class="text-muted">({{ $enrollment->student->student_id }})</span>
                            </div>
                        </div>

                        <!-- Package/Class Info (Read Only) -->
                        <div class="form-group">
                            <label>Enrollment Type</label>
                            <div class="form-control bg-light" readonly>
                                @if($enrollment->package)
                                    <span class="badge badge-info">Package</span>
                                    {{ $enrollment->package->name }}
                                @elseif($enrollment->class)
                                    <span class="badge badge-secondary">Class</span>
                                    {{ $enrollment->class->name }} - {{ $enrollment->class->subject->name ?? '' }}
                                @endif
                            </div>
                        </div>

                        <hr>

                        <!-- Editable Fields -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="monthly_fee">Monthly Fee (RM) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                                           id="monthly_fee" name="monthly_fee"
                                           value="{{ old('monthly_fee', $enrollment->monthly_fee) }}" required>
                                    @error('monthly_fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_cycle_day">Payment Cycle Day <span class="text-danger">*</span></label>
                                    <select class="form-control @error('payment_cycle_day') is-invalid @enderror"
                                            id="payment_cycle_day" name="payment_cycle_day" required>
                                        @for($i = 1; $i <= 28; $i++)
                                            <option value="{{ $i }}" {{ old('payment_cycle_day', $enrollment->payment_cycle_day) == $i ? 'selected' : '' }}>
                                                Day {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('payment_cycle_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                           id="start_date" name="start_date"
                                           value="{{ old('start_date', $enrollment->start_date->format('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                           id="end_date" name="end_date"
                                           value="{{ old('end_date', $enrollment->end_date ? $enrollment->end_date->format('Y-m-d') : '') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave empty for ongoing enrollment</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $enrollment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ old('status', $enrollment->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                                <option value="suspended" {{ old('status', $enrollment->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="expired" {{ old('status', $enrollment->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="cancelled" {{ old('status', $enrollment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes/Reason for Change</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Enter any notes about this change...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Enrollment
                            </button>
                            <a href="{{ route('staff.enrollments.show', $enrollment) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong><br>
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

                    <div class="mb-3">
                        <strong>Current Fee:</strong><br>
                        <span class="h5 text-success">RM {{ number_format($enrollment->monthly_fee, 2) }}</span>
                    </div>

                    @if($enrollment->end_date)
                    <div class="mb-3">
                        <strong>Days Remaining:</strong><br>
                        @if($enrollment->days_remaining !== null)
                            @if($enrollment->days_remaining > 30)
                                <span class="text-success">{{ $enrollment->days_remaining }} days</span>
                            @elseif($enrollment->days_remaining > 0)
                                <span class="text-warning">{{ $enrollment->days_remaining }} days</span>
                            @else
                                <span class="text-danger">Expired</span>
                            @endif
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>Enrolled On:</strong><br>
                        {{ $enrollment->created_at->format('d M Y g:i A') }}
                    </div>

                    @if($enrollment->cancelled_at)
                    <div class="mb-3">
                        <strong>Cancelled On:</strong><br>
                        <span class="text-danger">{{ $enrollment->cancelled_at->format('d M Y g:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Fee History Card -->
            @if($enrollment->feeHistory && $enrollment->feeHistory->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fee Change History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Old</th>
                                    <th>New</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollment->feeHistory->sortByDesc('created_at')->take(5) as $history)
                                <tr>
                                    <td><small>{{ $history->created_at->format('d/m/y') }}</small></td>
                                    <td><small>RM {{ number_format($history->old_fee, 2) }}</small></td>
                                    <td><small>RM {{ number_format($history->new_fee, 2) }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-body">
                    <h6 class="text-warning">Quick Actions</h6>
                    <p class="small text-muted">Use these actions to quickly change enrollment status:</p>

                    @if($enrollment->status == 'active')
                        @can('suspend-enrollments')
                        <button type="button" class="btn btn-warning btn-sm btn-block mb-2" data-toggle="modal" data-target="#suspendModal">
                            <i class="fas fa-pause"></i> Suspend Enrollment
                        </button>
                        @endcan
                    @elseif($enrollment->status == 'suspended')
                        @can('activate-enrollments')
                        <form action="{{ route('staff.enrollments.resume', $enrollment) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-play"></i> Resume Enrollment
                            </button>
                        </form>
                        @endcan
                    @endif

                    @if(in_array($enrollment->status, ['active', 'suspended', 'expired']))
                        @can('edit-enrollments')
                        <button type="button" class="btn btn-success btn-sm btn-block mb-2" data-toggle="modal" data-target="#renewModal">
                            <i class="fas fa-redo"></i> Renew Enrollment
                        </button>
                        @endcan
                    @endif

                    @if($enrollment->status != 'cancelled')
                        @can('cancel-enrollments')
                        <button type="button" class="btn btn-danger btn-sm btn-block" data-toggle="modal" data-target="#cancelModal">
                            <i class="fas fa-times"></i> Cancel Enrollment
                        </button>
                        @endcan
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
                        <strong>Warning:</strong> Cancelling this enrollment will stop all future invoices.
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
