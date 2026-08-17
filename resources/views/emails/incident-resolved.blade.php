<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report Resolved - EcoConnect</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f7fdf7;
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
            background: #28a745;
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
        .status-badge.resolved { background: #28a745; }
        .resolution-box {
            background: #f0f9f0;
            border-left: 4px solid #28a745;
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
            background: #28a745;
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
            background: #28a745;
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
            background: #28a745;
            border-radius: 50%;
            margin-right: 15px;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .timeline-content {
            flex: 1;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .metric-card {
            background: white;
            border: 1px solid #e9ecef;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }
        .metric-label {
            font-size: 12px;
            color: #666;
        }
        .thank-you {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #28a745, #1a472a);
            color: white;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✅ Incident Report Resolved</h1>
            <p>Reference: {{ $incident->reference_number }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="thank-you">
                <h2 style="margin: 0 0 10px 0;">Thank You for Your Environmental Service!</h2>
                <p style="margin: 0; opacity: 0.9;">Your report has been successfully resolved</p>
            </div>

            <p>Dear {{ $user->fname }},</p>
            <p>We're pleased to inform you that the environmental incident you reported has been successfully resolved. Your vigilance and commitment to environmental protection have made a positive impact in our community.</p>

            <div class="incident-details">
                <h3>📊 Resolution Summary</h3>
                <p><strong>Reference Number:</strong> {{ $incident->reference_number }}</p>
                <p><strong>Incident Type:</strong> {{ $incident->incident_type?->value }}</p>
                <p><strong>Final Status:</strong> 
                    <span class="status-badge resolved">RESOLVED</span>
                </p>
                <p><strong>Date Resolved:</strong> {{ now()->format('F j, Y g:i A') }}</p>
                <p><strong>Resolution Time:</strong> 
                    {{ $incident->created_at->diffInDays(now()) }} days
                </p>
                 <!-- Media Evidence Section -->
                @if($incident->mediaEvidence->count() > 0)
                    <div class="media-section">
                        <h3>📎 Media Evidence 
                            <span class="media-count">{{ $incident->mediaEvidence->count() }} file(s)</span>
                        </h3>
                        
                        @php
                            $images = $incident->mediaEvidence->filter(function($media) {
                                return str_starts_with($media->mime_type, 'image/');
                            });
                            $videos = $incident->mediaEvidence->filter(function($media) {
                                return str_starts_with($media->mime_type, 'video/');
                            });
                        @endphp

                        @if($images->count() > 0)
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #1a472a; margin-bottom: 10px;">
                                📷 Photos ({{ $images->count() }})
                            </h4>
                            <div class="media-grid">
                                @foreach($images as $image)
                                <div class="media-item">
                                    <div class="media-info">
                                        @if($image->latitude && $image->longitude)
                                        <div style=" margin-top: 5px;">
                                            📍 {{ $image->address }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($videos->count() > 0)
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #1a472a; margin-bottom: 10px;">
                                🎥 Videos ({{ $videos->count() }})
                            </h4>
                            <div class="media-grid">
                                @foreach($videos as $video)
                                <div class="media-item">
                                    <div class="media-info">
                                        <div style=" margin-top: 5px;">
                                            @if($video->latitude && $video->longitude)
                                            <div>📍 {{ $video->address }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
            </div>

            @if($incident->resolution_details)
            <div class="resolution-box">
                <h3>🔧 Actions Taken</h3>
                <div class="officer-note">
                    <div class="officer-info">
                        <div class="officer-avatar">DO</div>
                        <div>
                            <strong>Environmental Officer's Report</strong>
                            <div style="font-size: 12px; color: #666;">Resolution Details</div>
                        </div>
                    </div>
                    <p style="margin: 0;">{{ $incident->resolution_details }}</p>
                </div>
            </div>
            @endif

            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-value">{{ $incident->created_at->diffInDays(now()) }}</div>
                    <div class="metric-label">Days to Resolve</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value">
                        @if($incident->priority?->value === 'High') 48
                        @elseif($incident->priority?->value === 'Medium') 72
                        @else 96
                        @endif
                    </div>
                    <div class="metric-label">Target Hours</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value">1</div>
                    <div class="metric-label">Incident Resolved</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value">{{ $user->incidents()->resolved()->count() + 1 }}</div>
                    <div class="metric-label">Your Total Resolved Reports</div>
                </div>
            </div>

            <div class="timeline">
                <h3>📈 Resolution Timeline</h3>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Report Submitted</strong>
                        <p>{{ $incident->created_at->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Initial Assessment</strong>
                        <p>Report reviewed and prioritized</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Action Taken</strong>
                        <p>Appropriate measures implemented</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Resolution Confirmed</strong>
                        <p>Incident successfully resolved on {{ now()->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <h3>🎉 Your Impact Matters</h3>
                <p>By reporting this incident, you've contributed to:</p>
                <ul style="text-align: left; margin: 15px 0;">
                    <li>Cleaner and safer environment for our community</li>
                    <li>Preservation of natural resources in Sanchez Mira</li>
                    <li>Enhanced environmental monitoring and protection</li>
                    <li>Community awareness and engagement</li>
                </ul>
                
                <a href="{{ url('/new-report') }}" class="btn btn-primary">Report Another Incident</a>
                <a href="{{ url('/incidents') }}" class="btn btn-secondary">View Your Incident Reports</a>
            </div>

            <div style="background: #f0f7ff; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
                <h3 style="color: #1a472a; margin-bottom: 15px;">🏆 Environmental Champion</h3>
                <p>You're making a difference! Continue to be our eyes and ears in the community.</p>
                <div style="font-size: 14px; color: #666; margin-top: 10px;">
                    <strong>Share your environmental commitment:</strong><br>
                    #EcoConnectChampion #DENRCENROSanchezMira
                </div>
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