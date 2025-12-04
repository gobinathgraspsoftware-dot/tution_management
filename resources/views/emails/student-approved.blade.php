<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Approved</title>
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
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .success-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #fda530;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .info-box h3 {
            margin-top: 0;
            color: #fda530;
        }
        .info-item {
            margin: 10px 0;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .referral-box {
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .referral-code {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            background-color: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #fda530 0%, #4c4c4c 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
        }
        .next-steps {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .next-steps h3 {
            color: #0066cc;
            margin-top: 0;
        }
        .next-steps ul {
            padding-left: 20px;
        }
        .next-steps li {
            margin: 8px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .contact-info {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <div class="icon">🎓</div>
                <h1>Arena Matriks Edu Group</h1>
            </div>

            <div class="content">
                <div class="success-badge">✓ Registration Approved</div>

                <p>Dear {{ $parent_name }},</p>

                <p>Great news! We are pleased to inform you that your child's registration has been <strong>approved</strong>.</p>

                <div class="info-box">
                    <h3>Student Details</h3>
                    <div class="info-item">
                        <span class="info-label">Name:</span> {{ $student_name }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Student ID:</span> {{ $student_id }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span> {{ $student_email }}
                    </div>
                </div>

                <p style="text-align: center;">
                    <a href="{{ $login_url }}" class="btn">Login to Student Portal</a>
                </p>

                <div class="referral-box">
                    <p style="margin: 0;">🎁 Share & Earn RM50!</p>
                    <p style="margin: 5px 0;">Your referral code:</p>
                    <div class="referral-code">{{ $referral_code }}</div>
                    <p style="margin: 5px 0; font-size: 14px;">Share this code with friends and earn RM50 voucher for each successful referral!</p>
                </div>

                <div class="next-steps">
                    <h3>📋 Next Steps</h3>
                    <ul>
                        <li>Login to the student portal using the email and password set during registration</li>
                        <li>Check the class schedule and enrolled subjects</li>
                        <li>Review the payment details and make payment</li>
                        <li>Access study materials and resources</li>
                        <li>Contact us if you need any assistance</li>
                    </ul>
                </div>

                <p>If you have any questions or need assistance, please don't hesitate to contact us.</p>

                <p>Welcome to the Arena Matriks family! We're excited to be part of your child's educational journey.</p>

                <p>
                    Best regards,<br>
                    <strong>Arena Matriks Edu Group Team</strong>
                </p>
            </div>

            <div class="footer">
                <div class="contact-info">
                    <strong>{{ $centre_name }}</strong><br>
                    📍 {{ $centre_address }}<br>
                    📞 {{ $centre_phone }}
                </div>
                <p>This is an automated message. Please do not reply directly to this email.</p>
                <p>© {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
