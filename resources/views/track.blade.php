@extends('layouts.guest')
@section('title', 'Track Incident Report | EcoConnect - DENR CENRO Sanchez Mira')
@section('content')
<section id="track-incident" class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Header -->
                <div class="mb-5 text-center">
                    <h1 class="display-5 fw-bold text-denr-green">Track Incident Report</h1>
                    <p class="lead text-muted">Enter your reference number to check the status of your reported incident</p>
                </div>

                <!-- Search Form -->
                <div class="shadow-sm card">
                    <div class="p-4 card-body">
                        <form id="track-form" method="POST" action="{{ route('track.search') }}">
                            @csrf

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="reference_number" class="form-label fw-semibold">
                                    <i class="fas fa-hashtag me-2 text-denr-green"></i>Reference Number
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg @error('reference_number') is-invalid @enderror"
                                       id="reference_number"
                                       name="reference_number"
                                       value="{{ old('reference_number') }}"
                                       placeholder="Enter your reference number (e.g., INC-2024-001)"
                                       required
                                       autofocus>
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    You can find your reference number in the confirmation email or in your reports dashboard.
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-denr-green btn-lg">
                                    <i class="fas fa-search me-2"></i>Track Incident
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-4">
                    <div class="border-0 card bg-light">
                        <div class="card-body">
                            <h6 class="card-title text-denr-green">
                                <i class="fas fa-question-circle me-2"></i>Need Help?
                            </h6>
                            <ul class="mb-0 list-unstyled">
                                <li class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-2"></i>
                                        Contact support: support@ecoconnect.gov.ph
                                    </small>
                                </li>
                                <li class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-2"></i>
                                        Call us: (078) 123-4567
                                    </small>
                                </li>
                                <li>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-2"></i>
                                        Support hours: Mon-Fri, 8:00 AM - 5:00 PM
                                    </small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Loading Overlay -->
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-content">
        <div class="spinner-border text-denr-green" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-white">Searching for incident...</p>
    </div>
</div>

@if(session('incident'))
    @php
        $incident = session('incident');
        $statusBadgeClasses = [
            'Pending' => 'bg-secondary',
            'In Progress' => 'bg-primary',
            'Resolved' => 'bg-success',
            'Rejected' => 'bg-danger'
        ];
        $priorityBadgeClasses = [
            'Normal' => 'bg-success',
            'High' => 'bg-warning',
            'Urgent' => 'bg-danger'
        ];

        // Separate images and videos
        $images = $incident->mediaEvidence->filter(function($media) {
            return str_starts_with($media->mime_type, 'image/');
        });
        $videos = $incident->mediaEvidence->filter(function($media) {
            return str_starts_with($media->mime_type, 'video/');
        });
    @endphp

    <!-- Incident Modal -->
    <div class="modal fade" id="incidentModal" tabindex="-1" aria-labelledby="incidentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-denr-green" id="incidentModalLabel">
                        <i class="fas fa-clipboard-list me-2"></i>Incident Status - {{ $incident->reference_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Success Alert -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Status Header -->
                    <div class="mb-4 row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-1 text-denr-green">Reference Number</h6>
                            <h4 class="fw-bold text-primary">{{ $incident->reference_number }}</h4>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge {{ $statusBadgeClasses[$incident->status] }} fs-6 px-3 py-2">
                                {{ $incident->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Incident Details -->
                    <div class="mb-4 row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="45%" class="text-muted">Incident Type:</th>
                                    <td class="fw-semibold">{{ $incident->incident_type }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Priority:</th>
                                    <td>
                                        <span class="badge {{ $priorityBadgeClasses[$incident->priority] }}">
                                            {{ $incident->priority }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Date Reported:</th>
                                    <td>{{ $incident->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="45%" class="text-muted">Incident Date:</th>
                                    <td>{{ $incident->incident_date->format('F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Incident Time:</th>
                                    <td>{{ $incident->incident_time }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Last Updated:</th>
                                    <td>{{ $incident->updated_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="mb-2 text-denr-green fw-semibold">Description</h6>
                        <div class="p-3 rounded bg-light">
                            <p class="mb-0">{{ $incident->description }}</p>
                        </div>
                    </div>

                    @if($incident->location)
                    <div class="mb-4">
                        <h6 class="mb-2 text-denr-green fw-semibold">Location</h6>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            {{ $incident->location }}
                        </p>
                    </div>
                    @endif

                    <!-- Media Evidence -->
                    @if($incident->mediaEvidence->count() > 0)
                    <div class="mb-4">
                        <h6 class="mb-3 text-denr-green fw-semibold">
                            Media Evidence
                            <span class="badge bg-primary ms-2">{{ $incident->mediaEvidence->count() }}</span>
                        </h6>

                        @if($images->count() > 0)
                        <div class="mb-4">
                            <h6 class="mb-3 text-muted">
                                <i class="fas fa-images me-2"></i>Photos ({{ $images->count() }})
                            </h6>
                            <div class="row g-3">
                                @foreach($images as $image)
                                <div class="col-md-4 col-sm-6">
                                    <div class="border-0 shadow-sm card">
                                        <img src="{{ asset('storage/' . $image->file_path) }}"
                                             class="cursor-pointer card-img-top media-thumbnail"
                                             alt="Evidence photo"
                                             style="height: 200px; object-fit: cover;"
                                             onclick="previewImage('{{ asset('storage/' . $image->file_path) }}', '{{ $image->file_name }}')">
                                        <div class="p-2 card-body">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-file-image me-1"></i>
                                                {{ \Illuminate\Support\Str::limit($image->file_name, 20) }}
                                            </small>
                                            @if($image->latitude && $image->longitude)
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i> Location tagged
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($videos->count() > 0)
                        <div class="mb-4">
                            <h6 class="mb-3 text-muted">
                                <i class="fas fa-video me-2"></i>Videos ({{ $videos->count() }})
                            </h6>
                            <div class="row g-3">
                                @foreach($videos as $video)
                                <div class="col-md-6">
                                    <div class="border-0 shadow-sm card">
                                        <div class="video-container">
                                            <video controls class="w-100" style="max-height: 250px; background: #000;">
                                                <source src="{{ asset('storage/' . $video->file_path) }}" type="{{ $video->mime_type }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>
                                        <div class="p-2 card-body">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-file-video me-1"></i>
                                                {{ \Illuminate\Support\Str::limit($video->file_name, 25) }}
                                            </small>
                                            @if($video->latitude && $video->longitude)
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i> Location tagged
                                            </small>
                                            @endif
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . $video->file_path) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   download="{{ $video->file_name }}">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Timeline -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-denr-green fw-semibold">Status Timeline</h6>
                        <div class="timeline">
                            <div class="timeline-item {{ $incident->status === 'Resolved' || $incident->status === 'Rejected' ? 'completed' : 'active' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Report Submitted</h6>
                                    <p class="mb-1 text-muted">{{ $incident->created_at->format('M j, Y g:i A') }}</p>
                                    <small class="text-muted">Your incident report has been received and is under review.</small>
                                </div>
                            </div>

                            @if($incident->status === 'In Progress' || $incident->status === 'Resolved')
                            <div class="timeline-item {{ $incident->status === 'Resolved' ? 'completed' : 'active' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Under Investigation</h6>
                                    <p class="mb-1 text-muted">
                                        @if($incident->assigned_at)
                                            Assigned on {{ $incident->assigned_at->format('M j, Y') }}
                                        @else
                                            Investigation in progress
                                        @endif
                                    </p>
                                    <small class="text-muted">Our team is currently investigating your report.</small>
                                </div>
                            </div>
                            @endif

                            @if($incident->status === 'Resolved')
                            <div class="timeline-item completed">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Resolved</h6>
                                    <p class="mb-1 text-muted">
                                        @if($incident->resolved_at)
                                            {{ $incident->resolved_at->format('M j, Y') }}
                                        @else
                                            Case closed
                                        @endif
                                    </p>
                                    @if($incident->resolution_details)
                                        <small class="text-muted">{{ $incident->resolution_details }}</small>
                                    @endif
                                </div>
                            </div>
                            @elseif($incident->status === 'Rejected')
                            <div class="timeline-item completed">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Rejected</h6>
                                    <p class="mb-1 text-muted">
                                        @if($incident->rejected_at)
                                            {{ $incident->rejected_at->format('M j, Y') }}
                                        @else
                                            Report rejected
                                        @endif
                                    </p>
                                    @if($incident->rejection_reason)
                                        <small class="text-danger">{{ $incident->rejection_reason }}</small>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Additional Information -->
                    @if($incident->assignedTo || $incident->resolution_details || $incident->rejection_reason)
                    <div class="pt-3 border-top">
                        <h6 class="mb-3 text-denr-green fw-semibold">Additional Information</h6>

                        @if($incident->assignedTo)
                        <div class="mb-3">
                            <h6 class="mb-1 text-muted small">Assigned Officer</h6>
                            <p class="mb-0 fw-semibold">{{ $incident->assignedTo->name }}</p>
                            <small class="text-muted">{{ $incident->assignedTo->email }}</small>
                        </div>
                        @endif

                        @if($incident->resolution_details)
                        <div class="mb-3">
                            <h6 class="mb-1 text-muted small">Resolution Details</h6>
                            <div class="p-3 border border-opacity-25 rounded bg-success bg-opacity-10 border-success">
                                <i class="fas fa-check-circle me-2 text-success"></i>
                                <span class="text-success">{{ $incident->resolution_details }}</span>
                            </div>
                        </div>
                        @endif

                        @if($incident->rejection_reason)
                        <div class="mb-3">
                            <h6 class="mb-1 text-muted small">Rejection Reason</h6>
                            <div class="p-3 border border-opacity-25 rounded bg-danger bg-opacity-10 border-danger">
                                <i class="fas fa-times-circle me-2 text-danger"></i>
                                <span class="text-danger">{{ $incident->rejection_reason }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Follow-ups Section in the modal -->
                    <div class="mt-4">
                        <h6 class="mb-3 text-denr-green fw-semibold">Follow-ups</h6>

                        @if($incident->followups->count() > 0)
                            @foreach($incident->followups as $followup)
                                <div class="mb-3 card border-left-denr">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">{{ $followup->user->name ?? 'Anonymous' }}</h6>
                                            <small class="text-muted">{{ $followup->created_at->diffForHumans() }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ $followup->follow_up_type }}</span>
                                        <p class="mt-2 mb-0">{{ $followup->follow_up_text }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No follow-ups yet.</p>
                        @endif
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="{{ route('track.index') }}" class="btn btn-denr-green">
                        <i class="fas fa-search me-2"></i>Track Another
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="border-0 modal-header">
                    <h6 class="text-white modal-title" id="imagePreviewTitle"></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center">
                    <img id="previewImage" src="" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                </div>
                <div class="border-0 modal-footer justify-content-center">
                    <a id="downloadImage" href="#" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>



    <script>


        // Show loading overlay on form submit
        document.getElementById('track-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent immediate form submission
            document.getElementById('loading-overlay').style.display = 'flex';
            // Submit the form after a short delay to ensure overlay is visible
            setTimeout(() => {
                this.submit();
            }, 100);
        });

        // Auto-show the incident modal when page loads with incident data
        document.addEventListener('DOMContentLoaded', function() {
            const incidentModal = new bootstrap.Modal(document.getElementById('incidentModal'));
            incidentModal.show();
        });

        function previewImage(imageUrl, fileName) {
            const previewImage = document.getElementById('previewImage');
            const downloadLink = document.getElementById('downloadImage');
            const title = document.getElementById('imagePreviewTitle');

            // Set image source
            previewImage.src = imageUrl;

            // Set download link
            downloadLink.href = imageUrl;
            downloadLink.download = fileName || 'image.jpg';

            // Set title
            title.textContent = fileName || 'Image Preview';

            // Show modal
            const imagePreviewModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            imagePreviewModal.show();
        }

        function downloadFile(fileUrl, fileName) {
            // Create a temporary anchor element to trigger download
            const link = document.createElement('a');
            link.href = fileUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endif

    <style>
        .btn-denr-green {
            background: var(--denr-green);
            color: white;
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-denr-green:hover {
            background: var(--denr-light-green);
            transform: translateY(-1px);
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .form-control-lg {
            padding: 12px 16px;
            font-size: 1.1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control-lg:focus {
            border-color: var(--denr-green);
            box-shadow: 0 0 0 0.2rem rgba(26, 71, 42, 0.1);
        }

        .modal-xl {
            max-width: 900px;
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #6c757d;
            border: 3px solid white;
        }

        .timeline-item.active .timeline-marker {
            background-color: var(--denr-green);
            box-shadow: 0 0 0 3px rgba(26, 71, 42, 0.2);
        }

        .timeline-item.completed .timeline-marker {
            background-color: #198754;
        }

        .timeline-content h6 {
            margin-bottom: 5px;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .timeline-content p {
            font-size: 0.85rem;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: -23px;
            top: 14px;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }

        /* Media Evidence Styles */
        .cursor-pointer {
            cursor: pointer;
        }

        .media-thumbnail {
            transition: transform 0.3s ease;
        }

        .media-thumbnail:hover {
            transform: scale(1.05);
        }

        .video-container video {
            border-radius: 8px 8px 0 0;
        }

        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-content {
            text-align: center;
        }

        .loading-content .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

@endsection
