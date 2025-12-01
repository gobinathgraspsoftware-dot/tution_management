<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arena Matriks Edu Group')</title>
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
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e9ecef;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin: 15px 0;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .alert-info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #1565c0;
        }
        .alert-warning {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            color: #e65100;
        }
        .alert-success {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }
        .divider {
            border: 0;
            border-top: 1px solid #e9ecef;
            margin: 20px 0;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.info-table td {
            padding: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        table.info-table td:first-child {
            color: #666;
            width: 40%;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                width: 100% !important;
            }
            .email-body {
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <h1>Arena Matriks Edu Group</h1>
            <p>Excellence in Education</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Arena Matriks Edu Group</strong></p>
            <p>
                <a href="{{ url('/') }}">Website</a> |
                <a href="tel:+60312345678">03-1234 5678</a> |
                <a href="mailto:info@arenamatriks.edu.my">info@arenamatriks.edu.my</a>
            </p>
            <p style="margin-top: 15px; color: #999;">
                This is an automated message from Arena Matriks Edu Group.<br>
                Please do not reply directly to this email.
            </p>
            <p style="color: #999;">&copy; {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
