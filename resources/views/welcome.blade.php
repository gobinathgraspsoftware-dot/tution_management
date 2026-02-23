<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Arena Matriks Edu Group</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        $carouselImages = \App\Models\CarouselImage::active()->ordered()->get();
        $hasCarousel = $carouselImages->count() > 0;
    @endphp

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ============================================================ */
        /*  NAVBAR                                                      */
        /* ============================================================ */

        /* Solid navbar when carousel is active */
        .navbar-carousel {
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            position: relative;
            z-index: 10;
            padding: 0.6rem 0;
        }

        /* Transparent overlay navbar when hero section is shown */
        .navbar-hero {
            background: transparent;
            position: absolute;
            width: 100%;
            z-index: 10;
            padding: 0.8rem 0;
        }

        .navbar .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            transition: color 0.3s;
        }

        .navbar .nav-link:hover {
            color: #ffffff !important;
        }

        .btn-nav-login {
            background: #ffffff;
            color: #4c4c4c;
            border: none;
            border-radius: 50px;
            padding: 0.4rem 1.4rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .btn-nav-login:hover {
            background: #f0f0f0;
            color: #4c4c4c;
            transform: scale(1.05);
        }

        .btn-nav-register {
            background: transparent;
            color: #ffffff;
            border: 2px solid #ffffff;
            border-radius: 50px;
            padding: 0.35rem 1.4rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .btn-nav-register:hover {
            background: #ffffff;
            color: #4c4c4c;
            transform: scale(1.05);
        }

        /* ============================================================ */
        /*  CAROUSEL                                                    */
        /* ============================================================ */

        .carousel-section .carousel-item img {
            width: 100%;
            height: 520px;
            object-fit: cover;
        }

        .carousel-section .carousel-control-prev,
        .carousel-section .carousel-control-next {
            width: 5%;
            opacity: 0.7;
        }

        .carousel-section .carousel-control-prev:hover,
        .carousel-section .carousel-control-next:hover {
            opacity: 1;
        }

        .carousel-section .carousel-indicators button {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 5px;
        }

        @media (max-width: 768px) {
            .carousel-section .carousel-item img {
                height: 280px;
            }
        }

        /* ============================================================ */
        /*  HERO SECTION (fallback)                                     */
        /* ============================================================ */

        .hero-section {
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
        }

        /* ============================================================ */
        /*  GENERAL                                                     */
        /* ============================================================ */

        .feature-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .btn-custom {
            padding: 12px 40px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-white {
            background: white;
            color: #fda530;
        }

        .btn-white:hover {
            background: #f8f9fa;
            transform: scale(1.05);
        }

        .stats-section {
            background: #f8f9fa;
            padding: 80px 0;
        }

        .stat-item {
            text-align: center;
            padding: 30px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ============================================================ */
        /*  FOOTER — readable white text on gradient background         */
        /* ============================================================ */

        .footer {
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            color: white;
            padding: 40px 0 20px 0;
        }

        .footer h5 {
            color: #ffffff;
        }

        .footer p,
        .footer small {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.85) !important;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: #ffffff !important;
        }

        .footer hr {
            border-color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>

    {{-- ============================================================ --}}
    {{-- NAVBAR                                                        --}}
    {{-- ============================================================ --}}
    <nav class="navbar navbar-expand-lg navbar-dark {{ $hasCarousel ? 'navbar-carousel' : 'navbar-hero' }}">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                Arena Matriks Edu Group
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-nav-register" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-nav-login" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ============================================================ --}}
    {{-- CAROUSEL or HERO SECTION                                      --}}
    {{-- ============================================================ --}}
    @if($hasCarousel)
    <section class="carousel-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

            {{-- Indicators --}}
            {{-- @if($carouselImages->count() > 1)
            <div class="carousel-indicators">
                @foreach($carouselImages as $index => $slide)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}"
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
            @endif --}}

            {{-- Slides --}}
            <div class="carousel-inner">
                @foreach($carouselImages as $index => $slide)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    @if($slide->image_url)
                        <img src="{{ $slide->image_url }}" class="d-block w-100"
                             alt="Banner {{ $index + 1 }}">
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Controls --}}
            @if($carouselImages->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            @endif

        </div>
    </section>
    @else
    {{-- Fallback: Original hero section --}}
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Welcome to Arena Matriks Edu Group</h1>
                    <p class="lead mb-4">Empowering students with quality education.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('login') }}" class="btn btn-custom btn-white btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-custom btn-outline-light btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <h5>Active Students</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <h5>Expert Teachers</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">30+</div>
                        <h5>Active Classes</h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number">95%</div>
                        <h5>Success Rate</h5>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Features</h2>
                <p class="lead text-muted">Everything you need to manage your tuition centre effectively</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card card p-4">
                        <div class="feature-icon mx-auto" style="background-color: #e3f2fd; color: #2196f3;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h4 class="text-center mb-3">Student Management</h4>
                        <p class="text-muted text-center">
                            Complete student enrollment, profile management, and progress tracking system.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card card p-4">
                        <div class="feature-icon mx-auto" style="background-color: #e8f5e9; color: #4caf50;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h4 class="text-center mb-3">Payment Processing</h4>
                        <p class="text-muted text-center">
                            Automated invoice generation, online payments, and comprehensive financial reports.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card card p-4">
                        <div class="feature-icon mx-auto" style="background-color: #f3e5f5; color: #9c27b0;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4 class="text-center mb-3">Attendance Tracking</h4>
                        <p class="text-muted text-center">
                            Real-time attendance marking with instant parent notifications via WhatsApp.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card card p-4">
                        <div class="feature-icon mx-auto" style="background-color: #fff3e0; color: #ff9800;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <h4 class="text-center mb-3">Learning Materials</h4>
                        <p class="text-muted text-center">
                            Digital material distribution and management with access control.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card card p-4">
                        <div class="feature-icon mx-auto" style="background-color: #e1f5fe; color: #03a9f4;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4 class="text-center mb-3">Class Management</h4>
                        <p class="text-muted text-center">
                            Schedule classes, manage teachers, and coordinate educational programs efficiently.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card card p-4">
                        <div class="feature-icon mx-auto" style="background-color: #ffebee; color: #f44336;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="text-center mb-3">Reports & Analytics</h4>
                        <p class="text-muted text-center">
                            Comprehensive reporting on revenue, attendance, and academic performance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">About Arena Matriks</h2>
                    <p class="lead mb-4">
                        We are dedicated to providing quality education and exceptional learning experiences to students across Malaysia.
                    </p>
                    <p class="mb-4">
                        Our comprehensive tuition management system ensures smooth operations, transparent communication, and outstanding results. With experienced teachers and modern facilities, we help students achieve their academic goals.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Experienced Teachers</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Small Class Sizes</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Flexible Schedule</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Proven Results</li>
                    </ul>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-school" style="font-size: 20rem; color: #fda530; opacity: 0.1;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Get In Touch</h2>
                <p class="lead text-muted">Have questions? We're here to help!</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center h-100">
                        <i class="fas fa-map-marker-alt fa-3x mb-3 text-primary"></i>
                        <h5>Location</h5>
                        <p class="text-muted">
                            No.7, Jalan Kemuning Prima B33/B<br>
                            40400 Shah Alam, Selangor
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center h-100">
                        <i class="fas fa-phone fa-3x mb-3 text-primary"></i>
                        <h5>Phone</h5>
                        <p class="text-muted">
                            +60 14-648 8869<br>
                            +60 3-7972 3663
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 text-center h-100">
                        <i class="fas fa-envelope fa-3x mb-3 text-primary"></i>
                        <h5>Email</h5>
                        <p class="text-muted">
                            govind@graspsoftwaresolutions.com<br>
                            info@arenamatriks.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Arena Matriks Edu Group
                    </h5>
                    <p>
                        Excellence in education through quality teaching and comprehensive student support.
                    </p>
                </div>

                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-decoration-none">Features</a></li>
                        <li><a href="#about" class="text-decoration-none">About Us</a></li>
                        <li><a href="#contact" class="text-decoration-none">Contact</a></li>
                        <li><a href="{{ route('login') }}" class="text-decoration-none">Login</a></li>
                        <li><a href="{{ route('register') }}" class="text-decoration-none">Register</a></li>
                    </ul>
                </div>

                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Follow Us</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-whatsapp fa-2x"></i></a>
                    </div>
                </div>
            </div>

            <hr>

            <div class="text-center">
                <p class="mb-0">
                    &copy; {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.
                    <br>
                    <small>Developed by GRASP Software Solutions</small>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
