<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follow-up on Incident - EcoConnect</title>
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
        .incident-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .followup-box {
            background: #e8f5e9;
            padding: 20px;
            border-left: 4px solid #1a472a;
            margin: 20px 0;
            border-radius: 4px;
        }
        .followup-box p {
            margin: 0;
            color: #1a472a;
            font-weight: 500;
        }
        .followup-text {
            background: white;
            padding: 15px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .reference-number {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background: #1a472a;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .button:hover {
            background: #145a38;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Follow-up Received</h1>
            <p>New update on incident report</p>
        </div>

        <div class="content">
            <p>Hello {{ $officer->name }},</p>

            <p>A new follow-up has been submitted on the incident you are assigned to:</p>

            <div class="incident-details">
                <p><strong>Reference Number:</strong></p>
                <div class="reference-number">{{ $incident->reference_number }}</div>
                <p><strong>Incident Type:</strong> {{ $incident->incident_type?->value }}</p>
                <p><strong>Status:</strong> {{ $incident->status?->value }}</p>
                <p><strong>Reported by:</strong> {{ $incident->user->name ?? 'Unknown' }}</p>
            </div>

            <div class="followup-box">
                <p>New Follow-up Update:</p>
                <div class="followup-text">
                    {{ $followup->follow_up_text }}
                </div>
                <small style="color: #666;">Submitted on {{ $followup->created_at->format('F j, Y \a\t g:i A') }}</small>
            </div>

            <p>Please review this follow-up update and take any necessary action on the incident report.</p>

            <a href="{{ route('incidents.show', $incident) }}" class="button">
                View Incident Details
            </a>
        </div>

        <div class="footer">
            <p>© 2026 EcoConnect - DENR CENRO Sanchez Mira. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
