@extends('layouts.app')

@section('title', 'Enroll in Package')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shopping-cart"></i> Enroll in Package
        </h1>
        <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Packages
        </a>
    </div>

    <div class="row">
        <!-- Package Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-success">
                <div class="card-header bg-gradient-success text-white">
                    <h3 class="mb-0">{{ $package->name }}</h3>
                    <div class="mt-2">
                        <span class="badge badge-light text-success">
                            <i class="fas fa-clock"></i> {{ $package->duration_months }} Months Duration
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($package->description)
                    <p class="lead">{{ $package->description }}</p>
                    <hr>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-success">Package Includes</h6>
                            <p><strong>{{ $package->subjects->count() }} Subjects</strong> with <strong>{{ $classes->count() }} Classes</strong></p>
                            <ul class="list-group">
                                @foreach($package->subjects as $subject)
                                    <li class="list-group-item">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <strong>{{ $subject->name }}</strong>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-md-6 mb-3">
                            <h6 class="text-success">Pricing</h6>
                            <div class="pricing-box p-4 bg-light rounded text-center mb-3">
                                <div class="h2 text-success mb-0">
                                    RM {{ number_format($package->price, 2) }}
                                </div>
                                <small class="text-muted">per month</small>
                            </div>

                            @if($package->discountRule)
                            <div class="alert alert-success">
                                <i class="fas fa-tag"></i> <strong>Special Offer!</strong><br>
                                <small>{{ $package->discountRule->discount_percentage }}% discount applied</small>
                            </div>
                            @endif

                            <div class="small text-muted">
                                <strong>Package Benefits:</strong>
                                <ul class="mb-0">
                                    <li>Save compared to individual classes</li>
                                    <li>Comprehensive learning coverage</li>
                                    <li>Structured curriculum</li>
                                    <li>All materials included</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-success">Included Classes</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Class Name</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Schedule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classes as $class)
                                <tr>
                                    <td><strong>{{ $class->name }}</strong></td>
                                    <td>{{ $class->subject->name }}</td>
                                    <td>{{ $class->teacher->user->name }}</td>
                                    <td>
                                        @if($class->schedules->isNotEmpty())
                                            @foreach($class->schedules as $schedule)
                                                <small class="d-block">
                                                    {{ $schedule->day_of_week }}:
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}
                                                </small>
                                            @endforeach
                                        @else
                                            <small class="text-muted">TBA</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Enrollment Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Complete Your Package Enrollment</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('student.enrollments.enroll-package.store', $package) }}">
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
                            <small class="form-text text-muted">Select the date when you want to start this package</small>
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
                            <h6><i class="fas fa-info-circle"></i> Package Enrollment Details</h6>
                            <ul class="mb-0 small">
                                <li>You will be automatically enrolled in all <strong>{{ $classes->count() }} classes</strong> included in this package</li>
                                <li>A single invoice of <strong>RM {{ number_format($package->price, 2) }}</strong> will be generated monthly</li>
                                <li>You will receive instant access to materials for all classes</li>
                                <li>Package duration: <strong>{{ $package->duration_months }} months</strong></li>
                                <li>End date: <strong>{{ \Carbon\Carbon::parse(old('start_date', date('Y-m-d')))->addMonths($package->duration_months)->format('d M Y') }}</strong></li>
                            </ul>
                        </div>

                        <div class="alert alert-success">
                            <h6><i class="fas fa-piggy-bank"></i> Package Savings</h6>
                            <p class="small mb-0">By choosing this package instead of individual classes, you're making a smart investment in comprehensive learning!</p>
                        </div>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="terms" required>
                            <label class="custom-control-label" for="terms">
                                I agree to the <a href="#" target="_blank">terms and conditions</a> and understand
                                that I will be enrolled in all {{ $classes->count() }} classes included in this package
                            </label>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-check"></i> Confirm Package Enrollment
                            </button>
                            <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-secondary btn-block">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Package Summary -->
            <div class="card shadow mb-4 border-left-success">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Package Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Duration:</span>
                            <strong>{{ $package->duration_months }} Months</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Classes:</span>
                            <strong>{{ $classes->count() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subjects:</span>
                            <strong>{{ $package->subjects->count() }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Monthly Fee:</span>
                            <strong class="text-success h5">RM {{ number_format($package->price, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Investment:</span>
                            <strong class="text-success h5">RM {{ number_format($package->price * $package->duration_months, 2) }}</strong>
                        </div>
                    </div>
                    <small class="text-muted d-block">
                        * First invoice may be pro-rated based on your start date
                    </small>
                </div>
            </div>

            <!-- Why Choose Package -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="text-primary"><i class="fas fa-star"></i> Why Choose This Package?</h6>
                    <ul class="small mb-0">
                        <li>Cost-effective compared to individual classes</li>
                        <li>Comprehensive curriculum coverage</li>
                        <li>Structured learning path</li>
                        <li>All materials included</li>
                        <li>Single monthly payment</li>
                        <li>Consistent schedule</li>
                    </ul>
                </div>
            </div>

            <!-- Help -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="text-primary"><i class="fas fa-question-circle"></i> Questions?</h6>
                    <p class="small mb-2">Contact us for more information about this package:</p>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Update end date when start date changes
    $('#start_date').on('change', function() {
        const startDate = new Date($(this).val());
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + {{ $package->duration_months }});

        const formattedDate = endDate.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });

        $('.alert-info strong:last').text(formattedDate);
    });
});
</script>
@endpush
