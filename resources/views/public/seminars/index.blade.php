<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Seminars - Arena Matriks Edu Group</title>
    
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

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 0;
        }

        .seminar-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }

        .seminar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .seminar-image {
            height: 200px;
            object-fit: cover;
        }

        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff9800;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .early-bird {
            color: #4caf50;
            font-size: 0.9rem;
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
                        <a class="nav-link active" href="{{ route('public.seminars.index') }}">Seminars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-3">Upcoming Seminars & Workshops</h1>
            <p class="lead">Join our expert-led seminars and workshops to excel in your academic journey</p>
        </div>
    </section>

    <!-- Filters -->
    <section class="py-4 bg-white shadow-sm">
        <div class="container">
            <form method="GET" action="{{ route('public.seminars.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search seminars..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="spm" {{ request('type') == 'spm' ? 'selected' : '' }}>SPM</option>
                        <option value="workshop" {{ request('type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                        <option value="bootcamp" {{ request('type') == 'bootcamp' ? 'selected' : '' }}>Bootcamp</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Featured Seminars -->
    @if($featured->count() > 0 && !request()->has('search'))
    <section class="py-5">
        <div class="container">
            <h2 class="mb-4"><i class="fas fa-star text-warning"></i> Featured Seminars</h2>
            <div class="row">
                @foreach($featured as $seminar)
                <div class="col-md-4 mb-4">
                    <div class="card seminar-card">
                        <div class="position-relative">
                            @if($seminar->image)
                            <img src="{{ asset('storage/' . $seminar->image) }}" class="card-img-top seminar-image" alt="{{ $seminar->name }}">
                            @else
                            <div class="seminar-image bg-gradient" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"></div>
                            @endif
                            <span class="featured-badge"><i class="fas fa-star"></i> Featured</span>
                        </div>
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2">{{ strtoupper($seminar->type) }}</span>
                            <h5 class="card-title">{{ $seminar->name }}</h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> {{ $seminar->date->format('d M Y') }}<br>
                                    @if($seminar->is_online)
                                    <i class="fas fa-video"></i> Online Seminar
                                    @else
                                    <i class="fas fa-map-marker-alt"></i> {{ Str::limit($seminar->venue, 30) }}
                                    @endif
                                </small>
                            </p>
                            <div class="price-tag">
                                RM {{ number_format($seminar->early_bird_fee && now() < $seminar->early_bird_deadline ? $seminar->early_bird_fee : $seminar->regular_fee, 2) }}
                            </div>
                            @if($seminar->early_bird_fee && now() < $seminar->early_bird_deadline)
                            <div class="early-bird">
                                <i class="fas fa-tag"></i> Early Bird Price! Save RM {{ number_format($seminar->regular_fee - $seminar->early_bird_fee, 2) }}
                            </div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('public.seminars.show', $seminar) }}" class="btn btn-primary w-100">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- All Seminars -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4">All Upcoming Seminars</h2>
            
            @if($seminars->count() > 0)
            <div class="row">
                @foreach($seminars as $seminar)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card seminar-card">
                        @if($seminar->image)
                        <img src="{{ asset('storage/' . $seminar->image) }}" class="card-img-top seminar-image" alt="{{ $seminar->name }}">
                        @else
                        <div class="seminar-image bg-gradient" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);"></div>
                        @endif
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2">{{ strtoupper($seminar->type) }}</span>
                            <h6 class="card-title">{{ Str::limit($seminar->name, 50) }}</h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> {{ $seminar->date->format('d M Y') }}<br>
                                    @if($seminar->capacity)
                                    <i class="fas fa-users"></i> {{ $seminar->capacity - $seminar->current_participants }} spots left
                                    @endif
                                </small>
                            </p>
                            <div class="price-tag">
                                RM {{ number_format($seminar->early_bird_fee && now() < $seminar->early_bird_deadline ? $seminar->early_bird_fee : $seminar->regular_fee, 2) }}
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('public.seminars.show', $seminar) }}" class="btn btn-primary btn-sm w-100">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $seminars->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No seminars found</h5>
                <p class="text-muted">Please check back later for upcoming seminars.</p>
            </div>
            @endif
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
