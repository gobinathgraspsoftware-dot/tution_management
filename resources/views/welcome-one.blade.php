<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Arena Matriks Edu Group - Empowering students with quality education. Tuition centre management, online payments, attendance tracking.">
    <title>Arena Matriks Edu Group - Excellence in Education</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @php
        $carouselImages = \App\Models\CarouselImage::active()->ordered()->get();
        $hasCarousel = $carouselImages->count() > 0;
    @endphp

    <style>
        :root {
            --primary:       #fda530;
            --primary-dark:  #e8941a;
            --primary-light: #ffecd2;
            --secondary:     #1a2a3a;
            --dark:          #0f1923;
            --accent:        #ff6b35;
            --text-dark:     #2c3e50;
            --text-muted:    #6c7a8a;
            --bg-light:      #f5f7fa;
            --bg-section:    #fafbfc;
            --white:         #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        /* ================================================================ */
        /*  TOP INFO BAR                                                    */
        /* ================================================================ */
        .top-bar {
            background: var(--secondary);
            color: rgba(255,255,255,0.8);
            font-size: 0.8rem;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .top-bar a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .top-bar a:hover {
            color: var(--primary);
        }

        .top-bar .separator {
            color: rgba(255,255,255,0.25);
            margin: 0 12px;
        }

        .top-bar .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
            font-size: 0.75rem;
            margin-left: 6px;
            transition: all 0.3s;
        }

        .top-bar .social-icons a:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .top-bar { display: none; }
        }

        /* ================================================================ */
        /*  NAVBAR                                                          */
        /* ================================================================ */
        .main-navbar {
            background: var(--white);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s;
        }

        .main-navbar .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--secondary) !important;
            padding: 12px 0;
        }

        .main-navbar .navbar-brand i {
            color: var(--primary);
            margin-right: 8px;
        }

        .main-navbar .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 22px 16px !important;
            position: relative;
            transition: color 0.3s;
        }

        .main-navbar .nav-link:hover,
        .main-navbar .nav-link.active {
            color: var(--primary) !important;
        }

        .main-navbar .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 16px;
            right: 16px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
            transform: scaleX(0);
            transition: transform 0.3s;
        }

        .main-navbar .nav-link:hover::after,
        .main-navbar .nav-link.active::after {
            transform: scaleX(1);
        }

        .btn-register-nav {
            background: transparent;
            color: var(--primary) !important;
            border: 2px solid var(--primary);
            border-radius: 6px;
            padding: 8px 20px !important;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-register-nav:hover {
            background: var(--primary);
            color: var(--white) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(253,165,48,0.35);
        }

        .btn-login-nav {
            background: var(--primary);
            color: var(--white) !important;
            border: 2px solid var(--primary);
            border-radius: 6px;
            padding: 8px 22px !important;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-login-nav:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(253,165,48,0.35);
        }

        @media (max-width: 991px) {
            .main-navbar .nav-link {
                padding: 10px 0 !important;
            }
            .main-navbar .nav-link::after {
                display: none;
            }
            .navbar-nav .nav-item.ms-lg-2 {
                margin-top: 8px;
            }
        }

        /* ================================================================ */
        /*  CAROUSEL / HERO                                                 */
        /* ================================================================ */
        .hero-carousel {
            position: relative;
            overflow: hidden;
        }

        .hero-carousel .carousel-item img {
            width: 100%;
            height: 560px;
            object-fit: cover;
            filter: brightness(0.85);
        }

        .hero-carousel .carousel-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15,25,35,0.55) 0%, rgba(253,165,48,0.15) 100%);
            z-index: 1;
        }

        .hero-carousel .carousel-caption {
            z-index: 2;
            bottom: 50%;
            transform: translateY(50%);
            text-align: left;
            left: 8%;
            right: 40%;
        }

        .hero-carousel .carousel-caption h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.8rem;
            font-weight: 800;
            text-shadow: 0 2px 20px rgba(0,0,0,0.3);
            line-height: 1.2;
            margin-bottom: 16px;
        }

        .hero-carousel .carousel-caption p {
            font-size: 1.1rem;
            opacity: 0.9;
            text-shadow: 0 1px 8px rgba(0,0,0,0.3);
            margin-bottom: 24px;
        }

        .hero-carousel .carousel-control-prev,
        .hero-carousel .carousel-control-next {
            width: 60px;
            height: 60px;
            top: 50%;
            transform: translateY(-50%);
            bottom: auto;
            background: rgba(253,165,48,0.85);
            border-radius: 50%;
            opacity: 0;
            margin: 0 20px;
            transition: opacity 0.3s;
            z-index: 5;
        }

        .hero-carousel:hover .carousel-control-prev,
        .hero-carousel:hover .carousel-control-next {
            opacity: 1;
        }

        .hero-carousel .carousel-control-prev:hover,
        .hero-carousel .carousel-control-next:hover {
            background: var(--primary);
        }

        .hero-carousel .carousel-indicators {
            z-index: 5;
            margin-bottom: 25px;
        }

        .hero-carousel .carousel-indicators button {
            width: 35px;
            height: 4px;
            border-radius: 4px;
            margin: 0 4px;
            background: rgba(255,255,255,0.5);
            border: none;
            transition: all 0.3s;
        }

        .hero-carousel .carousel-indicators button.active {
            background: var(--primary);
            width: 50px;
        }

        @media (max-width: 768px) {
            .hero-carousel .carousel-item img {
                height: 320px;
            }
            .hero-carousel .carousel-caption {
                right: 10%;
                left: 8%;
            }
            .hero-carousel .carousel-caption h2 {
                font-size: 1.5rem;
            }
            .hero-carousel .carousel-caption p {
                font-size: 0.85rem;
            }
            .hero-carousel .carousel-control-prev,
            .hero-carousel .carousel-control-next {
                width: 40px;
                height: 40px;
                margin: 0 8px;
            }
        }

        /* Hero Fallback (no carousel) */
        .hero-section {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--dark) 50%, var(--secondary) 100%);
            min-height: 85vh;
            display: flex;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(253,165,48,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(253,165,48,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-section .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
        }

        .hero-section h1 span {
            color: var(--primary);
        }

        .hero-section p.lead {
            font-size: 1.15rem;
            opacity: 0.85;
            line-height: 1.7;
            margin-bottom: 30px;
        }

        .btn-hero-primary {
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            padding: 14px 36px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-hero-primary:hover {
            background: var(--primary-dark);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(253,165,48,0.4);
        }

        .btn-hero-outline {
            background: transparent;
            color: var(--white);
            border: 2px solid rgba(255,255,255,0.4);
            border-radius: 8px;
            padding: 13px 36px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-hero-outline:hover {
            border-color: var(--white);
            background: rgba(255,255,255,0.1);
            color: var(--white);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .hero-section h1 { font-size: 2rem; }
        }

        /* ================================================================ */
        /*  QUICK INFO STRIP (below carousel)                               */
        /* ================================================================ */
        .quick-info {
            background: var(--secondary);
            padding: 0;
            position: relative;
            z-index: 10;
        }

        .quick-info-item {
            display: flex;
            align-items: center;
            padding: 22px 30px;
            border-right: 1px solid rgba(255,255,255,0.08);
            transition: background 0.3s;
        }

        .quick-info-item:last-child {
            border-right: none;
        }

        .quick-info-item:hover {
            background: rgba(253,165,48,0.1);
        }

        .quick-info-item .qi-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(253,165,48,0.15);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .quick-info-item .qi-text h6 {
            color: var(--white);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .quick-info-item .qi-text p {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            margin: 0;
        }

        @media (max-width: 991px) {
            .quick-info-item {
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.08);
                padding: 15px 20px;
            }
        }

        /* ================================================================ */
        /*  SECTION COMMON                                                  */
        /* ================================================================ */
        .section-padding {
            padding: 80px 0;
        }

        .section-title {
            margin-bottom: 50px;
        }

        .section-title .subtitle-tag {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary-dark);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 6px 18px;
            border-radius: 50px;
            margin-bottom: 14px;
        }

        .section-title h2 {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1.25;
        }

        .section-title h2 span {
            color: var(--primary);
        }

        .section-title p {
            color: var(--text-muted);
            font-size: 1.05rem;
            max-width: 600px;
            margin: 12px auto 0;
        }

        /* ================================================================ */
        /*  FEATURES SECTION                                                */
        /* ================================================================ */
        .features-section {
            background: var(--bg-light);
        }

        .feature-card {
            background: var(--white);
            border-radius: 16px;
            padding: 35px 28px;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
            transform: scaleX(0);
            transition: transform 0.4s;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card .f-icon {
            width: 68px;
            height: 68px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .feature-card:hover .f-icon {
            transform: scale(1.1);
        }

        .feature-card h5 {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.65;
            margin: 0;
        }

        /* ================================================================ */
        /*  STATS COUNTER                                                   */
        /* ================================================================ */
        .stats-section {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--dark) 100%);
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .stat-block {
            text-align: center;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        .stat-block .stat-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(253,165,48,0.15);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 18px;
            border: 2px solid rgba(253,165,48,0.25);
        }

        .stat-block .stat-count {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--white);
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-block .stat-count .counter-suffix {
            color: var(--primary);
            font-size: 2rem;
        }

        .stat-block .stat-label {
            color: rgba(255,255,255,0.65);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* ================================================================ */
        /*  ABOUT SECTION                                                   */
        /* ================================================================ */
        .about-section {
            background: var(--white);
        }

        .about-image-wrap {
            position: relative;
        }

        .about-image-wrap .about-main-img {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }

        .about-image-wrap .about-main-img .img-placeholder {
            width: 100%;
            height: 420px;
            background: linear-gradient(135deg, var(--primary-light) 0%, #fce4b8 50%, var(--primary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8rem;
            color: rgba(253,165,48,0.3);
        }

        .about-experience-badge {
            position: absolute;
            bottom: -20px;
            right: -10px;
            background: var(--primary);
            color: var(--white);
            padding: 20px 28px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(253,165,48,0.4);
        }

        .about-experience-badge .years {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1;
        }

        .about-experience-badge .text {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .about-checklist {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .about-checklist li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 14px;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .about-checklist li .check-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            flex-shrink: 0;
            margin-right: 12px;
            margin-top: 2px;
        }

        /* ================================================================ */
        /*  WHY CHOOSE US                                                   */
        /* ================================================================ */
        .why-section {
            background: var(--bg-light);
        }

        .why-card {
            background: var(--white);
            border-radius: 16px;
            padding: 30px 25px;
            text-align: center;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.04);
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            transition: all 0.3s;
        }

        .why-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.08);
            border-color: rgba(253,165,48,0.2);
        }

        .why-card .wc-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 18px;
            transition: all 0.3s;
        }

        .why-card:hover .wc-icon {
            background: var(--primary);
            color: var(--white);
            transform: rotateY(180deg);
        }

        .why-card h6 {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--secondary);
            margin-bottom: 8px;
        }

        .why-card p {
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.6;
            margin: 0;
        }

        /* ================================================================ */
        /*  CONTACT SECTION                                                 */
        /* ================================================================ */
        .contact-section {
            background: var(--white);
        }

        .contact-card {
            background: var(--bg-light);
            border-radius: 16px;
            padding: 35px 25px;
            text-align: center;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s;
        }

        .contact-card:hover {
            background: var(--white);
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            transform: translateY(-5px);
        }

        .contact-card .cc-icon {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin: 0 auto 18px;
            transition: all 0.3s;
        }

        .contact-card:hover .cc-icon {
            background: var(--primary);
            color: var(--white);
        }

        .contact-card h5 {
            font-weight: 700;
            font-size: 1rem;
            color: var(--secondary);
            margin-bottom: 10px;
        }

        .contact-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.7;
            margin: 0;
        }

        .contact-card a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s;
        }

        .contact-card a:hover {
            color: var(--primary);
        }

        /* ================================================================ */
        /*  CTA BANNER                                                      */
        /* ================================================================ */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .cta-section h2 {
            color: var(--white);
            font-size: 2rem;
            font-weight: 800;
        }

        .cta-section p {
            color: rgba(255,255,255,0.85);
            font-size: 1.05rem;
        }

        .btn-cta-white {
            background: var(--white);
            color: var(--primary-dark);
            border: none;
            border-radius: 8px;
            padding: 14px 36px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .btn-cta-white:hover {
            background: var(--secondary);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        /* ================================================================ */
        /*  FOOTER                                                          */
        /* ================================================================ */
        .main-footer {
            background: var(--secondary);
            color: rgba(255,255,255,0.8);
            padding: 60px 0 0;
        }

        .main-footer h5 {
            color: var(--white);
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 22px;
            position: relative;
            padding-bottom: 12px;
        }

        .main-footer h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .footer-about p {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
        }

        .footer-links a i {
            color: var(--primary);
            margin-right: 8px;
            font-size: 0.55rem;
        }

        .footer-links a:hover {
            color: var(--primary);
            padding-left: 6px;
        }

        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .footer-contact-item .fci-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(253,165,48,0.12);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            margin-right: 12px;
            margin-top: 2px;
        }

        .footer-contact-item p {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            line-height: 1.6;
            margin: 0;
        }

        .footer-contact-item a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
        }

        .footer-contact-item a:hover {
            color: var(--primary);
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
            margin-right: 8px;
            transition: all 0.3s;
        }

        .footer-social a:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 20px 0;
            margin-top: 40px;
        }

        .footer-bottom p {
            color: rgba(255,255,255,0.5);
            font-size: 0.82rem;
            margin: 0;
        }

        .footer-bottom a {
            color: var(--primary);
            text-decoration: none;
        }

        .footer-bottom a:hover {
            color: var(--white);
        }

        /* ================================================================ */
        /*  ANIMATIONS                                                      */
        /* ================================================================ */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Scroll to top */
        .scroll-top-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: var(--primary);
            color: var(--white);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 999;
            box-shadow: 0 4px 15px rgba(253,165,48,0.4);
        }

        .scroll-top-btn.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top-btn:hover {
            background: var(--secondary);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>

    <!-- ============================================================== -->
    <!-- TOP INFO BAR                                                    -->
    <!-- ============================================================== -->
    <div class="top-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="tel:+60146488869"><i class="fas fa-phone-alt me-1"></i> +60 14-648 8869</a>
                    <span class="separator">|</span>
                    <a href="mailto:info@arenamatriks.com"><i class="fas fa-envelope me-1"></i> info@arenamatriks.com</a>
                    <span class="separator">|</span>
                    <span><i class="fas fa-clock me-1"></i> Mon - Sat: 9:00 AM - 9:00 PM</span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="social-icons">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MAIN NAVBAR                                                     -->
    <!-- ============================================================== -->
    <nav class="navbar navbar-expand-lg main-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap"></i>Arena Matriks
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                    <a href="{{ route('register') }}" class="btn btn-register-nav">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-login-nav">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ============================================================== -->
    <!-- CAROUSEL / HERO SECTION                                         -->
    <!-- ============================================================== -->
    @if($hasCarousel)
    <section class="hero-carousel">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">

            {{-- Indicators --}}
            @if($carouselImages->count() > 1)
            <div class="carousel-indicators">
                @foreach($carouselImages as $index => $slide)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}"
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
            @endif

            {{-- Slides --}}
            <div class="carousel-inner">
                @foreach($carouselImages as $index => $slide)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    @if($slide->image_url)
                        <img src="{{ $slide->image_url }}" class="d-block w-100" alt="Banner {{ $index + 1 }}">
                    @endif
                    <div class="carousel-overlay"></div>
                    <div class="carousel-caption d-none d-md-block">
                        <h2>Welcome to Arena Matriks Edu Group</h2>
                        <p>Empowering students with quality education and comprehensive learning support across Malaysia.</p>
                        <div class="d-flex gap-3">
                            <a href="{{ route('register') }}" class="btn btn-hero-primary">
                                <i class="fas fa-user-plus me-2"></i>Get Started
                            </a>
                            <a href="#about" class="btn btn-hero-outline">
                                <i class="fas fa-play-circle me-2"></i>Learn More
                            </a>
                        </div>
                    </div>
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
    {{-- Fallback Hero --}}
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 hero-content">
                    <div class="subtitle-tag mb-3" style="background: rgba(253,165,48,0.2); color: var(--primary); display:inline-block; padding:6px 18px; border-radius:50px; font-size:0.8rem; font-weight:600; letter-spacing:1px;">
                        WELCOME TO ARENA MATRIKS
                    </div>
                    <h1>Excellence in <span>Education</span> Starts Here</h1>
                    <p class="lead">Empowering students with quality education, experienced teachers, and a comprehensive tuition management system for outstanding academic results.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('login') }}" class="btn btn-hero-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-hero-outline">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ============================================================== -->
    <!-- QUICK INFO STRIP                                                -->
    <!-- ============================================================== -->
    <section class="quick-info">
        <div class="container-fluid px-0">
            <div class="row g-0">
                <div class="col-lg-4">
                    <div class="quick-info-item">
                        <div class="qi-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="qi-text">
                            <h6>Call Us Anytime</h6>
                            <p>+60 14-648 8869 / +60 3-7972 3663</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="quick-info-item">
                        <div class="qi-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="qi-text">
                            <h6>Visit Our Centre</h6>
                            <p>No.7, Jalan Kemuning Prima B33/B, Shah Alam</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="quick-info-item">
                        <div class="qi-icon"><i class="fas fa-clock"></i></div>
                        <div class="qi-text">
                            <h6>Operating Hours</h6>
                            <p>Mon - Sat: 9:00 AM - 9:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- FEATURES SECTION                                                -->
    <!-- ============================================================== -->
    <section id="features" class="features-section section-padding">
        <div class="container">
            <div class="section-title text-center fade-up">
                <div class="subtitle-tag">What We Offer</div>
                <h2>Our <span>Features</span></h2>
                <p>Everything you need to manage your tuition centre effectively and deliver excellent education.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="feature-card">
                        <div class="f-icon" style="background: #e3f2fd; color: #2196f3;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5>Student Management</h5>
                        <p>Complete student enrollment, profile management, attendance tracking, and academic progress monitoring system.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="feature-card">
                        <div class="f-icon" style="background: #e8f5e9; color: #4caf50;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h5>Payment & Billing</h5>
                        <p>Automated invoice generation, online payment processing via Malaysian gateways, and comprehensive financial reports.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="feature-card">
                        <div class="f-icon" style="background: #f3e5f5; color: #9c27b0;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5>Attendance Tracking</h5>
                        <p>Real-time attendance marking with instant parent notifications via WhatsApp and comprehensive attendance reports.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="feature-card">
                        <div class="f-icon" style="background: #fff3e0; color: #ff9800;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5>Smart Notifications</h5>
                        <p>Multi-channel notifications via WhatsApp, email, and SMS for attendance, payments, announcements, and more.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="feature-card">
                        <div class="f-icon" style="background: #e1f5fe; color: #03a9f4;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h5>Class Management</h5>
                        <p>Schedule classes, manage teachers, assign subjects, and coordinate educational programs all in one place.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 fade-up">
                    <div class="feature-card">
                        <div class="f-icon" style="background: #ffebee; color: #f44336;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Reports & Analytics</h5>
                        <p>Comprehensive reporting on revenue, expenses, attendance, academic performance, and business intelligence.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- STATS SECTION                                                   -->
    <!-- ============================================================== -->
    <section class="stats-section section-padding">
        <div class="container">
            <div class="row">
                <div class="col-6 col-lg-3 fade-up">
                    <div class="stat-block">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-count"><span class="counter" data-target="500">0</span><span class="counter-suffix">+</span></div>
                        <div class="stat-label">Active Students</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 fade-up">
                    <div class="stat-block">
                        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div class="stat-count"><span class="counter" data-target="50">0</span><span class="counter-suffix">+</span></div>
                        <div class="stat-label">Expert Teachers</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 fade-up">
                    <div class="stat-block">
                        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                        <div class="stat-count"><span class="counter" data-target="30">0</span><span class="counter-suffix">+</span></div>
                        <div class="stat-label">Active Classes</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3 fade-up">
                    <div class="stat-block">
                        <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                        <div class="stat-count"><span class="counter" data-target="95">0</span><span class="counter-suffix">%</span></div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- ABOUT SECTION                                                   -->
    <!-- ============================================================== -->
    <section id="about" class="about-section section-padding">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-up">
                    <div class="about-image-wrap">
                        <div class="about-main-img">
                            <div class="img-placeholder">
                                <i class="fas fa-school"></i>
                            </div>
                        </div>
                        <div class="about-experience-badge">
                            <div class="years">10+</div>
                            <div class="text">Years of<br>Excellence</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 fade-up">
                    <div class="subtitle-tag">About Us</div>
                    <h2 class="mb-3" style="font-size:2rem; color: var(--secondary);">About <span style="color: var(--primary);">Arena Matriks</span> Edu Group</h2>
                    <p class="mb-3" style="color: var(--text-muted); line-height:1.8;">
                        We are dedicated to providing quality education and exceptional learning experiences to students across Malaysia. Our comprehensive tuition management system ensures smooth operations, transparent communication, and outstanding academic results.
                    </p>
                    <p class="mb-4" style="color: var(--text-muted); line-height:1.8;">
                        With experienced teachers, modern facilities, and innovative technology, we help every student achieve their full academic potential and build a brighter future.
                    </p>
                    <ul class="about-checklist">
                        <li>
                            <span class="check-icon"><i class="fas fa-check"></i></span>
                            Experienced & Qualified Teachers
                        </li>
                        <li>
                            <span class="check-icon"><i class="fas fa-check"></i></span>
                            Small Class Sizes for Personal Attention
                        </li>
                        <li>
                            <span class="check-icon"><i class="fas fa-check"></i></span>
                            Flexible & Convenient Scheduling
                        </li>
                        <li>
                            <span class="check-icon"><i class="fas fa-check"></i></span>
                            Proven Track Record of Academic Results
                        </li>
                        <li>
                            <span class="check-icon"><i class="fas fa-check"></i></span>
                            Modern Digital Learning Tools & Materials
                        </li>
                        <li>
                            <span class="check-icon"><i class="fas fa-check"></i></span>
                            Real-time Progress Updates for Parents
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- WHY CHOOSE US                                                   -->
    <!-- ============================================================== -->
    <section class="why-section section-padding">
        <div class="container">
            <div class="section-title text-center fade-up">
                <div class="subtitle-tag">Why Choose Us</div>
                <h2>What Makes Us <span>Different</span></h2>
                <p>We combine traditional teaching excellence with modern technology for the best outcomes.</p>
            </div>
            <div class="row g-4">
                <div class="col-sm-6 col-lg-3 fade-up">
                    <div class="why-card">
                        <div class="wc-icon"><i class="fas fa-award"></i></div>
                        <h6>Certified Teachers</h6>
                        <p>All teachers are certified with years of teaching experience in their subjects.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 fade-up">
                    <div class="why-card">
                        <div class="wc-icon"><i class="fas fa-laptop-code"></i></div>
                        <h6>Digital Platform</h6>
                        <p>Modern management system with online payments, e-materials, and real-time tracking.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 fade-up">
                    <div class="why-card">
                        <div class="wc-icon"><i class="fas fa-comments"></i></div>
                        <h6>Parent Engagement</h6>
                        <p>Regular updates via WhatsApp on attendance, performance, and announcements.</p>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3 fade-up">
                    <div class="why-card">
                        <div class="wc-icon"><i class="fas fa-shield-alt"></i></div>
                        <h6>Safe Environment</h6>
                        <p>Secure and comfortable learning environment with modern facilities and resources.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- CONTACT SECTION                                                 -->
    <!-- ============================================================== -->
    <section id="contact" class="contact-section section-padding">
        <div class="container">
            <div class="section-title text-center fade-up">
                <div class="subtitle-tag">Get In Touch</div>
                <h2>Contact <span>Us</span></h2>
                <p>Have questions about enrollment or our programmes? We're here to help!</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 fade-up">
                    <div class="contact-card">
                        <div class="cc-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <h5>Our Location</h5>
                        <p>No.7, Jalan Kemuning Prima B33/B<br>40400 Shah Alam, Selangor</p>
                    </div>
                </div>
                <div class="col-md-4 fade-up">
                    <div class="contact-card">
                        <div class="cc-icon"><i class="fas fa-phone-alt"></i></div>
                        <h5>Phone Number</h5>
                        <p>
                            <a href="tel:+60146488869">+60 14-648 8869</a><br>
                            <a href="tel:+60379723663">+60 3-7972 3663</a>
                        </p>
                    </div>
                </div>
                <div class="col-md-4 fade-up">
                    <div class="contact-card">
                        <div class="cc-icon"><i class="fas fa-envelope"></i></div>
                        <h5>Email Address</h5>
                        <p>
                            <a href="mailto:govind@graspsoftwaresolutions.com">govind@graspsoftwaresolutions.com</a><br>
                            <a href="mailto:info@arenamatriks.com">info@arenamatriks.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- CTA BANNER                                                      -->
    <!-- ============================================================== -->
    <section class="cta-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <h2>Ready to Start Your Learning Journey?</h2>
                    <p class="mb-0">Join hundreds of students already excelling at Arena Matriks Edu Group.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('register') }}" class="btn btn-cta-white">
                        <i class="fas fa-user-plus me-2"></i>Enroll Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- FOOTER                                                          -->
    <!-- ============================================================== -->
    <footer class="main-footer">
        <div class="container">
            <div class="row g-4">
                {{-- About Column --}}
                <div class="col-lg-4 col-md-6">
                    <div class="footer-about">
                        <h5><i class="fas fa-graduation-cap me-2" style="color: var(--primary);"></i>Arena Matriks</h5>
                        <p>Excellence in education through quality teaching, modern technology, and comprehensive student support. Empowering the next generation of achievers.</p>
                        <div class="footer-social mt-3">
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="col-lg-2 col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-circle"></i> Home</a></li>
                        <li><a href="#features"><i class="fas fa-circle"></i> Features</a></li>
                        <li><a href="#about"><i class="fas fa-circle"></i> About Us</a></li>
                        <li><a href="#contact"><i class="fas fa-circle"></i> Contact</a></li>
                        <li><a href="{{ route('login') }}"><i class="fas fa-circle"></i> Login</a></li>
                        <li><a href="{{ route('register') }}"><i class="fas fa-circle"></i> Register</a></li>
                    </ul>
                </div>

                {{-- Programmes --}}
                <div class="col-lg-3 col-md-6">
                    <h5>Our Programmes</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-circle"></i> Primary School</a></li>
                        <li><a href="#"><i class="fas fa-circle"></i> Secondary School</a></li>
                        <li><a href="#"><i class="fas fa-circle"></i> SPM Preparation</a></li>
                        <li><a href="#"><i class="fas fa-circle"></i> UPSR Classes</a></li>
                        <li><a href="#"><i class="fas fa-circle"></i> Tuition Classes</a></li>
                    </ul>
                </div>

                {{-- Contact Info --}}
                <div class="col-lg-3 col-md-6">
                    <h5>Contact Info</h5>
                    <div class="footer-contact-item">
                        <div class="fci-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <p>No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor</p>
                    </div>
                    <div class="footer-contact-item">
                        <div class="fci-icon"><i class="fas fa-phone-alt"></i></div>
                        <p>
                            <a href="tel:+60146488869">+60 14-648 8869</a><br>
                            <a href="tel:+60379723663">+60 3-7972 3663</a>
                        </p>
                    </div>
                    <div class="footer-contact-item">
                        <div class="fci-icon"><i class="fas fa-envelope"></i></div>
                        <p><a href="mailto:info@arenamatriks.com">info@arenamatriks.com</a></p>
                    </div>
                </div>
            </div>

            {{-- Footer Bottom --}}
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

    <!-- Scroll to Top Button -->
    <button class="scroll-top-btn" id="scrollTopBtn" title="Back to Top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function() {

        /* ============================================================ */
        /*  SCROLL ANIMATIONS (fade-up)                                 */
        /* ============================================================ */
        function checkFadeUp() {
            $('.fade-up').each(function() {
                var top = $(this).offset().top;
                var bottom = $(window).scrollTop() + $(window).height();
                if (top < bottom - 60) {
                    $(this).addClass('visible');
                }
            });
        }
        checkFadeUp();
        $(window).on('scroll', checkFadeUp);

        /* ============================================================ */
        /*  ANIMATED COUNTER                                            */
        /* ============================================================ */
        var countersAnimated = false;

        function animateCounters() {
            if (countersAnimated) return;
            var statsTop = $('.stats-section').offset().top;
            var scrollBottom = $(window).scrollTop() + $(window).height();

            if (scrollBottom > statsTop + 100) {
                countersAnimated = true;
                $('.counter').each(function() {
                    var $this = $(this);
                    var target = parseInt($this.data('target'));
                    var duration = 2000;
                    var step = target / (duration / 16);
                    var current = 0;

                    var timer = setInterval(function() {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        $this.text(Math.floor(current));
                    }, 16);
                });
            }
        }
        $(window).on('scroll', animateCounters);
        animateCounters();

        /* ============================================================ */
        /*  SMOOTH SCROLL for anchor links                              */
        /* ============================================================ */
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({ scrollTop: target.offset().top - 70 }, 600);
            }
        });

        /* ============================================================ */
        /*  SCROLL TO TOP BUTTON                                        */
        /* ============================================================ */
        $(window).on('scroll', function() {
            if ($(this).scrollTop() > 400) {
                $('#scrollTopBtn').addClass('show');
            } else {
                $('#scrollTopBtn').removeClass('show');
            }
        });

        $('#scrollTopBtn').on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });

        /* ============================================================ */
        /*  NAVBAR active state on scroll                               */
        /* ============================================================ */
        $(window).on('scroll', function() {
            var scrollPos = $(this).scrollTop() + 100;
            $('section[id]').each(function() {
                var top = $(this).offset().top - 100;
                var bottom = top + $(this).outerHeight();
                var id = $(this).attr('id');
                if (scrollPos >= top && scrollPos < bottom) {
                    $('.main-navbar .nav-link').removeClass('active');
                    $('.main-navbar .nav-link[href="#' + id + '"]').addClass('active');
                }
            });
        });
    });
    </script>
</body>
</html>
