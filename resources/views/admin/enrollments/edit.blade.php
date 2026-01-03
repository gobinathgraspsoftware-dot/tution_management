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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}" id="enrollmentEditForm">
        @csrf
        @method('PUT')
        
        <!-- Hidden field to indicate enrollment type -->
        <input type="hidden" name="enrollment_type" value="{{ $enrollment->package_id ? 'package' : 'single' }}">

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Student Information (Read-only) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-graduate me-2"></i>Student Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student Name</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $enrollment->student->user->name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student ID</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $enrollment->student->student_id ?? 'N/A' }}" readonly>
                            </div>
                        </div>
                        <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}">
                    </div>
                </div>

                <!-- Enrollment Type Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            @if($enrollment->package_id)
                                <i class="fas fa-box me-2"></i>Package Enrollment
                            @else
                                <i class="fas fa-chalkboard me-2"></i>Single Class Enrollment
                            @endif
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($enrollment->package_id)
                            <!-- Package Enrollment -->
                            <div class="mb-3">
                                <label class="form-label">Package</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $enrollment->package->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="package_id" value="{{ $enrollment->package_id }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Package Price</label>
                                    <input type="text" class="form-control bg-light" 
                                           value="RM {{ number_format($enrollment->package->price ?? 0, 2) }}" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration</label>
                                    <input type="text" class="form-control bg-light" 
                                           value="{{ $enrollment->package->duration_months ?? 0 }} months" readonly>
                                </div>
                            </div>
                            @if($enrollment->class_id && $enrollment->class)
                            <div class="mb-3">
                                <label class="form-label">Assigned Class</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $enrollment->class->name }} ({{ $enrollment->class->subject->name ?? 'N/A' }})" readonly>
                                <input type="hidden" name="class_id" value="{{ $enrollment->class_id }}">
                            </div>
                            @endif
                        @else
                            <!-- Single Class Enrollment -->
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Current Class</label>
                                    <input type="text" class="form-control bg-light" 
                                           value="{{ $enrollment->class->name ?? 'N/A' }} - {{ $enrollment->class->subject->name ?? 'N/A' }} ({{ $enrollment->class->teacher->user->name ?? 'No Teacher' }})" readonly>
                                    <input type="hidden" name="class_id" value="{{ $enrollment->class_id }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Monthly Fee (RM)</label>
                                    <input type="text" class="form-control bg-light" 
                                           value="{{ number_format($enrollment->monthly_fee, 2) }}" readonly>
                                    <input type="hidden" name="monthly_fee" value="{{ $enrollment->monthly_fee }}">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Enrollment Details -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-cog me-2"></i>Enrollment Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date" name="start_date"
                                       value="{{ old('start_date', $enrollment->start_date->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date" name="end_date"
                                       value="{{ old('end_date', $enrollment->end_date ? $enrollment->end_date->format('Y-m-d') : '') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty for ongoing enrollment</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_cycle_day" class="form-label">Payment Cycle Day <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_cycle_day') is-invalid @enderror"
                                        id="payment_cycle_day" name="payment_cycle_day" required>
                                    @for($i = 1; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ old('payment_cycle_day', $enrollment->payment_cycle_day) == $i ? 'selected' : '' }}>
                                            {{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} of each month
                                        </option>
                                    @endfor
                                </select>
                                @error('payment_cycle_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($enrollment->package_id)
                            <div class="col-md-6 mb-3">
                                <label for="monthly_fee_package" class="form-label">Monthly Fee (RM) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('monthly_fee') is-invalid @enderror"
                                       id="monthly_fee_package" name="monthly_fee"
                                       value="{{ old('monthly_fee', $enrollment->monthly_fee) }}" required>
                                @error('monthly_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                        </div>

                        <!-- Fee Change Reason (shown when fee changes) -->
                        <div class="mb-3" id="fee_change_reason_group" style="display: none;">
                            <label for="fee_change_reason" class="form-label">Reason for Fee Change <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="fee_change_reason" name="fee_change_reason" rows="2"
                                      placeholder="Please provide a reason for changing the monthly fee...">{{ old('fee_change_reason') }}</textarea>
                            <small class="text-muted">This will be recorded in the fee history</small>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
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
                        <div class="mb-3">
                            <label class="form-label">Previous Cancellation Reason</label>
                            <textarea class="form-control bg-light" readonly rows="2">{{ $enrollment->cancellation_reason }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Enrollment
                        </button>
                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Summary & Info -->
            <div class="col-lg-4">
                <!-- Enrollment Summary -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-receipt me-2"></i>Enrollment Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="summaryContent">
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block">Student</small>
                                <strong>{{ $enrollment->student->user->name }}</strong>
                            </div>
                            
                            @if($enrollment->package_id)
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block">Package</small>
                                <strong>{{ $enrollment->package->name ?? 'N/A' }}</strong>
                                <div class="mt-1">
                                    <span class="badge bg-info">RM {{ number_format($enrollment->package->price ?? 0, 2) }}</span>
                                    <span class="badge bg-secondary">{{ $enrollment->package->duration_months ?? 0 }} months</span>
                                </div>
                            </div>
                            @else
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block">Class</small>
                                <strong>{{ $enrollment->class->name ?? 'N/A' }}</strong>
                                <br><small class="text-muted">{{ $enrollment->class->subject->name ?? '' }}</small>
                            </div>
                            @endif

                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block">Current Monthly Fee</small>
                                <strong class="text-success h5">RM {{ number_format($enrollment->monthly_fee, 2) }}</strong>
                            </div>

                            <div class="mb-0">
                                <small class="text-muted d-block">Status</small>
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
                        </div>
                    </div>
                </div>

                <!-- Current Status -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Current Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Enrolled On:</strong><br>{{ $enrollment->created_at->format('d M Y g:i A') }}</p>
                        <p class="mb-2"><strong>Start Date:</strong><br>{{ $enrollment->start_date->format('d M Y') }}</p>
                        @if($enrollment->end_date)
                            <p class="mb-2"><strong>End Date:</strong><br>{{ $enrollment->end_date->format('d M Y') }}</p>
                            <p class="mb-0"><strong>Days Remaining:</strong><br>
                                @if($enrollment->days_remaining > 0)
                                    <span class="{{ $enrollment->days_remaining > 30 ? 'text-success' : 'text-warning' }}">
                                        {{ $enrollment->days_remaining }} days
                                    </span>
                                @else
                                    <span class="text-danger">Expired</span>
                                @endif
                            </p>
                        @else
                            <p class="mb-0"><strong>End Date:</strong><br><span class="text-muted">Ongoing</span></p>
                        @endif
                    </div>
                </div>

                <!-- Fee History -->
                @if($enrollment->feeHistory && $enrollment->feeHistory->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history me-2"></i>Fee Change History
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($enrollment->feeHistory->sortByDesc('change_date')->take(5) as $history)
                        <div class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <small class="text-muted">{{ $history->change_date->format('d M Y') }}</small><br>
                            <strong class="text-muted text-decoration-line-through">RM {{ number_format($history->old_fee, 2) }}</strong>
                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                            <strong class="text-success">RM {{ number_format($history->new_fee, 2) }}</strong><br>
                            <small class="text-muted"><i class="fas fa-comment me-1"></i>{{ $history->reason }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Important Notes -->
                <div class="card shadow mb-4 border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Important Notes</h6>
                        <ul class="small mb-0 ps-3">
                            <li>Changing the monthly fee will be recorded in fee history</li>
                            <li>Start date changes may affect existing invoices</li>
                            <li>Status changes take effect immediately</li>
                            <li>Changing to 'cancelled' will stop future invoices</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($enrollment->package_id)
    const originalFee = {{ $enrollment->monthly_fee }};

    // Show fee change reason field when fee is changed (only for package enrollments)
    $('#monthly_fee_package').on('change input', function() {
        const newFee = parseFloat($(this).val());
        if (newFee !== originalFee && !isNaN(newFee)) {
            $('#fee_change_reason_group').show();
            $('#fee_change_reason').prop('required', true);
        } else {
            $('#fee_change_reason_group').hide();
            $('#fee_change_reason').prop('required', false);
        }
    });

    // Form validation before submit
    $('#enrollmentEditForm').on('submit', function(e) {
        const newFee = parseFloat($('#monthly_fee_package').val());
        if (newFee !== originalFee && !$('#fee_change_reason').val().trim()) {
            e.preventDefault();
            alert('Please provide a reason for the fee change.');
            $('#fee_change_reason').focus();
            return false;
        }
    });
    @endif
});
</script>
@endpush
