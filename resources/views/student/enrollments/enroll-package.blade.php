@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Enroll in Package</h4>
                <div class="page-title-right">
                    <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Packages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Package Details --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white">{{ $package->name }}</h5>
                </div>
                <div class="card-body">
                    <p class="lead">{{ $package->description }}</p>

                    @if($package->discountRule)
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="fas fa-tag me-2"></i>Special Discount Applied!
                            </h6>
                            <p class="mb-0">
                                @if($package->discountRule->type === 'percentage')
                                    Save {{ $package->discountRule->value }}% on this package
                                @else
                                    Save RM {{ number_format($package->discountRule->value, 2) }} on this package
                                @endif
                            </p>
                        </div>
                    @endif

                    <h6 class="mb-3">Classes Included in This Package:</h6>

                    @if($classes->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No active classes are currently available in this package. Please contact admin for more information.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Class Name</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Schedule</th>
                                        <th>Type</th>
                                        <th>Monthly Fee</th>
                                        <th>Seats</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                        <tr>
                                            <td><strong>{{ $class->name }}</strong></td>
                                            <td>
                                                <span class="badge bg-soft-primary text-primary">
                                                    {{ $class->subject->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $class->teacher->user->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($class->schedules && $class->schedules->isNotEmpty())
                                                    <small>
                                                        @foreach($class->schedules->take(1) as $schedule)
                                                            {{ ucfirst($schedule->day_of_week) }}<br>
                                                            {{ date('g:i A', strtotime($schedule->start_time)) }}
                                                        @endforeach
                                                        @if($class->schedules->count() > 1)
                                                            <br><span class="text-muted">+{{ $class->schedules->count() - 1 }} more</span>
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="text-muted">TBA</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-info text-info">
                                                    {{ ucfirst($class->type) }}
                                                </span>
                                            </td>
                                            <td>RM {{ number_format($class->monthly_fee, 2) }}</td>
                                            <td>
                                                @php
                                                    $available = $class->capacity - $class->current_enrollment;
                                                @endphp
                                                <span class="badge {{ $available > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $available }} left
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="text-end"><strong>Total Monthly Fee:</strong></td>
                                        <td colspan="2"><strong>RM {{ number_format($classes->sum('monthly_fee'), 2) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-soft-primary rounded text-center">
                                <i class="fas fa-book fs-3 text-primary mb-2"></i>
                                <p class="mb-0 small text-muted">Total Classes</p>
                                <h5 class="mb-0">{{ $classes->count() }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-soft-info rounded text-center">
                                <i class="fas fa-calendar-alt fs-3 text-info mb-2"></i>
                                <p class="mb-0 small text-muted">Duration</p>
                                <h5 class="mb-0">{{ $package->duration_months }} months</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-soft-success rounded text-center">
                                <i class="fas fa-dollar-sign fs-3 text-success mb-2"></i>
                                <p class="mb-0 small text-muted">Package Price</p>
                                <h5 class="mb-0">
                                    @if($package->discountRule)
                                        @php
                                            $discountedPrice = $package->discountRule->type === 'percentage'
                                                ? $package->price - ($package->price * $package->discountRule->value / 100)
                                                : $package->price - $package->discountRule->value;
                                        @endphp
                                        <small class="text-muted text-decoration-line-through">RM {{ number_format($package->price, 2) }}</small><br>
                                        RM {{ number_format($discountedPrice, 2) }}
                                    @else
                                        RM {{ number_format($package->price, 2) }}
                                    @endif
                                </h5>
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

                    @if($classes->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cannot enroll at this time. Please contact admin.
                        </div>
                    @else
                        <form action="{{ route('student.enrollments.enroll-package.store', $package) }}" method="POST">
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
                                <small class="form-text text-muted">Choose when you want to start all classes</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Payment Cycle Day</label>
                                <select name="payment_cycle_day" class="form-select @error('payment_cycle_day') is-invalid @enderror" required>
                                    <option value="">Select day of month</option>
                                    @for($i = 1; $i <= 28; $i++)
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
                                @php
                                    $totalMonthlyFee = $classes->sum('monthly_fee');
                                    if ($package->discountRule) {
                                        $packageDiscount = $package->discountRule->type === 'percentage'
                                            ? $package->price * $package->discountRule->value / 100
                                            : $package->discountRule->value;
                                    } else {
                                        $packageDiscount = 0;
                                    }
                                @endphp
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Monthly Fee (All Classes):</span>
                                    <strong>RM {{ number_format($totalMonthlyFee, 2) }}</strong>
                                </div>
                                @if($packageDiscount > 0)
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Package Discount:</span>
                                        <strong>- RM {{ number_format($packageDiscount, 2) }}</strong>
                                    </div>
                                @endif
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span><strong>Total Due Today:</strong></span>
                                    <strong class="text-primary">RM {{ number_format($totalMonthlyFee - $packageDiscount, 2) }}</strong>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    I agree to enroll in all {{ $classes->count() }} classes in this package and understand the payment terms
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> Confirm Package Enrollment
                                </button>
                                <a href="{{ route('student.enrollments.browse-packages') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Package Benefits --}}
            <div class="card">
                <div class="card-header bg-info">
                    <h6 class="card-title mb-0 text-white">
                        <i class="fas fa-star me-2"></i>Package Benefits
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li class="mb-2">Enroll in multiple classes at once</li>
                        @if($package->discountRule)
                            <li class="mb-2">Special package discount applied</li>
                        @endif
                        <li class="mb-2">Single monthly payment for all classes</li>
                        <li class="mb-2">Comprehensive learning across subjects</li>
                        <li>Access to all class materials and resources</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
