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
            <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Enrollment Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Enrollment Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}">
                        @csrf
                        @method('PUT')

                        <!-- Student Info (Read-only) -->
                        <div class="form-group">
                            <label>Student</label>
                            <input type="text" class="form-control" value="{{ $enrollment->student->user->name }} ({{ $enrollment->student->student_id }})" readonly>
                            <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}">
                        </div>

                        <!-- Package/Class Info (Read-only) -->
                        <div class="form-group">
                            <label>Enrollment Type</label>
                            <input type="text" class="form-control" value="@if($enrollment->package)Package: {{ $enrollment->package->name }}@else Class: {{ $enrollment->class->name }}@endif" readonly>
                            @if($enrollment->package_id)
                                <input type="hidden" name="package_id" value="{{ $enrollment->package_id }}">
                            @endif
                            @if($enrollment->class_id)
                                <input type="hidden" name="class_id" value="{{ $enrollment->class_id }}">
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Start Date -->
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
                                <!-- End Date -->
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

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Payment Cycle Day -->
                                <div class="form-group">
                                    <label for="payment_cycle_day">Payment Cycle Day <span class="text-danger">*</span></label>
                                    <select class="form-control @error('payment_cycle_day') is-invalid @enderror"
                                            id="payment_cycle_day" name="payment_cycle_day" required>
                                        @for($i = 1; $i <= 28; $i++)
                                            <option value="{{ $i }}" {{ old('payment_cycle_day', $enrollment->payment_cycle_day) == $i ? 'selected' : '' }}>
                                                Day {{ $i }} of each month
                                            </option>
                                        @endfor
                                    </select>
                                    @error('payment_cycle_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Monthly Fee -->
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
                        </div>

                        <!-- Fee Change Reason (if fee is changed) -->
                        <div class="form-group" id="fee_change_reason_group" style="display: none;">
                            <label for="fee_change_reason">Reason for Fee Change <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="fee_change_reason" name="fee_change_reason" rows="2"
                                      placeholder="Please provide a reason for changing the monthly fee...">{{ old('fee_change_reason') }}</textarea>
                            <small class="form-text text-muted">This will be recorded in the fee history</small>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $enrollment->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $enrollment->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="expired" {{ old('status', $enrollment->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="cancelled" {{ old('status', $enrollment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="trial" {{ old('status', $enrollment->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cancellation Reason (if status is cancelled) -->
                        @if($enrollment->cancellation_reason)
                        <div class="form-group">
                            <label>Previous Cancellation Reason</label>
                            <textarea class="form-control" readonly rows="2">{{ $enrollment->cancellation_reason }}</textarea>
                        </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Enrollment
                            </button>
                            <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="col-lg-4">
            <!-- Current Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Status</h6>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong>
                        @if($enrollment->status == 'active')
                            <span class="badge badge-success">Active</span>
                        @elseif($enrollment->status == 'suspended')
                            <span class="badge badge-warning">Suspended</span>
                        @elseif($enrollment->status == 'expired')
                            <span class="badge badge-danger">Expired</span>
                        @elseif($enrollment->status == 'cancelled')
                            <span class="badge badge-dark">Cancelled</span>
                        @elseif($enrollment->status == 'trial')
                            <span class="badge badge-info">Trial</span>
                        @endif
                    </p>
                    <p><strong>Enrolled:</strong> {{ $enrollment->created_at->format('d M Y') }}</p>
                    @if($enrollment->end_date)
                        <p><strong>Days Remaining:</strong>
                            @if($enrollment->days_remaining > 0)
                                {{ $enrollment->days_remaining }} days
                            @else
                                <span class="text-danger">Expired</span>
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <!-- Fee History -->
            @if($enrollment->feeHistory->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fee Change History</h6>
                </div>
                <div class="card-body">
                    @foreach($enrollment->feeHistory->take(5) as $history)
                    <div class="mb-2 pb-2 border-bottom">
                        <small class="text-muted">{{ $history->change_date->format('d M Y') }}</small><br>
                        <strong>RM {{ number_format($history->old_fee, 2) }}</strong> →
                        <strong class="text-success">RM {{ number_format($history->new_fee, 2) }}</strong><br>
                        <small>{{ $history->reason }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Important Notes -->
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-body">
                    <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                    <ul class="small mb-0">
                        <li>Changing the monthly fee will be recorded in fee history</li>
                        <li>Start date changes may affect existing invoices</li>
                        <li>Status changes take effect immediately</li>
                        <li>Changing to 'cancelled' will stop future invoices</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const originalFee = {{ $enrollment->monthly_fee }};

    // Show fee change reason field when fee is changed
    $('#monthly_fee').on('change input', function() {
        const newFee = parseFloat($(this).val());
        if (newFee !== originalFee && !isNaN(newFee)) {
            $('#fee_change_reason_group').show();
            $('#fee_change_reason').prop('required', true);
        } else {
            $('#fee_change_reason_group').hide();
            $('#fee_change_reason').prop('required', false);
        }
    });
});
</script>
@endpush
