<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification' }} - Arena Matriks</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #fda5300%, #4c4c4c 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .email-header .tagline {
            font-size: 13px;
            opacity: 0.9;
        }
        .email-body {
            padding: 35px;
        }
        .email-body h2 {
            color: #333;
            margin-top: 0;
            font-size: 22px;
        }
        .content-block {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #fda530;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .email-footer a {
            color: #fda530;
            text-decoration: none;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #fda530;
        }
        .btn-primary {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #fda5300%, #4c4c4c 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .contact-info p {
            margin: 5px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                margin: 0;
                border-radius: 0;
            }
            .email-body {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">Arena Matriks</div>
            <div class="tagline">Edu Group</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            @if(isset($subject))
            <h2>{{ $subject }}</h2>
            @endif

            <div class="content-block">
                {!! nl2br(e($body ?? '')) !!}
            </div>

            @if(isset($actionUrl) && isset($actionText))
            <div style="text-align: center;">
                <a href="{{ $actionUrl }}" class="btn-primary">{{ $actionText }}</a>
            </div>
            @endif

            <div class="contact-info">
                <p><strong>Need help?</strong></p>
                <p>Contact us at <a href="tel:+60312345678">03-1234 5678</a> or email <a href="mailto:info@arenamatriks.edu.my">info@arenamatriks.edu.my</a></p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>{{ $centerName ?? 'Arena Matriks Edu Group' }}</strong></p>
            <p>Your trusted partner in education excellence</p>

            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Instagram</a> |
                <a href="#">Website</a>
            </div>

            <p style="margin-top: 20px; color: #999; font-size: 11px;">
                This is an automated message from Arena Matriks Edu Group.<br>
                Please do not reply directly to this email.<br><br>
                &copy; {{ $year ?? date('Y') }} Arena Matriks Edu Group. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
