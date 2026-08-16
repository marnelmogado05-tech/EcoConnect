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
    @include('partials.track-result', ['incident' => session('incident')])
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
