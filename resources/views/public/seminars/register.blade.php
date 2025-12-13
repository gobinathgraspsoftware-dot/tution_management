<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for {{ $seminar->name }} - Arena Matriks Edu Group</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .registration-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 0;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .summary-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }

        .required-field::after {
            content: " *";
            color: red;
        }

        .price-highlight {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <strong>Arena Matriks Edu Group</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.seminars.index') }}">All Seminars</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="registration-header">
        <div class="container text-center">
            <h1 class="display-5 mb-3">Seminar Registration</h1>
            <p class="lead">{{ $seminar->name }}</p>
        </div>
    </section>

    <!-- Registration Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Seminar Summary -->
                    <div class="summary-box mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-3">{{ $seminar->name }}</h4>
                                <p class="mb-1"><i class="fas fa-calendar"></i> {{ $seminar->date->format('l, d F Y') }}</p>
                                <p class="mb-1">
                                    <i class="fas fa-clock"></i> 
                                    @if($seminar->start_time)
                                    {{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                                    @else
                                    Time TBA
                                    @endif
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-{{ $seminar->is_online ? 'video' : 'map-marker-alt' }}"></i> 
                                    {{ $seminar->is_online ? 'Online Seminar' : $seminar->venue }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="price-highlight">
                                    RM {{ number_format($currentFee, 2) }}
                                </div>
                                @if($seminar->early_bird_fee && now() < $seminar->early_bird_deadline)
                                <span class="badge bg-success">Early Bird Price!</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Registration Form -->
                    <div class="form-container">
                        <h4 class="mb-4"><i class="fas fa-user-edit"></i> Your Information</h4>
                        
                        <form action="{{ route('public.seminars.submit', $seminar) }}" method="POST" id="registrationForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label required-field">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label required-field">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Confirmation will be sent to this email</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label required-field">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" required
                                           placeholder="e.g. 012-3456789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="school" class="form-label">School Name</label>
                                    <input type="text" class="form-control @error('school') is-invalid @enderror" 
                                           id="school" name="school" value="{{ old('school') }}">
                                    @error('school')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="grade" class="form-label">Grade/Form</label>
                                    <select class="form-select @error('grade') is-invalid @enderror" id="grade" name="grade">
                                        <option value="">Select Grade</option>
                                        <option value="Form 1" {{ old('grade') == 'Form 1' ? 'selected' : '' }}>Form 1</option>
                                        <option value="Form 2" {{ old('grade') == 'Form 2' ? 'selected' : '' }}>Form 2</option>
                                        <option value="Form 3" {{ old('grade') == 'Form 3' ? 'selected' : '' }}>Form 3</option>
                                        <option value="Form 4" {{ old('grade') == 'Form 4' ? 'selected' : '' }}>Form 4</option>
                                        <option value="Form 5" {{ old('grade') == 'Form 5' ? 'selected' : '' }}>Form 5</option>
                                        <option value="Form 6" {{ old('grade') == 'Form 6' ? 'selected' : '' }}>Form 6</option>
                                        <option value="Other" {{ old('grade') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('grade')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                        <option value="">Select Method</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash (Pay at Counter)</option>
                                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Choose your preferred payment method</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Any special requirements or questions?">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <!-- Terms & Conditions -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                    </label>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="alert alert-info">
                                <h5 class="mb-3">Registration Summary</h5>
                                <div class="d-flex justify-content-between">
                                    <span>Seminar Fee:</span>
                                    <strong>RM {{ number_format($currentFee, 2) }}</strong>
                                </div>
                                @if($seminar->early_bird_fee && now() < $seminar->early_bird_deadline)
                                <div class="d-flex justify-content-between text-success">
                                    <span>Early Bird Discount:</span>
                                    <strong>- RM {{ number_format($seminar->regular_fee - $seminar->early_bird_fee, 2) }}</strong>
                                </div>
                                @endif
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total Amount:</strong>
                                    <strong class="text-primary fs-4">RM {{ number_format($currentFee, 2) }}</strong>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('public.seminars.show', $seminar) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle"></i> Complete Registration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Registration</h6>
                    <p>By registering for this seminar, you agree to provide accurate and complete information.</p>
                    
                    <h6>2. Payment</h6>
                    <p>Payment must be completed within 3 days of registration to secure your spot.</p>
                    
                    <h6>3. Cancellation</h6>
                    <p>Cancellations must be made at least 7 days before the seminar date for a refund.</p>
                    
                    <h6>4. Changes</h6>
                    <p>Arena Matriks reserves the right to make changes to the seminar schedule or content.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
        </div>
    </footer>

    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Check email availability
        $('#email').blur(function() {
            const email = $(this).val();
            if(email) {
                $.ajax({
                    url: '{{ route("public.seminars.check-email") }}',
                    method: 'GET',
                    data: {
                        email: email,
                        seminar_id: {{ $seminar->id }}
                    },
                    success: function(response) {
                        if(response.exists) {
                            $('#email').addClass('is-invalid');
                            $('#email').after('<div class="invalid-feedback" style="display: block;">This email is already registered for this seminar.</div>');
                        } else {
                            $('#email').removeClass('is-invalid');
                            $('#email').next('.invalid-feedback').remove();
                        }
                    }
                });
            }
        });

        // Format phone number
        $('#phone').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if(value.length > 3 && value.length <= 6) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            } else if(value.length > 6) {
                value = value.slice(0, 3) + '-' + value.slice(3, 6) + value.slice(6, 10);
            }
            $(this).val(value);
        });
    });
    </script>
</body>
</html>
