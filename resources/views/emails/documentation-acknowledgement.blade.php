<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            margin-bottom: 30px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        h2 {
            color: #34495e;
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
        strong {
            color: #2c3e50;
        }
        .section {
            margin-bottom: 25px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
        }
        .highlight {
            background-color: #e8f5e9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Documentation Acknowledgement Receipt</h1>
    </div>

    <div class="content">
        <p>Dear {{ $officer->fname }} {{ $officer->lname }},</p>

        <p>Thank you for submitting documentation for the incident assignment. Your submission has been recorded and acknowledged by the system.</p>

        <div class="section">
            <h2>Incident Information</h2>
            <ul>
                <li><strong>Reference Number:</strong> {{ $incident->reference_number }}</li>
                <li><strong>Incident Type:</strong> {{ $incident->incident_type?->value }}</li>
                <li><strong>Priority Level:</strong> {{ $incident->priority?->value }}</li>
                <li><strong>Status:</strong> {{ $incident->status?->value }}</li>
            </ul>
        </div>

        <div class="section">
            <h2>Documentation Summary</h2>
            <ul>
                <li><strong>Documentation Submitted:</strong> {{ $acknowledgement->acknowledged_at->format('F j, Y g:i A') }}</li>
                <li><strong>Officer:</strong> {{ $officer->fname }} {{ $officer->lname }} ({{ $officer->role === 'police' ? 'Police Department' : 'BFP' }})</li>
                <li><strong>Evidence Files Submitted:</strong> {{ $acknowledgement->evidence_count }} file(s)</li>
            </ul>
        </div>

        <div class="section">
            <h2>Documentation Notes</h2>
            <div class="highlight">
                {{ $acknowledgement->documentation }}
            </div>
        </div>

        <hr style="border: none; border-top: 1px dashed #ccc; margin: 30px 0;">

        <div class="section">
            <h3>What's Next?</h3>
            <p>The reporter has been notified of your actions on this incident. Please continue monitoring this case until it is fully resolved.</p>

            <p>If you have any questions or updates regarding this incident, please contact the DENR CENRO Sanchez Mira office.</p>

            <p>Thank you for your service in protecting our environment.</p>
        </div>
    </div>

    <div class="footer">
        <p><strong>Best regards,</strong><br>
        <strong>DENR CENRO Sanchez Mira</strong></p>
    </div>
</body>
</html>
