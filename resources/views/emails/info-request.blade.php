<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Additional Information Required</title>
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
            background: linear-gradient(135deg, #fda5300%, #4c4c4c 100%);
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
        .info-badge {
            display: inline-block;
            background-color: #ffc107;
            color: #333;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .student-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .request-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        .request-box h3 {
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
                <div class="info-badge">ℹ️ Information Required</div>

                <p>Dear Parent/Guardian,</p>

                <p>We are reviewing the registration for your child and require some additional information before we can proceed.</p>

                <div class="student-info">
                    <strong>Student:</strong> {{ $student_name }}<br>
                    <strong>Student ID:</strong> {{ $student_id }}
                </div>

                <div class="request-box">
                    <h3>📝 Information Needed</h3>
                    <p>{{ $info_request }}</p>
                </div>

                <p>Please provide the requested information as soon as possible so we can complete the registration process.</p>

                <div class="contact-box">
                    <h3>How to Respond</h3>
                    <p>You can provide the information by:</p>
                    <ul style="text-align: left; display: inline-block;">
                        <li>Replying to our WhatsApp message</li>
                        <li>Calling us at <strong>03-7972 3663</strong></li>
                        <li>Visiting our centre in person</li>
                    </ul>
                </div>

                <p>Thank you for your cooperation. We look forward to welcoming your child to Arena Matriks!</p>

                <p>
                    Best regards,<br>
                    <strong>{{ $requested_by }}</strong><br>
                    Arena Matriks Edu Group
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
