<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report Review Complete - EcoConnect</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #fef7f7;
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
            background: #dc3545;
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
        .status-badge {
            display: inline-block;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.rejected { background: #dc3545; }
        .review-box {
            background: #fef0f0;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .action-buttons {
            margin: 25px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 0 10px;
        }
        .btn-primary {
            background: #1a472a;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .officer-note {
            background: white;
            border: 1px solid #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
        }
        .officer-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .officer-avatar {
            width: 40px;
            height: 40px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 12px;
        }
        .timeline {
            margin: 20px 0;
        }
        .timeline-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .timeline-marker {
            width: 12px;
            height: 12px;
            background: #dc3545;
            border-radius: 50%;
            margin-right: 15px;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .timeline-content {
            flex: 1;
        }
        .reason-list {
            margin: 15px 0;
            padding-left: 20px;
        }
        .reason-list li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 Incident Report Review Complete</h1>
            <p>Reference: {{ $incident->reference_number }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Report Review Update</h2>
            <p>Dear {{ $user->fname }},</p>
            <p>Your environmental incident report has been reviewed by our team. After careful assessment, we regret to inform you that your report could not be processed at this time.</p>

            <div class="incident-details">
                <h3>📊 Report Summary</h3>
                <p><strong>Reference Number:</strong> {{ $incident->reference_number }}</p>
                <p><strong>Incident Type:</strong> {{ $incident->incident_type }}</p>
                <p><strong>Final Status:</strong> 
                    <span class="status-badge rejected">REJECTED</span>
                </p>
                <p><strong>Date Reviewed:</strong> {{ now()->format('F j, Y g:i A') }}</p>
                <p><strong>Reviewed By:</strong> DENR CENRO Sanchez Mira</p>
            </div>

            <div class="review-box">
                <h3>❌ Reason for Rejection</h3>
                
                @if($incident->rejection_reason)
                <div class="officer-note">
                    <div class="officer-info">
                        <div class="officer-avatar">DO</div>
                        <div>
                            <strong>Reviewing Officer's Note</strong>
                            <div style="font-size: 12px; color: #666;">Environmental Officer</div>
                        </div>
                    </div>
                    <p style="margin: 0; font-style: italic;">"{{ $incident->rejection_reason }}"</p>
                </div>
                @else
                <p>Your report was rejected due to one or more of the following common reasons:</p>
                <ul class="reason-list">
                    <li>Insufficient evidence or information provided</li>
                    <li>Duplicate of an existing report</li>
                    <li>Outside of DENR CENRO Sanchez Mira jurisdiction</li>
                    <li>Does not meet environmental incident criteria</li>
                    <li>Incomplete contact information</li>
                </ul>
                @endif
            </div>

            <div class="timeline">
                <h3>📈 Review Process</h3>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Report Submitted</strong>
                        <p>Your report was received on {{ $incident->created_at->format('F j, Y') }}</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Initial Review</strong>
                        <p>Report was assessed for completeness and validity</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Final Decision</strong>
                        <p>Report was rejected based on the criteria above</p>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <h3>🔄 Next Steps</h3>
                <p>If you believe this decision was made in error, or if you have additional information to provide, you may:</p>
                
                <a href="{{ url('/new-report') }}" class="btn btn-primary">Submit New Report</a>
                <a href="{{ url('/contact-support') }}" class="btn btn-secondary">Contact Support</a>
                
                <div style="margin-top: 20px; font-size: 14px; color: #666;">
                    <p><strong>Need to improve your report?</strong><br>
                    Ensure you provide clear photos, precise location, and detailed description.</p>
                </div>
            </div>

            <div style="background: #f0f9f0; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <h3>💡 Tips for Better Reports</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Take clear, well-lit photos of the incident</li>
                    <li>Include location coordinates if possible</li>
                    <li>Provide specific date and time of observation</li>
                    <li>Describe the environmental impact clearly</li>
                    <li>Include contact information for follow-up</li>
                </ul>
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