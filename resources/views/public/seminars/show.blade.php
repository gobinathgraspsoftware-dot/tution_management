<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seminar->name }} - Arena Matriks Edu Group</title>
    
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
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .seminar-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0 40px;
        }

        .seminar-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .price-box {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .price-amount {
            font-size: 3rem;
            font-weight: bold;
        }

        .btn-register {
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .feature-icon {
            font-size: 2rem;
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
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Seminar Header -->
    <section class="seminar-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-light text-dark mb-3">{{ strtoupper($seminar->type) }}</span>
                    <h1 class="display-4 mb-3">{{ $seminar->name }}</h1>
                    <p class="lead">{{ $seminar->code }}</p>
                    @if($seminar->facilitator)
                    <p class="mb-0"><i class="fas fa-user-tie"></i> Facilitated by {{ $seminar->facilitator }}</p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    @if($isRegistrationOpen)
                    <span class="badge bg-success fs-5">
                        <i class="fas fa-check-circle"></i> Open for Registration
                    </span>
                    @else
                    <span class="badge bg-danger fs-5">
                        <i class="fas fa-times-circle"></i> Registration Closed
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8 mb-4">
                    @if($seminar->image)
                    <img src="{{ asset('storage/' . $seminar->image) }}" class="img-fluid seminar-image mb-4" alt="{{ $seminar->name }}">
                    @endif

                    <!-- Description -->
                    @if($seminar->description)
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-info-circle"></i> About This Seminar</h4>
                        </div>
                        <div class="card-body">
                            <p class="lead">{{ $seminar->description }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Key Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-calendar-check"></i> Key Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-calendar feature-icon me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Date</h6>
                                            <p class="mb-0">{{ $seminar->date->format('l, d F Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-clock feature-icon me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Time</h6>
                                            <p class="mb-0">
                                                @if($seminar->start_time)
                                                {{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}
                                                @if($seminar->end_time)
                                                 - {{ \Carbon\Carbon::parse($seminar->end_time)->format('h:i A') }}
                                                @endif
                                                @else
                                                To Be Announced
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-{{ $seminar->is_online ? 'video' : 'map-marker-alt' }} feature-icon me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Venue</h6>
                                            <p class="mb-0">
                                                @if($seminar->is_online)
                                                Online Seminar
                                                <br><small class="text-muted">Meeting link will be provided after registration</small>
                                                @else
                                                {{ $seminar->venue }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-users feature-icon me-3"></i>
                                        <div>
                                            <h6 class="mb-1">Capacity</h6>
                                            <p class="mb-0">
                                                @if($seminar->capacity)
                                                {{ $availableSpots }} spots available
                                                <div class="progress mt-2" style="height: 10px;">
                                                    @php
                                                        $percentage = $seminar->capacity > 0 ? ($seminar->current_participants / $seminar->capacity) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                @else
                                                Unlimited capacity
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Registration Box -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 20px;">
                        <!-- Price Box -->
                        <div class="price-box mb-4">
                            <h4 class="mb-3">Registration Fee</h4>
                            <div class="price-amount">
                                RM {{ number_format($currentFee, 2) }}
                            </div>
                            
                            @if($seminar->early_bird_fee && now() < $seminar->early_bird_deadline)
                            <div class="mt-3">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-tag"></i> Early Bird Price!
                                </span>
                                <p class="mt-2 mb-0">
                                    <small>Regular price: <del>RM {{ number_format($seminar->regular_fee, 2) }}</del></small><br>
                                    <small>Save RM {{ number_format($seminar->regular_fee - $seminar->early_bird_fee, 2) }}!</small><br>
                                    <small class="text-warning">Valid until {{ $seminar->early_bird_deadline->format('d M Y') }}</small>
                                </p>
                            </div>
                            @endif
                            
                            @if($isRegistrationOpen)
                            <a href="{{ route('public.seminars.register', $seminar) }}" class="btn btn-light btn-register mt-4 w-100">
                                <i class="fas fa-user-plus"></i> Register Now
                            </a>
                            @else
                            <button class="btn btn-secondary btn-register mt-4 w-100" disabled>
                                <i class="fas fa-times-circle"></i> Registration Closed
                            </button>
                            @endif
                        </div>

                        <!-- Important Dates -->
                        <div class="info-box">
                            <h5 class="mb-3"><i class="fas fa-bell"></i> Important Dates</h5>
                            
                            @if($seminar->early_bird_deadline)
                            <div class="mb-2">
                                <strong>Early Bird Deadline:</strong><br>
                                {{ $seminar->early_bird_deadline->format('d M Y') }}
                            </div>
                            @endif
                            
                            @if($seminar->registration_deadline)
                            <div class="mb-2">
                                <strong>Registration Closes:</strong><br>
                                {{ $seminar->registration_deadline->format('d M Y') }}
                            </div>
                            @endif
                            
                            <div>
                                <strong>Seminar Date:</strong><br>
                                {{ $seminar->date->format('d M Y') }}
                            </div>
                        </div>

                        <!-- Share -->
                        <div class="info-box text-center">
                            <h5 class="mb-3"><i class="fas fa-share-alt"></i> Share This Seminar</h5>
                            <div class="btn-group" role="group">
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($seminar->name) }}" target="_blank" class="btn btn-info btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($seminar->name . ' - ' . url()->current()) }}" target="_blank" class="btn btn-success btn-sm">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
