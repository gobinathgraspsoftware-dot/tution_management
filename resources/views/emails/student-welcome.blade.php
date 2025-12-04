<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Arena Matriks!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-wrapper {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header .welcome-icon {
            font-size: 64px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-card h3 {
            margin-top: 0;
            color: #fda530;
            border-bottom: 2px solid #fda530;
            padding-bottom: 10px;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin: 12px 0;
        }
        .info-icon {
            width: 30px;
            text-align: center;
            margin-right: 10px;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 16px;
            margin: 15px 0;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .referral-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
        }
        .referral-section h3 {
            margin-top: 0;
        }
        .referral-code {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 3px;
            background-color: rgba(255,255,255,0.2);
            padding: 12px 25px;
            border-radius: 8px;
            display: inline-block;
            margin: 15px 0;
        }
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .feature-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .feature-item .icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .feature-item h4 {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 25px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #fda530;
            font-size: 20px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <div class="welcome-icon">🎉</div>
                <h1>Welcome to Arena Matriks!</h1>
                <p>Your learning journey begins now</p>
            </div>

            <div class="content">
                <p>Hi <strong>{{ $student_name }}</strong>,</p>

                <p>Congratulations! Your registration has been approved and you are now officially part of the Arena Matriks family!</p>

                <div class="info-card">
                    <h3>📋 Your Account Details</h3>
                    <div class="info-item">
                        <span class="info-icon">🎓</span>
                        <span><strong>Student ID:</strong> {{ $student_id }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">📧</span>
                        <span><strong>Email:</strong> {{ $student_email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">🔗</span>
                        <span><strong>Portal:</strong> {{ $login_url }}</span>
                    </div>
                </div>

                <p style="text-align: center;">
                    <a href="{{ $login_url }}" class="btn">🚀 Login to Your Portal</a>
                </p>

                <div class="features-grid">
                    <div class="feature-item">
                        <div class="icon">📚</div>
                        <h4>Study Materials</h4>
                    </div>
                    <div class="feature-item">
                        <div class="icon">📅</div>
                        <h4>Class Schedule</h4>
                    </div>
                    <div class="feature-item">
                        <div class="icon">📊</div>
                        <h4>Track Progress</h4>
                    </div>
                    <div class="feature-item">
                        <div class="icon">🎯</div>
                        <h4>Exam Results</h4>
                    </div>
                </div>

                <div class="referral-section">
                    <h3>🎁 Earn RM50 Per Referral!</h3>
                    <p>Share your unique code with friends and family:</p>
                    <div class="referral-code">{{ $referral_code }}</div>
                    <p style="font-size: 14px; margin-bottom: 0;">For every friend who joins using your code, you'll receive an RM50 voucher!</p>
                </div>

                <p>If you have any questions or need assistance, don't hesitate to reach out to us. Our team is here to support you every step of the way!</p>

                <p>Let's make this an amazing learning journey together! 🌟</p>

                <p>
                    Best regards,<br>
                    <strong>The Arena Matriks Team</strong>
                </p>
            </div>

            <div class="footer">
                <p><strong>{{ $centre_name }}</strong></p>
                <p>📍 {{ $centre_address }}</p>
                <p>📞 {{ $centre_phone }}</p>

                <div class="social-links">
                    <a href="#">📘</a>
                    <a href="#">📷</a>
                    <a href="#">🐦</a>
                </div>

                <p style="margin-top: 15px;">This is an automated message. Please do not reply directly to this email.</p>
                <p>© {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
