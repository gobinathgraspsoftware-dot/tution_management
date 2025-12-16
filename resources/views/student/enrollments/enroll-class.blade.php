@extends('layouts.app')

@section('title', 'Enroll in Class')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus"></i> Enroll in Class
        </h1>
        <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Classes
        </a>
    </div>

    <div class="row">
        <!-- Class Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-primary">
                <div class="card-header bg-gradient-primary text-white">
                    <h4 class="mb-0">{{ $class->name }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Class Information</h6>
                            <p class="mb-2">
                                <strong><i class="fas fa-book"></i> Subject:</strong><br>
                                {{ $class->subject->name }}
                            </p>
                            <p class="mb-2">
                                <strong><i class="fas fa-chalkboard-teacher"></i> Teacher:</strong><br>
                                {{ $class->teacher->user->name }}
                            </p>
                            @if($class->description)
                            <p class="mb-2">
                                <strong><i class="fas fa-info-circle"></i> Description:</strong><br>
                                {{ $class->description }}
                            </p>
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary">Schedule</h6>
                            @if($class->schedules->isNotEmpty())
                                @foreach($class->schedules as $schedule)
                                <div class="mb-2 p-2 border-left border-primary">
                                    <strong>{{ $schedule->day_of_week }}</strong><br>
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                                    @if($schedule->venue)
                                        <br><small class="text-muted">{{ $schedule->venue }}</small>
                                    @endif
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted">Schedule to be announced</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="pricing-box p-4 bg-light rounded text-center">
                                <h6 class="text-muted">Monthly Fee</h6>
                                <div class="h2 text-success mb-0">
                                    RM {{ number_format($class->monthly_fee, 2) }}
                                </div>
                                <small class="text-muted">per month</small>
                            </div>
                        </div>

                        @if($class->max_students)
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted">Class Capacity</h6>
                                <p class="mb-2">
                                    <strong>{{ $class->enrollments()->active()->count() }}</strong> /
                                    <strong>{{ $class->max_students }}</strong> students enrolled
                                </p>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ ($class->enrollments()->active()->count() / $class->max_students) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Enrollment Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Complete Your Enrollment</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('student.enrollments.enroll-class.store', $class) }}">
                        @csrf

                        <div class="form-group">
                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date"
                                   value="{{ old('start_date', date('Y-m-d')) }}"
                                   min="{{ date('Y-m-d') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select the date when you want to start attending this class</small>
                        </div>

                        <div class="form-group">
                            <label for="payment_cycle_day">Payment Cycle Day <span class="text-danger">*</span></label>
                            <select class="form-control @error('payment_cycle_day') is-invalid @enderror"
                                    id="payment_cycle_day" name="payment_cycle_day" required>
                                <option value="">Select Day...</option>
                                @for($i = 1; $i <= 28; $i++)
                                    <option value="{{ $i }}" {{ old('payment_cycle_day', 15) == $i ? 'selected' : '' }}>
                                        Day {{ $i }} of each month
                                    </option>
                                @endfor
                            </select>
                            @error('payment_cycle_day')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Choose the day of each month when your payment is due</small>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> What happens next?</h6>
                            <ul class="mb-0 small">
                                <li>An invoice will be automatically generated for your first payment</li>
                                <li>You will receive confirmation via email and WhatsApp</li>
                                <li>You will get access to class materials immediately</li>
                                <li>Monthly invoices will be generated on your payment cycle day</li>
                            </ul>
                        </div>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="terms" required>
                            <label class="custom-control-label" for="terms">
                                I agree to the <a href="#" target="_blank">terms and conditions</a> and confirm
                                that I will make payments on time
                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-check"></i> Confirm Enrollment
                            </button>
                            <a href="{{ route('student.enrollments.browse-classes') }}" class="btn btn-secondary btn-block">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Fee Breakdown -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Fee Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Monthly Tuition:</span>
                        <strong>RM {{ number_format($class->monthly_fee, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Monthly:</strong>
                        <strong class="text-success">RM {{ number_format($class->monthly_fee, 2) }}</strong>
                    </div>
                    <small class="text-muted d-block mt-2">
                        * First invoice may be pro-rated based on your start date
                    </small>
                </div>
            </div>

            <!-- Benefits -->
            <div class="card shadow mb-4 border-left-success">
                <div class="card-body">
                    <h6 class="text-success"><i class="fas fa-check-circle"></i> Enrollment Benefits</h6>
                    <ul class="small mb-0">
                        <li>Access to all class materials</li>
                        <li>Direct communication with teacher</li>
                        <li>Progress tracking & reports</li>
                        <li>Attendance monitoring</li>
                        <li>Online payment options</li>
                        <li>WhatsApp notifications</li>
                    </ul>
                </div>
            </div>

            <!-- Help -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="text-primary"><i class="fas fa-question-circle"></i> Need Help?</h6>
                    <p class="small mb-2">If you have any questions about enrolling in this class, please contact us:</p>
                    <p class="small mb-0">
                        <strong>Email:</strong> support@arenamatriks.com<br>
                        <strong>Phone:</strong> 03-7972 3663
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
