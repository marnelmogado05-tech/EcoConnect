<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Incident Assigned - EcoConnect</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f0f7ff;
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
            background: #1e40af;
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
        .status-badge.assigned { background: #1e40af; }
        .status-badge.high { background: #dc2626; }
        .status-badge.medium { background: #ea580c; }
        .status-badge.low { background: #16a34a; }
        .action-box {
            background: #eff6ff;
            border-left: 4px solid #1e40af;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .btn-primary {
            background: #1e40af;
            color: white;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .officer-info {
            display: flex;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin: 15px 0;
        }
        .officer-avatar {
            width: 50px;
            height: 50px;
            background: #1e40af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            margin-right: 15px;
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
            background: #1e40af;
            border-radius: 50%;
            margin-right: 15px;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .timeline-content {
            flex: 1;
        }
        .urgency-indicator {
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .media-indicator {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 10px 0;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📋 New Incident Assigned</h1>
            <p>Reference: {{ $incident->reference_number }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Attention: Officer {{ $assignedOfficer->fname }} {{ $assignedOfficer->lname }}</h2>
            <p>You have been assigned a new environmental incident that requires your immediate attention.</p>

            <div class="officer-info">
                <div class="officer-avatar">
                    {{ strtoupper(substr($assignedOfficer->fname, 0, 1)) }}{{ strtoupper(substr($assignedOfficer->lname, 0, 1)) }}
                </div>
                <div>
                    <strong>Assigned To:</strong> {{ $assignedOfficer->fname }} {{ $assignedOfficer->lname }}<br>
                    <strong>Badge/ID:</strong> {{ $assignedOfficer->badge_number ?? 'N/A' }}<br>
                    <strong>Role:</strong> Environmental Officer
                </div>
            </div>

            <div class="incident-details">
                <h3>🚨 Incident Details</h3>
                <p><strong>Reference Number:</strong> {{ $incident->reference_number }}</p>
                <p><strong>Incident Type:</strong> {{ $incident->incident_type }}</p>
                <p><strong>Priority Level:</strong> 
                    <span class="status-badge {{ strtolower($incident->priority) }}">{{ $incident->priority }}</span>
                </p>
                <p><strong>Current Status:</strong> 
                    <span class="status-badge assigned">Assigned</span>
                </p>
                <p><strong>Date & Time Reported:</strong> {{ $incident->created_at->format('F j, Y g:i A') }}</p>
                <p><strong>Incident Date:</strong> {{ $incident->incident_date->format('F j, Y') }}</p>
                <p><strong>Incident Time:</strong> {{ $incident->incident_time }}</p>
                
                @if($incident->location)
                <p><strong>Location:</strong> {{ $incident->location }}</p>
                @endif

                @if($incident->latitude && $incident->longitude)
                <p><strong>Coordinates:</strong> {{ $incident->latitude }}, {{ $incident->longitude }}</p>
                @endif
                
                <p><strong>Description:</strong><br>{{ $incident->description }}</p>

                @if($incident->mediaEvidence->count() > 0)
                <div class="media-indicator">
                    📎 <strong>Media Evidence Available:</strong> 
                    {{ $incident->mediaEvidence->count() }} file(s) attached
                </div>
                @endif
            </div>

            @if($incident->priority == 'High')
            <div class="urgency-indicator">
                <h3>🚨 URGENT ACTION REQUIRED</h3>
                <p>This incident has been marked as <strong>HIGH PRIORITY</strong> and requires immediate attention due to potential environmental impact.</p>
            </div>
            @endif

            <div class="action-box">
                <h3>🎯 Required Actions</h3>
                <p>As the assigned officer, please take the following steps:</p>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>Review Incident Details</strong>
                            <p>Examine all provided information and media evidence</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>Initial Assessment</strong>
                            <p>Conduct preliminary assessment and determine next steps</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>On-site Investigation</strong>
                            <p>Visit the location if necessary for further investigation</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <strong>Update Status</strong>
                            <p>Keep the incident status updated in the system</p>
                        </div>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin: 25px 0;">
                <a href="{{ url('/incidents/' . $incident->id) }}" class="btn btn-primary">View Incident Details</a>
                <a href="{{ url('/login') }}" class="btn btn-secondary">Login</a>
            </div>

            <div style="background: #f0f9f0; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <h3>📞 Support & Resources</h3>
                <p><strong>Supervisor:</strong> Department Supervisor</p>
                <p><strong>Emergency Backup:</strong> (078) 123-4567</p>
                <p><strong>Equipment Checkout:</strong> Available at DENR CENRO Office</p>
            </div>

            @if($assigner)
            <div style="background: #fefce8; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <p><strong>Assigned by:</strong> DENR CENRO Sanchez Mira</p>
                <p><strong>Assignment Time:</strong> {{ now()->format('F j, Y g:i A') }}</p>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>EcoConnect - DENR CENRO Sanchez Mira</strong></p>
            <p>Environmental Incident Reporting System</p>
            <p>📍 Sanchez Mira, Cagayan, Philippines</p>
            <p>📞 (078) 123-4567 | 📧 support@ecoconnect.gov.ph</p>
            <p>
                <a href="{{ url('/privacy') }}" style="color: #1e40af;">Privacy Policy</a> | 
                <a href="{{ url('/terms') }}" style="color: #1e40af;">Terms of Service</a>
            </p>
            <p style="font-size: 12px; margin-top: 10px;">
                This is an automated assignment notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>