<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Arena Matriks Edu Group</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-container {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 700px;
            text-align: center;
        }

        .success-icon {
            font-size: 5rem;
            color: #4caf50;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .info-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="mt-4 mb-3">Registration Successful!</h1>
        <p class="lead">Thank you for registering for our seminar.</p>
        
        <hr class="my-4">
        
        <div class="info-box">
            <h5 class="mb-3">Registration Details</h5>
            
            <div class="info-item">
                <span class="text-muted">Participant Name:</span>
                <strong>{{ $participant->name }}</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Email:</span>
                <strong>{{ $participant->email }}</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Phone:</span>
                <strong>{{ $participant->phone }}</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Seminar:</span>
                <strong>{{ $seminar->name }}</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Date:</span>
                <strong>{{ $seminar->date->format('l, d F Y') }}</strong>
            </div>
            
            @if($seminar->start_time)
            <div class="info-item">
                <span class="text-muted">Time:</span>
                <strong>{{ \Carbon\Carbon::parse($seminar->start_time)->format('h:i A') }}</strong>
            </div>
            @endif
            
            <div class="info-item">
                <span class="text-muted">Venue:</span>
                <strong>{{ $seminar->is_online ? 'Online Seminar' : $seminar->venue }}</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Registration Fee:</span>
                <strong class="text-primary">RM {{ number_format($participant->fee_amount, 2) }}</strong>
            </div>
            
            <div class="info-item">
                <span class="text-muted">Payment Status:</span>
                <span class="badge {{ $participant->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                    {{ ucfirst($participant->payment_status) }}
                </span>
            </div>
        </div>
        
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>What's Next?</strong>
            <ul class="text-start mt-2 mb-0">
                <li>A confirmation email has been sent to <strong>{{ $participant->email }}</strong></li>
                @if($participant->payment_status == 'pending')
                <li>Please complete your payment within 3 days to secure your spot</li>
                @endif
                @if($seminar->is_online)
                <li>You will receive the meeting link via email 1 day before the seminar</li>
                @else
                <li>Please arrive 15 minutes early on the seminar date</li>
                @endif
                <li>You will receive reminders 1 week and 1 day before the seminar</li>
            </ul>
        </div>
        
        @if($participant->payment_status == 'pending')
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Payment Required</strong><br>
            Please make payment to secure your registration. Bank details and payment instructions have been sent to your email.
        </div>
        @endif
        
        <div class="mt-4">
            <a href="{{ route('public.seminars.index') }}" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-arrow-left"></i> Browse More Seminars
            </a>
            <a href="/" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
        </div>
        
        <div class="mt-4">
            <p class="text-muted mb-0">
                <small>
                    Need help? Contact us at <a href="mailto:info@arenamatriks.com">info@arenamatriks.com</a>
                    or call <a href="tel:+60123456789">012-345 6789</a>
                </small>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
