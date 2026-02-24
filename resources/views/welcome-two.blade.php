<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Arena Matriks Edu Group - Quality Tuition Centre Management System">
    <title>Arena Matriks Edu Group - Excellence in Education</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @php
        $carouselImages = \App\Models\CarouselImage::active()->ordered()->get();
        $hasCarousel    = $carouselImages->count() > 0;
    @endphp

    <style>
    /* ================================================================
       ENROLL TEMPLATE - EXACT REPLICA STYLES
       Primary: Navy #1a3c5e | Accent: Orange #f7941d
       Adapted for Arena Matriks Edu Group
    ================================================================ */
    :root {
        --primary: #1a3c5e;
        --primary-dark: #142e4a;
        --primary-light: #24517a;
        --accent: #f7941d;
        --accent-dark: #e07e0a;
        --accent-light: #fef4e5;
        --dark: #111d2e;
        --text: #666666;
        --heading: #333333;
        --light-bg: #f5f5f5;
        --white: #ffffff;
        --border: #e5e5e5;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Open Sans', sans-serif; color: var(--text); font-size: 14px; line-height: 1.7; overflow-x: hidden; }
    a { text-decoration: none; transition: all .3s ease; }
    img { max-width: 100%; }
    h1,h2,h3,h4,h5,h6 { font-family: 'Montserrat', sans-serif; color: var(--heading); font-weight: 700; }

    /* ================================================================
       1. TOP BAR (Dark navy strip - Enroll style)
    ================================================================ */
    .top-bar {
        background: var(--dark);
        padding: 0;
        font-size: 12px;
        border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .top-bar-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .top-bar .info-left a,
    .top-bar .info-left span {
        color: rgba(255,255,255,.65);
        display: inline-flex;
        align-items: center;
        padding: 10px 15px;
        border-right: 1px solid rgba(255,255,255,.06);
    }
    .top-bar .info-left a:hover { color: var(--accent); }
    .top-bar .info-left i {
        color: var(--accent);
        margin-right: 6px;
        font-size: 11px;
    }
    .top-bar .social-right a {
        color: rgba(255,255,255,.5);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-left: 1px solid rgba(255,255,255,.06);
        font-size: 13px;
        transition: .3s;
    }
    .top-bar .social-right a:hover {
        color: var(--white);
        background: var(--accent);
    }
    @media(max-width:991px) { .top-bar { display: none; } }

    /* ================================================================
       2. MAIN HEADER / NAVBAR (Enroll style with login box)
    ================================================================ */
    .main-header {
        background: var(--white);
        box-shadow: 0 1px 10px rgba(0,0,0,.07);
        position: sticky;
        top: 0;
        z-index: 1050;
    }
    .header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Logo */
    .site-logo {
        display: flex;
        align-items: center;
        padding: 15px 0;
    }
    .site-logo .logo-icon {
        width: 48px;
        height: 48px;
        background: var(--accent);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 22px;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .site-logo .logo-text h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--primary);
        line-height: 1.2;
        font-family: 'Montserrat', sans-serif;
    }
    .site-logo .logo-text small {
        font-size: 10px;
        color: var(--text);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 600;
    }

    /* Nav Area - flex row to keep nav + login on same line */
    .nav-area {
        display: flex;
        align-items: center;
        gap: 0;
    }

    /* Navigation */
    .main-nav {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        align-items: center;
    }
    .main-nav > li > a {
        display: block;
        padding: 25px 14px;
        color: var(--heading);
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
        position: relative;
        white-space: nowrap;
    }
    .main-nav > li > a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 14px;
        right: 14px;
        height: 3px;
        background: var(--accent);
        transform: scaleX(0);
        transition: transform .3s;
    }
    .main-nav > li > a:hover,
    .main-nav > li > a.active {
        color: var(--accent);
    }
    .main-nav > li > a:hover::after,
    .main-nav > li > a.active::after {
        transform: scaleX(1);
    }

    /* Header Login Box (Enroll signature) - inline with nav */
    .header-login-box {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: 15px;
        padding-left: 15px;
        border-left: 1px solid var(--border);
        flex-shrink: 0;
    }
    .header-login-box .hlb-btn {
        background: var(--accent);
        color: var(--white);
        border: none;
        padding: 9px 20px;
        border-radius: 3px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        cursor: pointer;
        transition: .3s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }
    .header-login-box .hlb-btn:hover {
        background: var(--accent-dark);
    }
    .header-login-box .hlb-link {
        font-size: 11px;
        color: var(--accent);
        font-weight: 600;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }
    .header-login-box .hlb-link:hover {
        color: var(--primary);
    }

    /* Mobile Nav */
    .mobile-toggle {
        display: none;
        background: var(--accent);
        color: var(--white);
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 18px;
        cursor: pointer;
    }
    @media(max-width:991px) {
        .mobile-toggle { display: block; }
        .nav-area {
            display: none;
            flex-direction: column;
            align-items: stretch;
        }
        .nav-area.open {
            display: flex;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            box-shadow: 0 5px 20px rgba(0,0,0,.1);
            z-index: 100;
        }
        .main-nav {
            flex-direction: column;
            padding: 10px 20px;
        }
        .main-nav > li > a {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .main-nav > li > a::after { display: none; }
        .header-login-box {
            margin-left: 0;
            padding-left: 0;
            border-left: none;
            padding: 15px 20px;
            border-top: 1px solid var(--border);
        }
    }

    /* ================================================================
       3. HERO SLIDER (Enroll full-width slider)
    ================================================================ */
    .hero-slider { position: relative; }
    .hero-slider .carousel-item img {
        width: 100%;
        height: 560px;
        object-fit: cover;
    }
    .hero-slider .slide-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(17,29,46,.8) 0%, rgba(17,29,46,.4) 100%);
        z-index: 1;
    }
    .hero-slider .slide-caption {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 0;
        width: 100%;
        transform: translateY(-50%);
        padding: 0 8%;
    }
    .hero-slider .slide-caption .caption-tag {
        display: inline-block;
        background: var(--accent);
        color: var(--white);
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        padding: 6px 20px;
        border-radius: 2px;
        margin-bottom: 16px;
    }
    .hero-slider .slide-caption h1 {
        color: var(--white);
        font-size: 44px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 15px;
        max-width: 600px;
    }
    .hero-slider .slide-caption h1 span { color: var(--accent); }
    .hero-slider .slide-caption p {
        color: rgba(255,255,255,.8);
        font-size: 15px;
        max-width: 500px;
        margin-bottom: 25px;
        line-height: 1.8;
    }
    .hero-slider .carousel-control-prev,
    .hero-slider .carousel-control-next {
        width: 50px;
        height: 50px;
        top: 50%;
        bottom: auto;
        transform: translateY(-50%);
        background: rgba(255,255,255,.15);
        border-radius: 0;
        opacity: 0;
        margin: 0 15px;
        transition: .3s;
        z-index: 5;
    }
    .hero-slider:hover .carousel-control-prev,
    .hero-slider:hover .carousel-control-next { opacity: 1; }
    .hero-slider .carousel-control-prev:hover,
    .hero-slider .carousel-control-next:hover { background: var(--accent); }

    .hero-slider .carousel-indicators {
        z-index: 5;
        margin-bottom: 25px;
    }
    .hero-slider .carousel-indicators button {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin: 0 4px;
        border: 2px solid rgba(255,255,255,.5);
        background: transparent;
        transition: .3s;
    }
    .hero-slider .carousel-indicators button.active {
        background: var(--accent);
        border-color: var(--accent);
    }

    .btn-enroll {
        display: inline-block;
        background: var(--accent);
        color: var(--white);
        padding: 12px 30px;
        border-radius: 2px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
        border: 2px solid var(--accent);
        transition: .3s;
    }
    .btn-enroll:hover {
        background: var(--accent-dark);
        border-color: var(--accent-dark);
        color: var(--white);
    }
    .btn-enroll-outline {
        display: inline-block;
        background: transparent;
        color: var(--white);
        padding: 12px 30px;
        border-radius: 2px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
        border: 2px solid rgba(255,255,255,.4);
        transition: .3s;
    }
    .btn-enroll-outline:hover {
        border-color: var(--white);
        background: rgba(255,255,255,.1);
        color: var(--white);
    }

    @media(max-width:768px) {
        .hero-slider .carousel-item img { height: 380px; }
        .hero-slider .slide-caption h1 { font-size: 24px; }
        .hero-slider .slide-caption p { font-size: 13px; }
        .hero-slider .carousel-control-prev,
        .hero-slider .carousel-control-next { width: 38px; height: 38px; }
    }

    /* Hero Fallback */
    .hero-fallback {
        background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
        min-height: 80vh;
        display: flex;
        align-items: center;
        color: var(--white);
        position: relative;
        overflow: hidden;
    }
    .hero-fallback::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(247,148,29,.15), transparent 70%);
        border-radius: 50%;
    }
    .hero-fallback h1 { font-size: 42px; font-weight: 800; color: var(--white); line-height: 1.2; margin-bottom: 16px; }
    .hero-fallback h1 span { color: var(--accent); }
    .hero-fallback p { color: rgba(255,255,255,.8); font-size: 15px; max-width: 500px; margin-bottom: 28px; }

    /* ================================================================
       4. THREE INFO BOXES (Enroll signature - overlapping slider)
    ================================================================ */
    .info-boxes {
        margin-top: -60px;
        position: relative;
        z-index: 20;
    }
    .info-box {
        padding: 28px 25px;
        display: flex;
        align-items: center;
        color: var(--white);
        transition: .3s;
        height: 100%;
    }
    .info-box:hover { transform: translateY(-3px); }
    .info-box.box-orange { background: var(--accent); border-radius: 5px 0 0 5px; }
    .info-box.box-navy   { background: var(--primary); }
    .info-box.box-dark   { background: var(--primary-dark); border-radius: 0 5px 5px 0; }
    .info-box .ib-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        margin-right: 16px;
    }
    .info-box h5 {
        color: var(--white);
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .info-box p {
        color: rgba(255,255,255,.75);
        font-size: 12px;
        margin: 0;
        line-height: 1.5;
    }
    @media(max-width:991px) {
        .info-box { border-radius: 0 !important; }
        .info-boxes { margin-top: 0; }
    }

    /* ================================================================
       5. SECTION COMMON STYLES
    ================================================================ */
    .section-padding { padding: 75px 0; }
    .section-heading { margin-bottom: 45px; }
    .section-heading .line-tag {
        display: inline-block;
        position: relative;
        color: var(--accent);
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        padding-left: 50px;
        margin-bottom: 8px;
    }
    .section-heading .line-tag::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 2px;
        background: var(--accent);
    }
    .section-heading h2 {
        font-size: 30px;
        font-weight: 800;
        color: var(--primary);
        line-height: 1.3;
    }
    .section-heading h2 span { color: var(--accent); }
    .section-heading p {
        color: var(--text);
        font-size: 14px;
        max-width: 550px;
        margin-top: 8px;
    }
    .section-heading.center-heading .line-tag {
        padding-left: 0;
    }
    .section-heading.center-heading .line-tag::before {
        display: none;
    }
    .section-heading.center-heading p {
        margin-left: auto;
        margin-right: auto;
    }

    /* ================================================================
       6. ABOUT / WELCOME SECTION (Enroll style)
    ================================================================ */
    .about-section { background: var(--white); }
    .about-img-block { position: relative; }
    .about-img-block .img-holder {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,.1);
    }
    .about-img-block .img-holder .placeholder {
        width: 100%;
        height: 380px;
        background: linear-gradient(135deg, var(--accent-light) 0%, #fce4b8 50%, var(--accent) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 6rem;
        color: rgba(26,60,94,.12);
    }
    .about-img-block .play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 70px;
        height: 70px;
        background: var(--accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 22px;
        box-shadow: 0 5px 25px rgba(247,148,29,.5);
        transition: .3s;
        cursor: pointer;
    }
    .about-img-block .play-btn:hover { transform: translate(-50%, -50%) scale(1.1); }
    .about-img-block .exp-badge {
        position: absolute;
        bottom: -20px;
        right: -15px;
        background: var(--primary);
        color: var(--white);
        padding: 20px 25px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(26,60,94,.35);
    }
    .about-img-block .exp-badge .num { font-size: 32px; font-weight: 800; line-height: 1; }
    .about-img-block .exp-badge .lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }

    .about-list { list-style: none; padding: 0; margin: 18px 0 0; }
    .about-list li {
        display: flex;
        align-items: center;
        margin-bottom: 11px;
        font-size: 14px;
        color: var(--heading);
    }
    .about-list li i {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--accent-light);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        flex-shrink: 0;
        margin-right: 10px;
    }

    /* ================================================================
       7. POPULAR COURSES (Enroll style grid with filter)
    ================================================================ */
    .courses-section { background: var(--light-bg); }
    .course-filter {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 30px;
    }
    .course-filter .filter-btn {
        background: transparent;
        border: 2px solid var(--border);
        color: var(--heading);
        padding: 8px 20px;
        border-radius: 3px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        cursor: pointer;
        transition: .3s;
    }
    .course-filter .filter-btn:hover,
    .course-filter .filter-btn.active {
        background: var(--accent);
        border-color: var(--accent);
        color: var(--white);
    }

    .course-card {
        background: var(--white);
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--border);
        transition: .3s;
        height: 100%;
    }
    .course-card:hover {
        box-shadow: 0 10px 35px rgba(0,0,0,.08);
        transform: translateY(-5px);
    }
    .course-card .cc-img {
        height: 180px;
        overflow: hidden;
        position: relative;
    }
    .course-card .cc-img .cc-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: rgba(26,60,94,.15);
    }
    .course-card .cc-img .cc-price {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--accent);
        color: var(--white);
        padding: 4px 12px;
        border-radius: 2px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 13px;
    }
    .course-card .cc-body { padding: 18px; }
    .course-card .cc-body .cc-category {
        font-size: 11px;
        color: var(--accent);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }
    .course-card .cc-body h5 {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .course-card .cc-body h5 a { color: var(--heading); }
    .course-card .cc-body h5 a:hover { color: var(--accent); }
    .course-card .cc-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 18px;
        border-top: 1px solid var(--border);
        font-size: 12px;
        color: var(--text);
    }
    .course-card .cc-meta .stars i { color: var(--accent); font-size: 11px; }
    .course-card .cc-meta .students i { margin-right: 4px; }

    /* ================================================================
       8. STATS / COUNTER (Enroll dark section)
    ================================================================ */
    .stats-section {
        background: var(--primary);
        position: relative;
        overflow: hidden;
    }
    .stats-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .stat-item {
        text-align: center;
        padding: 40px 15px;
        position: relative;
        z-index: 1;
    }
    .stat-item .si-icon {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        border: 2px solid rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: var(--accent);
        margin: 0 auto 15px;
    }
    .stat-item .si-num {
        font-size: 40px;
        font-weight: 800;
        color: var(--white);
        line-height: 1;
        margin-bottom: 5px;
        font-family: 'Montserrat', sans-serif;
    }
    .stat-item .si-num .suffix { color: var(--accent); font-size: 26px; }
    .stat-item .si-label { color: rgba(255,255,255,.6); font-size: 14px; font-weight: 500; }

    /* ================================================================
       9. EVENTS SECTION (Enroll style)
    ================================================================ */
    .events-section { background: var(--white); }
    .event-item {
        display: flex;
        align-items: flex-start;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 6px;
        overflow: hidden;
        transition: .3s;
        margin-bottom: 20px;
    }
    .event-item:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,.06);
        transform: translateY(-2px);
    }
    .event-item .ei-date {
        width: 85px;
        min-height: 85px;
        background: var(--accent);
        color: var(--white);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        padding: 12px;
    }
    .event-item .ei-date .day {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        font-family: 'Montserrat', sans-serif;
    }
    .event-item .ei-date .month {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .event-item .ei-body { padding: 15px 18px; }
    .event-item .ei-body h5 {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .event-item .ei-body p { font-size: 13px; color: var(--text); margin: 0; line-height: 1.6; }
    .event-item .ei-body .ei-meta {
        font-size: 12px;
        color: var(--accent);
        margin-top: 6px;
    }
    .event-item .ei-body .ei-meta i { margin-right: 4px; }

    /* ================================================================
       10. TESTIMONIALS (Enroll style)
    ================================================================ */
    .testimonials-section { background: var(--light-bg); }
    .testi-card {
        background: var(--white);
        border-radius: 8px;
        padding: 30px;
        border: 1px solid var(--border);
        height: 100%;
        position: relative;
        transition: .3s;
    }
    .testi-card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,.06);
    }
    .testi-card .quote-icon {
        font-size: 30px;
        color: var(--accent);
        margin-bottom: 12px;
        opacity: .3;
    }
    .testi-card p {
        font-size: 14px;
        color: var(--text);
        font-style: italic;
        line-height: 1.8;
        margin-bottom: 18px;
    }
    .testi-card .tc-author {
        display: flex;
        align-items: center;
    }
    .testi-card .tc-author .tc-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: var(--accent-light);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
        flex-shrink: 0;
        margin-right: 12px;
    }
    .testi-card .tc-author h6 {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 1px;
    }
    .testi-card .tc-author small {
        font-size: 12px;
        color: var(--text);
    }
    .testi-card .tc-stars {
        position: absolute;
        top: 28px;
        right: 28px;
    }
    .testi-card .tc-stars i { color: var(--accent); font-size: 11px; }

    /* ================================================================
       11. CTA SECTION (Enroll style)
    ================================================================ */
    .cta-section {
        background: var(--accent);
        padding: 50px 0;
        position: relative;
        overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 250px;
        height: 250px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }
    .cta-section::after {
        content: '';
        position: absolute;
        left: -40px;
        bottom: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
    }
    .cta-section h2 {
        color: var(--white);
        font-size: 26px;
        font-weight: 800;
        position: relative;
        z-index: 1;
    }
    .cta-section p {
        color: rgba(255,255,255,.85);
        font-size: 14px;
        position: relative;
        z-index: 1;
    }
    .btn-cta-white {
        display: inline-block;
        background: var(--white);
        color: var(--accent);
        padding: 13px 32px;
        border-radius: 3px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .5px;
        border: 2px solid var(--white);
        transition: .3s;
        position: relative;
        z-index: 1;
    }
    .btn-cta-white:hover {
        background: var(--primary);
        border-color: var(--primary);
        color: var(--white);
    }

    /* ================================================================
       12. CONTACT SECTION
    ================================================================ */
    .contact-section { background: var(--white); }
    .contact-card {
        background: var(--light-bg);
        border-radius: 6px;
        padding: 30px 22px;
        text-align: center;
        height: 100%;
        border: 1px solid var(--border);
        transition: .3s;
    }
    .contact-card:hover {
        background: var(--white);
        box-shadow: 0 12px 30px rgba(0,0,0,.06);
        transform: translateY(-4px);
    }
    .contact-card .cc-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--accent-light);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin: 0 auto 15px;
        transition: .3s;
    }
    .contact-card:hover .cc-icon { background: var(--accent); color: var(--white); }
    .contact-card h5 { font-size: 15px; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
    .contact-card p { font-size: 13px; color: var(--text); margin: 0; line-height: 1.7; }
    .contact-card a { color: var(--text); }
    .contact-card a:hover { color: var(--accent); }

    /* ================================================================
       13. FOOTER (Enroll dark multi-column)
    ================================================================ */
    .site-footer {
        background: var(--dark);
        color: rgba(255,255,255,.65);
        padding: 60px 0 0;
    }
    .site-footer h5 {
        color: var(--white);
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 12px;
        position: relative;
    }
    .site-footer h5::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 35px;
        height: 3px;
        background: var(--accent);
    }
    .footer-about p { font-size: 13px; line-height: 1.8; color: rgba(255,255,255,.5); }
    .footer-links { list-style: none; padding: 0; margin: 0; }
    .footer-links li { margin-bottom: 8px; }
    .footer-links li a {
        color: rgba(255,255,255,.5);
        font-size: 13px;
        display: inline-flex;
        align-items: center;
    }
    .footer-links li a i {
        color: var(--accent);
        font-size: 6px;
        margin-right: 8px;
    }
    .footer-links li a:hover { color: var(--accent); padding-left: 4px; }

    .footer-contact .fc-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .footer-contact .fc-item .fc-icon {
        width: 34px;
        height: 34px;
        border-radius: 6px;
        background: rgba(247,148,29,.1);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
        margin-right: 12px;
        margin-top: 2px;
    }
    .footer-contact .fc-item p {
        font-size: 13px;
        color: rgba(255,255,255,.5);
        margin: 0;
        line-height: 1.7;
    }
    .footer-contact .fc-item a { color: rgba(255,255,255,.5); }
    .footer-contact .fc-item a:hover { color: var(--accent); }

    .footer-social a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 4px;
        background: rgba(255,255,255,.05);
        color: rgba(255,255,255,.5);
        font-size: 14px;
        margin-right: 5px;
        transition: .3s;
    }
    .footer-social a:hover {
        background: var(--accent);
        color: var(--white);
        transform: translateY(-2px);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,.06);
        padding: 18px 0;
        margin-top: 40px;
    }
    .footer-bottom p { font-size: 12px; color: rgba(255,255,255,.4); margin: 0; }
    .footer-bottom a { color: var(--accent); }
    .footer-bottom a:hover { color: var(--white); }

    /* ================================================================
       SCROLL TO TOP
    ================================================================ */
    .scroll-top {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 42px;
        height: 42px;
        background: var(--accent);
        color: var(--white);
        border: none;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: .3s;
        z-index: 999;
        box-shadow: 0 3px 12px rgba(247,148,29,.35);
    }
    .scroll-top.show { opacity: 1; visibility: visible; }
    .scroll-top:hover { background: var(--primary); transform: translateY(-2px); }

    /* Animations */
    .fade-in { opacity: 0; transform: translateY(25px); transition: .5s ease; }
    .fade-in.vis { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>

<!-- ===== 1. TOP BAR ===== -->
<div class="top-bar">
    <div class="container-fluid px-0">
        <div class="top-bar-inner">
            <div class="info-left d-flex align-items-center">
                <a href="tel:+60146488869"><i class="fas fa-phone-alt"></i>+60 14-648 8869</a>
                <a href="mailto:info@arenamatriks.com"><i class="fas fa-envelope"></i>info@arenamatriks.com</a>
                <span class="d-none d-lg-inline-flex"><i class="fas fa-clock"></i>Mon - Sat: 9:00 AM - 9:00 PM</span>
            </div>
            <div class="social-right d-flex">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- ===== 2. MAIN HEADER ===== -->
<header class="main-header">
    <div class="container">
        <div class="header-inner">
            <!-- Logo -->
            <a href="#" class="site-logo">
                <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="logo-text">
                    <h4>Arena Matriks</h4>
                    <small>Edu Group</small>
                </div>
            </a>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" id="navToggle"><i class="fas fa-bars"></i></button>

            <!-- Nav Area -->
            <div class="nav-area" id="navArea">
                <!-- Nav Links -->
                <ul class="main-nav">
                    <li><a href="#" class="active">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#courses">Courses</a></li>
                    <li><a href="#events">Events</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>

                <!-- Header Login Box (Enroll Signature Feature) -->
                <div class="header-login-box">
                    <a href="{{ route('login') }}" class="hlb-btn"><i class="fas fa-sign-in-alt me-1"></i>LOGIN</a>
                    <a href="{{ route('register') }}" class="hlb-link"><i class="fas fa-user-plus me-1"></i>Sign Up</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ===== 3. HERO SLIDER / FALLBACK ===== -->
@if($hasCarousel)
<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

        @if($carouselImages->count() > 1)
        <div class="carousel-indicators">
            @foreach($carouselImages as $i => $sl)
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}"
                    class="{{ $i===0?'active':'' }}"></button>
            @endforeach
        </div>
        @endif

        <div class="carousel-inner">
            @foreach($carouselImages as $i => $sl)
            <div class="carousel-item {{ $i===0?'active':'' }}">
                @if($sl->image_url)
                    <img src="{{ $sl->image_url }}" class="d-block" alt="Banner {{ $i+1 }}">
                @endif
                <div class="slide-overlay"></div>
                <div class="slide-caption">
                    <span class="caption-tag d-none d-md-inline-block">Welcome to Arena Matriks</span>
                    <h1 class="d-none d-md-block">Excellence in<br><span>Education</span> Starts Here</h1>
                    <p class="d-none d-md-block">Empowering students with quality education, experienced teachers and comprehensive learning management across Malaysia.</p>
                    <div class="d-none d-md-flex gap-3">
                        <a href="{{ route('register') }}" class="btn-enroll"><i class="fas fa-user-plus me-2"></i>Enroll Now</a>
                        <a href="#about" class="btn-enroll-outline"><i class="fas fa-info-circle me-2"></i>Learn More</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($carouselImages->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        @endif
    </div>
</section>
@else
<section class="hero-fallback">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="caption-tag" style="display:inline-block;background:var(--accent);color:#fff;padding:6px 20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px;border-radius:2px;margin-bottom:16px;">Welcome to Arena Matriks</span>
                <h1>Excellence in<br><span>Education</span> Starts Here</h1>
                <p>Empowering students with quality education, experienced teachers and a comprehensive tuition management system for outstanding results.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('login') }}" class="btn-enroll"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
                    <a href="{{ route('register') }}" class="btn-enroll-outline"><i class="fas fa-user-plus me-2"></i>Register</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- ===== 4. THREE INFO BOXES (Overlapping) ===== -->
<section class="info-boxes">
    <div class="container">
        <div class="row g-0">
            <div class="col-lg-4">
                <div class="info-box box-orange">
                    <div class="ib-icon"><i class="fas fa-book-open"></i></div>
                    <div>
                        <h5>Best Education</h5>
                        <p>Quality teaching methods with experienced educators for academic excellence.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="info-box box-navy">
                    <div class="ib-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div>
                        <h5>Expert Teachers</h5>
                        <p>Certified and experienced teachers dedicated to student success.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="info-box box-dark">
                    <div class="ib-icon"><i class="fas fa-laptop-code"></i></div>
                    <div>
                        <h5>Digital Platform</h5>
                        <p>Modern management system with online payments and real-time tracking.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== 5. ABOUT SECTION ===== -->
<section id="about" class="about-section section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 fade-in">
                <div class="about-img-block">
                    <div class="img-holder">
                        <div class="placeholder"><i class="fas fa-school"></i></div>
                    </div>
                    <div class="play-btn"><i class="fas fa-play" style="margin-left:3px;"></i></div>
                    <div class="exp-badge">
                        <div class="num">10+</div>
                        <div class="lbl">Years of<br>Excellence</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 fade-in">
                <div class="section-heading">
                    <div class="line-tag">About Us</div>
                    <h2>Welcome to <span>Arena Matriks</span> Edu Group</h2>
                    <p>We are dedicated to providing quality education and exceptional learning experiences to students across Malaysia.</p>
                </div>
                <p style="margin-bottom:8px;">With experienced teachers, modern facilities, and innovative technology, we help every student achieve their full academic potential and build a brighter future. Our comprehensive tuition management system ensures smooth operations, transparent communication, and outstanding academic results.</p>
                <ul class="about-list">
                    <li><i class="fas fa-check"></i>Experienced & Qualified Teachers</li>
                    <li><i class="fas fa-check"></i>Small Class Sizes for Personal Attention</li>
                    <li><i class="fas fa-check"></i>Flexible & Convenient Scheduling</li>
                    <li><i class="fas fa-check"></i>Proven Track Record of Academic Results</li>
                    <li><i class="fas fa-check"></i>Modern Digital Learning Tools & Materials</li>
                    <li><i class="fas fa-check"></i>Real-time Progress Updates for Parents</li>
                </ul>
                <a href="{{ route('register') }}" class="btn-enroll mt-2"><i class="fas fa-arrow-right me-2"></i>Read More</a>
            </div>
        </div>
    </div>
</section>

<!-- ===== 6. POPULAR COURSES / SUBJECTS ===== -->
<section id="courses" class="courses-section section-padding">
    <div class="container">
        <div class="section-heading center-heading text-center fade-in">
            <div class="line-tag">Our Programmes</div>
            <h2>Popular <span>Courses</span></h2>
            <p>Browse our comprehensive range of subjects and programmes designed for academic success.</p>
        </div>

        <!-- Filter Tabs -->
        <div class="course-filter fade-in">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="primary">Primary</button>
            <button class="filter-btn" data-filter="secondary">Secondary</button>
            <button class="filter-btn" data-filter="spm">SPM</button>
            <button class="filter-btn" data-filter="special">Special</button>
        </div>

        <div class="row g-4" id="courseGrid">
            <!-- Course 1 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="primary">
                <div class="course-card">
                    <div class="cc-img" style="background:#e3f2fd;">
                        <div class="cc-placeholder"><i class="fas fa-calculator"></i></div>
                        <div class="cc-price">RM 150</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">Primary</div>
                        <h5><a href="#">Mathematics (Primary)</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 45 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 2 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="secondary">
                <div class="course-card">
                    <div class="cc-img" style="background:#e8f5e9;">
                        <div class="cc-placeholder"><i class="fas fa-flask"></i></div>
                        <div class="cc-price">RM 180</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">Secondary</div>
                        <h5><a href="#">Science (Form 1-3)</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 38 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 3 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="spm">
                <div class="course-card">
                    <div class="cc-img" style="background:#fce4ec;">
                        <div class="cc-placeholder"><i class="fas fa-language"></i></div>
                        <div class="cc-price">RM 200</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">SPM</div>
                        <h5><a href="#">Bahasa Melayu SPM</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 52 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 4 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="spm">
                <div class="course-card">
                    <div class="cc-img" style="background:#fff3e0;">
                        <div class="cc-placeholder"><i class="fas fa-atom"></i></div>
                        <div class="cc-price">RM 220</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">SPM</div>
                        <h5><a href="#">Additional Maths SPM</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 40 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 5 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="primary">
                <div class="course-card">
                    <div class="cc-img" style="background:#e1f5fe;">
                        <div class="cc-placeholder"><i class="fas fa-book-reader"></i></div>
                        <div class="cc-price">RM 130</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">Primary</div>
                        <h5><a href="#">English (Primary)</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 35 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 6 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="secondary">
                <div class="course-card">
                    <div class="cc-img" style="background:#f3e5f5;">
                        <div class="cc-placeholder"><i class="fas fa-history"></i></div>
                        <div class="cc-price">RM 160</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">Secondary</div>
                        <h5><a href="#">Sejarah (Form 1-3)</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><i class="far fa-star"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 30 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 7 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="spm">
                <div class="course-card">
                    <div class="cc-img" style="background:#e0f2f1;">
                        <div class="cc-placeholder"><i class="fas fa-vial"></i></div>
                        <div class="cc-price">RM 200</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">SPM</div>
                        <h5><a href="#">Chemistry SPM</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 42 Students</div>
                    </div>
                </div>
            </div>

            <!-- Course 8 -->
            <div class="col-md-6 col-lg-3 course-item fade-in" data-cat="special">
                <div class="course-card">
                    <div class="cc-img" style="background:#fff8e1;">
                        <div class="cc-placeholder"><i class="fas fa-quran"></i></div>
                        <div class="cc-price">RM 120</div>
                    </div>
                    <div class="cc-body">
                        <div class="cc-category">Special</div>
                        <h5><a href="#">Mengaji & Islamic Studies</a></h5>
                    </div>
                    <div class="cc-meta">
                        <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <div class="students"><i class="fas fa-users"></i> 28 Students</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== 7. STATS COUNTER ===== -->
<section class="stats-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="si-icon"><i class="fas fa-users"></i></div>
                    <div class="si-num"><span class="counter" data-target="500">0</span><span class="suffix">+</span></div>
                    <div class="si-label">Active Students</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="si-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="si-num"><span class="counter" data-target="50">0</span><span class="suffix">+</span></div>
                    <div class="si-label">Expert Teachers</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="si-icon"><i class="fas fa-book-open"></i></div>
                    <div class="si-num"><span class="counter" data-target="30">0</span><span class="suffix">+</span></div>
                    <div class="si-label">Courses Offered</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 fade-in">
                <div class="stat-item">
                    <div class="si-icon"><i class="fas fa-trophy"></i></div>
                    <div class="si-num"><span class="counter" data-target="95">0</span><span class="suffix">%</span></div>
                    <div class="si-label">Success Rate</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== 8. EVENTS SECTION ===== -->
<section id="events" class="events-section section-padding">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6 fade-in">
                <div class="section-heading">
                    <div class="line-tag">Upcoming</div>
                    <h2>Latest <span>Events</span></h2>
                </div>
                <div class="event-item">
                    <div class="ei-date">
                        <div class="day">15</div>
                        <div class="month">Mar</div>
                    </div>
                    <div class="ei-body">
                        <h5>SPM Results Day Celebration</h5>
                        <p>Join us to celebrate the outstanding results of our SPM students.</p>
                        <div class="ei-meta"><i class="fas fa-map-marker-alt"></i> Main Hall, Shah Alam Centre</div>
                    </div>
                </div>
                <div class="event-item">
                    <div class="ei-date">
                        <div class="day">22</div>
                        <div class="month">Mar</div>
                    </div>
                    <div class="ei-body">
                        <h5>Open Day & Trial Classes</h5>
                        <p>Experience our teaching methods with free trial classes for all levels.</p>
                        <div class="ei-meta"><i class="fas fa-clock"></i> 10:00 AM - 4:00 PM</div>
                    </div>
                </div>
                <div class="event-item">
                    <div class="ei-date">
                        <div class="day">05</div>
                        <div class="month">Apr</div>
                    </div>
                    <div class="ei-body">
                        <h5>Parent-Teacher Conference</h5>
                        <p>Discuss your child's progress with our dedicated teaching team.</p>
                        <div class="ei-meta"><i class="fas fa-map-marker-alt"></i> All Branches</div>
                    </div>
                </div>
            </div>

            <!-- Testimonials Column -->
            <div class="col-lg-6 fade-in">
                <div class="section-heading">
                    <div class="line-tag">Testimonials</div>
                    <h2>What <span>Students</span> Say</h2>
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="testi-card">
                            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                            <p>Arena Matriks has transformed my academic performance. The teachers are incredibly supportive and the teaching methods really make complex subjects easy to understand.</p>
                            <div class="tc-author">
                                <div class="tc-avatar">NI</div>
                                <div>
                                    <h6>Nurul Izzah</h6>
                                    <small>SPM Student, 2024</small>
                                </div>
                            </div>
                            <div class="tc-stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="testi-card">
                            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                            <p>As a parent, I appreciate the real-time updates via WhatsApp. I can track attendance, payments, and my child's progress all in one system. Highly recommended!</p>
                            <div class="tc-author">
                                <div class="tc-avatar">AR</div>
                                <div>
                                    <h6>Ahmad Razak</h6>
                                    <small>Parent, Form 4 Student</small>
                                </div>
                            </div>
                            <div class="tc-stars">
                                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== 9. CTA SECTION ===== -->
<section class="cta-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <h2>Ready to Start Your Learning Journey?</h2>
                <p class="mb-0">Join hundreds of students already excelling at Arena Matriks Edu Group.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('register') }}" class="btn-cta-white"><i class="fas fa-user-plus me-2"></i>Enroll Now</a>
            </div>
        </div>
    </div>
</section>

<!-- ===== 10. CONTACT SECTION ===== -->
<section id="contact" class="contact-section section-padding">
    <div class="container">
        <div class="section-heading center-heading text-center fade-in">
            <div class="line-tag">Get In Touch</div>
            <h2>Contact <span>Us</span></h2>
            <p>Have questions about enrollment or our programmes? We're here to help!</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4 fade-in">
                <div class="contact-card">
                    <div class="cc-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <h5>Our Location</h5>
                    <p>No.7, Jalan Kemuning Prima B33/B<br>40400 Shah Alam, Selangor</p>
                </div>
            </div>
            <div class="col-md-4 fade-in">
                <div class="contact-card">
                    <div class="cc-icon"><i class="fas fa-phone-alt"></i></div>
                    <h5>Phone Number</h5>
                    <p><a href="tel:+60146488869">+60 14-648 8869</a><br><a href="tel:+60379723663">+60 3-7972 3663</a></p>
                </div>
            </div>
            <div class="col-md-4 fade-in">
                <div class="contact-card">
                    <div class="cc-icon"><i class="fas fa-envelope"></i></div>
                    <h5>Email Address</h5>
                    <p><a href="mailto:govind@graspsoftwaresolutions.com">govind@graspsoftwaresolutions.com</a><br><a href="mailto:info@arenamatriks.com">info@arenamatriks.com</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== 11. FOOTER ===== -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <!-- About -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-about">
                    <h5><i class="fas fa-graduation-cap me-2" style="color:var(--accent);"></i>Arena Matriks</h5>
                    <p>Excellence in education through quality teaching, modern technology, and comprehensive student support. Empowering the next generation of achievers across Malaysia.</p>
                    <div class="footer-social mt-3">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-circle"></i>Home</a></li>
                    <li><a href="#about"><i class="fas fa-circle"></i>About Us</a></li>
                    <li><a href="#courses"><i class="fas fa-circle"></i>Courses</a></li>
                    <li><a href="#events"><i class="fas fa-circle"></i>Events</a></li>
                    <li><a href="#contact"><i class="fas fa-circle"></i>Contact</a></li>
                    <li><a href="{{ route('login') }}"><i class="fas fa-circle"></i>Login</a></li>
                    <li><a href="{{ route('register') }}"><i class="fas fa-circle"></i>Register</a></li>
                </ul>
            </div>

            <!-- Programmes -->
            <div class="col-lg-3 col-md-6">
                <h5>Our Programmes</h5>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-circle"></i>Primary School</a></li>
                    <li><a href="#"><i class="fas fa-circle"></i>Secondary School</a></li>
                    <li><a href="#"><i class="fas fa-circle"></i>SPM Preparation</a></li>
                    <li><a href="#"><i class="fas fa-circle"></i>UPSR Classes</a></li>
                    <li><a href="#"><i class="fas fa-circle"></i>Islamic Studies</a></li>
                    <li><a href="#"><i class="fas fa-circle"></i>Tuition Classes</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h5>Contact Info</h5>
                <div class="footer-contact">
                    <div class="fc-item">
                        <div class="fc-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <p>No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor</p>
                    </div>
                    <div class="fc-item">
                        <div class="fc-icon"><i class="fas fa-phone-alt"></i></div>
                        <p><a href="tel:+60146488869">+60 14-648 8869</a><br><a href="tel:+60379723663">+60 3-7972 3663</a></p>
                    </div>
                    <div class="fc-item">
                        <div class="fc-icon"><i class="fas fa-envelope"></i></div>
                        <p><a href="mailto:info@arenamatriks.com">info@arenamatriks.com</a></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p>&copy; {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Developed by <a href="#">GRASP Software Solutions</a></p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top -->
<button class="scroll-top" id="scrollTop"><i class="fas fa-arrow-up"></i></button>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){

    // Mobile Nav Toggle
    $('#navToggle').on('click', function(){
        $('#navArea').toggleClass('open');
    });

    // Close mobile nav on link click
    $('.main-nav a').on('click', function(){
        $('#navArea').removeClass('open');
    });

    // Scroll Animations
    function checkFadeIn(){
        $('.fade-in').each(function(){
            if($(this).offset().top < $(window).scrollTop() + $(window).height() - 50){
                $(this).addClass('vis');
            }
        });
    }
    checkFadeIn();
    $(window).on('scroll', checkFadeIn);

    // Counter Animation
    var countered = false;
    function animateCounters(){
        if(countered) return;
        var sec = $('.stats-section');
        if(!sec.length) return;
        if($(window).scrollTop() + $(window).height() > sec.offset().top + 80){
            countered = true;
            $('.counter').each(function(){
                var $el = $(this), target = parseInt($el.data('target')), step = target / 120, cur = 0;
                var t = setInterval(function(){
                    cur += step;
                    if(cur >= target){ cur = target; clearInterval(t); }
                    $el.text(Math.floor(cur));
                }, 16);
            });
        }
    }
    $(window).on('scroll', animateCounters);
    animateCounters();

    // Course Filter
    $('.filter-btn').on('click', function(){
        var filter = $(this).data('filter');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        if(filter === 'all'){
            $('.course-item').fadeIn(300);
        } else {
            $('.course-item').fadeOut(200);
            $('.course-item[data-cat="'+filter+'"]').fadeIn(300);
        }
    });

    // Smooth Scroll
    $('a[href^="#"]').on('click', function(e){
        var t = $(this.getAttribute('href'));
        if(t.length){
            e.preventDefault();
            $('html,body').animate({scrollTop: t.offset().top - 70}, 600);
        }
    });

    // Scroll to Top
    $(window).on('scroll', function(){
        if($(this).scrollTop() > 400){
            $('#scrollTop').addClass('show');
        } else {
            $('#scrollTop').removeClass('show');
        }
    });
    $('#scrollTop').on('click', function(){
        $('html,body').animate({scrollTop:0}, 500);
    });

    // Active nav on scroll
    $(window).on('scroll', function(){
        var pos = $(this).scrollTop() + 100;
        $('section[id]').each(function(){
            var top = $(this).offset().top - 100;
            var bottom = top + $(this).outerHeight();
            var id = $(this).attr('id');
            if(pos >= top && pos < bottom){
                $('.main-nav a').removeClass('active');
                $('.main-nav a[href="#'+id+'"]').addClass('active');
            }
        });
    });
});
</script>
</body>
</html>
