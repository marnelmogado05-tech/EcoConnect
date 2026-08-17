<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incident Report Submitted - EcoConnect</title>
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
        .status-badge {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-badge.pending { background: #6c757d; }
        .status-badge.in-progress { background: #007bff; }
        .status-badge.resolved { background: #28a745; }
        .status-badge.rejected { background: #dc3545; }
        .track-btn {
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
        .media-section {
            margin: 25px 0;
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
        }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 15px 0;
        }
        .media-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        .media-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        .media-video {
            width: 100%;
            height: 120px;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .media-info {
            padding: 10px;
            font-size: 12px;
        }
        .media-count {
            background: #1a472a;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
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
            background: #1a472a;
            border-radius: 50%;
            margin-right: 15px;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .timeline-content {
            flex: 1;
        }
        @media (max-width: 480px) {
            .media-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚨 Incident Report Submitted</h1>
            <p>Reference: {{ $incident->reference_number }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Thank You for Your Report, {{ $user->fname }}!</h2>
            <p>Your environmental incident report has been successfully submitted and is now under review.</p>

            <div class="incident-details">
                <h3>📋 Incident Details</h3>
                <p><strong>Reference Number:</strong> {{ $incident->reference_number }}</p>
                <p><strong>Incident Type:</strong> {{ $incident->incident_type?->value }}</p>
                <p><strong>Priority:</strong> 
                    <span class="status-badge">{{ $incident->priority?->value }}</span>
                </p>
                <p><strong>Current Status:</strong> 
                    <span class="status-badge {{ strtolower(str_replace(' ', '-', $incident->status?->value ?? '')) }}">
                        {{ $incident->status?->value }}
                    </span>
                </p>
                <p><strong>Date & Time Reported:</strong> {{ $incident->created_at->format('F j, Y g:i A') }}</p>
                <p><strong>Incident Date:</strong> {{ $incident->incident_date->format('F j, Y') }}</p>
                <p><strong>Incident Time:</strong> {{ $incident->incident_time }}</p>
                
                @if($incident->location)
                <p><strong>Location:</strong> {{ $incident->location }}</p>
                @endif
                
                <p><strong>Description:</strong><br>{{ $incident->description }}</p>
            </div>

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
                            {{-- embed() takes a filesystem path, not a URL; the evidence
                                 disk is private, so there is no URL to give it anyway. --}}
                            <img src="{{ $message->embed($image->storage_path) }}"
                                 alt="Evidence photo" 
                                 class="media-image">
                            <div class="media-info">
                                <strong>{{ \Illuminate\Support\Str::limit($image->file_name, 20) }}</strong>
                                @if($image->latitude && $image->longitude)
                                <div style="color: #666; margin-top: 5px;">
                                    📍 {{ $image->display_address }}
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
                            <div class="media-video">
                                <div style="text-align: center;">
                                    <div style="font-size: 24px; margin-bottom: 5px;">🎬</div>
                                    <div style="font-size: 11px;">Video File</div>
                                    <div style="font-size: 10px; opacity: 0.8;">
                                        {{ \Illuminate\Support\Str::limit($video->file_name, 15) }}
                                    </div>
                                </div>
                            </div>
                            <div class="media-info">
                                <strong>{{ \Illuminate\Support\Str::limit($video->file_name, 20) }}</strong>
                                <div style="color: #666; margin-top: 5px;">
                                    <div>📁 {{ round($video->file_size / 1024 / 1024, 1) }} MB</div>
                                    @if($video->latitude && $video->longitude)
                                    <div>📍 {{ $video->display_address }}</div>
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

            <div class="timeline">
                <h3>📈 What Happens Next?</h3>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Review & Assessment</strong>
                        <p>Our team will review your report and assess the situation within 24-48 hours.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Investigation</strong>
                        <p>If needed, our environmental officers will conduct an on-site investigation.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <strong>Resolution</strong>
                        <p>We'll work towards resolving the incident and keep you updated on the progress.</p>
                    </div>
                </div>
            </div>

            <div class="info-box">
                <h3>🔍 Track Your Report</h3>
                <p>You can track the status of your report anytime using your reference number:</p>
                <p><strong>Reference Number:</strong> <code style="background: #1a472a; color: white; padding: 4px 8px; border-radius: 4px;">{{ $incident->reference_number }}</code></p>
                <a href="{{ url('/track-incident') }}" class="track-btn">Track Your Report Online</a>
            </div>

            <div class="info-box">
                <h3>📞 Need Immediate Assistance?</h3>
                <p>If this is an emergency requiring immediate attention, please contact:</p>
                <p><strong>DENR CENRO Sanchez Mira Hotline:</strong> (078) 123-4567</p>
                <p><strong>Emergency Services:</strong> 911</p>
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