<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to EcoConnect - DENR CENRO Sanchez Mira</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f9f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: #1a472a;
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
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background: #1a472a;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-box {
            background: #f0f9f0;
            border-left: 4px solid #1a472a;
            padding: 15px;
            margin: 20px 0;
        }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🌿 Welcome to EcoConnect</h1>
            <p>DENR CENRO Sanchez Mira</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Welcome, {{ $user->fname }}!</h2>
            
            <p>Thank you for registering with EcoConnect - the environmental incident reporting system of DENR CENRO Sanchez Mira.</p>
            
            <div class="user-info">
                <h3>Your Account Details:</h3>
                <p><strong>Name:</strong> {{ $user->fname }} {{ $user->mname ? $user->mname . ' ' : '' }}{{ $user->lname }}{{ $user->extname ? ' ' . $user->extname : '' }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone ?? 'Not provided' }}</p>
                <p><strong>Registration Date:</strong> {{ $user->created_at->format('F j, Y') }}</p>
            </div>

            <div class="info-box">
                <h3>📋 What You Can Do:</h3>
                <ul>
                    <li>Report environmental incidents in real-time</li>
                    <li>Upload photos and videos as evidence</li>
                    <li>Track the status of your reports</li>
                    <li>Receive updates on incident resolution</li>
                </ul>
            </div>

            <p><strong>Ready to make a difference?</strong></p>
            <a href="{{ url('/login') }}" class="btn">Login to Your Account</a>

            <div class="info-box">
                <h3>🔒 Account Security</h3>
                <p>Your account security is important to us. Please keep your login credentials safe and do not share them with anyone.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>EcoConnect - DENR CENRO Sanchez Mira</strong></p>
            <p>Environmental Incident Reporting System</p>
            <p>📍 Sanchez Mira, Cagayan, Philippines</p>
            <p>📞 (078) 123-4567 | 📧 support@ecoconnect.gov.ph</p>
            <p>
                <a href="{{ url('/privacy') }}" style="color: #1a472a;">Privacy Policy</a> | 
                <a href="{{ url('/terms') }}" style="color: #1a472a;">Terms of Service</a>
            </p>
            <p style="font-size: 12px; margin-top: 10px;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>