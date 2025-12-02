<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Class Scheduled - Arena Matriks</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-item {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            width: 120px;
            color: #666;
            font-weight: 500;
        }
        .info-value {
            font-weight: 600;
            color: #333;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <h1>📚 Arena Matriks Edu Group</h1>
                <p>Trial Class Scheduled</p>
            </div>

            <div class="content">
                <p class="greeting">Dear {{ $trial_class->parent_name ?? 'Parent/Guardian' }},</p>

                <p>Great news! A trial class has been scheduled for <strong>{{ $trial_class->student_name ?? 'your child' }}</strong>.</p>

                <div class="info-box">
                    <h3 style="margin-top: 0; color: #667eea;">📋 Trial Class Details</h3>

                    <div class="info-item">
                        <span class="info-label">📖 Class:</span>
                        <span class="info-value">{{ $class->name ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">📚 Subject:</span>
                        <span class="info-value">{{ $class->subject->name ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">👨‍🏫 Teacher:</span>
                        <span class="info-value">{{ $class->teacher->user->name ?? 'TBA' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">📅 Date:</span>
                        <span class="info-value">{{ $trial_class->scheduled_date->format('l, d F Y') }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">⏰ Time:</span>
                        <span class="info-value">{{ $trial_class->scheduled_time ? $trial_class->scheduled_time->format('h:i A') : 'TBA' }}</span>
                    </div>

                    @if($class->type === 'online' && $class->meeting_link)
                    <div class="info-item">
                        <span class="info-label">🔗 Link:</span>
                        <span class="info-value"><a href="{{ $class->meeting_link }}">Join Online Class</a></span>
                    </div>
                    @else
                    <div class="info-item">
                        <span class="info-label">📍 Location:</span>
                        <span class="info-value">{{ $class->location ?? 'Arena Matriks Centre' }}</span>
                    </div>
                    @endif
                </div>

                <div class="highlight">
                    <strong>⏰ Please arrive 10 minutes early</strong><br>
                    This will give us time to welcome you and ensure everything is ready for the class.
                </div>

                <p>We're excited to have {{ $trial_class->student_name ?? 'your child' }} join us! This is a great opportunity to experience our teaching methodology and see if our program is the right fit.</p>

                <p>If you have any questions or need to reschedule, please don't hesitate to contact us.</p>

                <p style="margin-top: 30px;">
                    Best regards,<br>
                    <strong>Arena Matriks Edu Group Team</strong>
                </p>
            </div>

            <div class="footer">
                <p>
                    <strong>Arena Matriks Edu Group</strong><br>
                    📍 Wisma Arena Matriks, No.7, Jalan Kemuning Prima B33/B, 40400 Shah Alam, Selangor<br>
                    📞 03-7972 3663 | ✉️ info@arenamatriks.com
                </p>
                <p>
                    <a href="#">Unsubscribe</a> | <a href="#">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
