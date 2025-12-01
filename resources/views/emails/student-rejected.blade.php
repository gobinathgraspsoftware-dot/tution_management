<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Status</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .reason-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .reason-box h3 {
            margin-top: 0;
            color: #856404;
        }
        .contact-box {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .contact-box h3 {
            color: #0066cc;
            margin-top: 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-wrapper">
            <div class="header">
                <h1>Arena Matriks Edu Group</h1>
            </div>

            <div class="content">
                <div class="status-badge">Registration Not Approved</div>

                <p>Dear Parent/Guardian,</p>

                <p>We regret to inform you that the registration for <strong>{{ $student_name }}</strong> was not approved at this time.</p>

                <div class="reason-box">
                    <h3>Reason</h3>
                    <p>{{ $rejection_reason }}</p>
                </div>

                <p>We understand this may be disappointing news. If you believe there has been an error or if you have additional information that may support the application, please don't hesitate to contact us.</p>

                <div class="contact-box">
                    <h3>Need Help?</h3>
                    <p>Contact us to discuss this further:</p>
                    <p>
                        <strong>📞 {{ $centre_phone }}</strong><br>
                        <strong>✉️ info@arenamatriks.com</strong>
                    </p>
                    <p style="font-size: 14px; color: #666;">Our team is available Monday - Saturday, 9:00 AM - 6:00 PM</p>
                </div>

                <p>You may also submit a new application with the required corrections or additional documentation.</p>

                <p>Thank you for your interest in Arena Matriks Edu Group.</p>

                <p>
                    Best regards,<br>
                    <strong>Arena Matriks Edu Group Team</strong>
                </p>
            </div>

            <div class="footer">
                <p>This is an automated message. Please do not reply directly to this email.</p>
                <p>© {{ date('Y') }} Arena Matriks Edu Group. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
