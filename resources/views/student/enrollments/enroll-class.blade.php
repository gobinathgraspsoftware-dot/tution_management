@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Enroll in Class</h4>
                <div class="page-title-right">
                    <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Browse
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Class Details --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 text-white">Class Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Class Name</label>
                            <h5>{{ $class->name }}</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Subject</label>
                            <h5>
                                @if($class->subject)
                                    <span class="badge bg-soft-primary text-primary fs-6">
                                        {{ $class->subject->name }}
                                    </span>
                                @endif
                            </h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Teacher</label>
                            <h5>{{ $class->teacher->user->name ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Class Type</label>
                            <h5>
                                <span class="badge bg-soft-info text-info">
                                    {{ ucfirst($class->type) }}
                                </span>
                            </h5>
                        </div>
                    </div>

                    @if($class->description)
                        <div class="mb-3">
                            <label class="text-muted small">Description</label>
                            <p>{{ $class->description }}</p>
                        </div>
                    @endif

                    @if($class->schedules && $class->schedules->isNotEmpty())
                        <div class="mb-3">
                            <label class="text-muted small mb-2">Class Schedule</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($class->schedules as $schedule)
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
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="p-3 bg-soft-primary rounded text-center">
                                <i class="fas fa-users fs-3 text-primary mb-2"></i>
                                <p class="mb-0 small text-muted">Class Capacity</p>
                                <h5 class="mb-0">{{ $class->current_enrollment }}/{{ $class->capacity }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-soft-success rounded text-center">
                                <i class="fas fa-chair fs-3 text-success mb-2"></i>
                                <p class="mb-0 small text-muted">Available Seats</p>
                                <h5 class="mb-0">{{ $class->capacity - $class->current_enrollment }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-soft-info rounded text-center">
                                <i class="fas fa-dollar-sign fs-3 text-info mb-2"></i>
                                <p class="mb-0 small text-muted">Monthly Fee</p>
                                <h5 class="mb-0">RM {{ number_format($class->monthly_fee, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enrollment Form --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Enrollment Details</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('student.enrollments.enroll-class.store', $class) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Start Date</label>
                            <input type="date"
                                   name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', now()->addDays(1)->format('Y-m-d')) }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Choose when you want to start this class</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Payment Cycle Day</label>
                            <select name="payment_cycle_day" class="form-select @error('payment_cycle_day') is-invalid @enderror" required>
                                <option value="">Select day of month</option>
                                @for($i = 1; $i <= 15; $i++)
                                    <option value="{{ $i }}" {{ old('payment_cycle_day', 5) == $i ? 'selected' : '' }}>
                                        {{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} of each month
                                    </option>
                                @endfor
                            </select>
                            @error('payment_cycle_day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Choose your preferred monthly payment date</small>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">Payment Summary</h6>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Monthly Fee:</span>
                                <strong>RM {{ number_format($class->monthly_fee, 2) }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total Due Today:</span>
                                <strong class="text-primary">RM {{ number_format($class->monthly_fee, 2) }}</strong>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agree" required>
                            <label class="form-check-label" for="agree">
                                I agree to the terms and conditions and understand the payment schedule
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i> Confirm Enrollment
                            </button>
                            <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Important Notes --}}
            <div class="card">
                <div class="card-header bg-warning">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Important Notes
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li class="mb-2">Payment will be automatically generated on your selected payment cycle day</li>
                        <li class="mb-2">You will receive WhatsApp reminders before payment due dates</li>
                        <li class="mb-2">Regular attendance is required to maintain enrollment</li>
                        <li>Contact admin if you need to change your payment schedule</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
