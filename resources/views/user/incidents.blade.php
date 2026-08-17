@extends('layouts.mobile-user')
@section('page-title', 'My Incident Reports')
@section('title', 'My Reports | EcoConnect - DENR CENRO Sanchez Mira')
@section('content')
    <section id="my-reports" class="py-5">
        <div class="container">
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">My Incident Reports</h3>
                        <a href="{{ route('report-incident') }}" class="text-sm btn btn-success">
                            <i class="fas fa-plus"></i> Report New Incident
                        </a>
                    </div>
                    <p class="mb-0 text-muted">Track all your submitted environmental incident reports</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            {{-- <div class="mb-4 row">
                <div class="mb-3 col-md-3">
                    <div class="text-white card bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0">Total Reports</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3">
                    <div class="text-white card bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3">
                    <div class="text-white card bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['in_progress'] }}</h4>
                                    <p class="mb-0">In Progress</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tasks fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 col-md-3">
                    <div class="text-white card bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['resolved'] }}</h4>
                                    <p class="mb-0">Resolved</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Filters Form -->
            <div class="mb-4 border-0 shadow-sm card" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="p-3 card-body p-md-4">
                    <form method="GET" action="{{ route('incidents') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-primary">
                                    <i class="fas fa-filter me-1"></i>Filter by Status
                                </label>
                                <select class="form-select form-select-lg border-primary" name="status" style="border-radius: 10px;">
                                    <option value="">All Statuses</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-primary">
                                    <i class="fas fa-tag me-1"></i>Filter by Type
                                </label>
                                <select class="form-select form-select-lg border-primary" name="type" style="border-radius: 10px;">
                                    <option value="">All Types</option>
                                    <option value="Illegal Logging" {{ request('type') == 'Illegal Logging' ? 'selected' : '' }}>Illegal Logging</option>
                                    <option value="Pollution" {{ request('type') == 'Pollution' ? 'selected' : '' }}>Pollution</option>
                                    <option value="Wildlife Crime" {{ request('type') == 'Wildlife Crime' ? 'selected' : '' }}>Wildlife Crime</option>
                                    <option value="Illegal Waste Disposal" {{ request('type') == 'Illegal Waste Disposal' ? 'selected' : '' }}>Illegal Waste Disposal</option>
                                    <option value="Other" {{ request('type') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-primary">
                                    <i class="fas fa-search me-1"></i>Search
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg border-primary" name="search" value="{{ request('search') }}" placeholder="Search reports..." id="searchInput" style="border-radius: 10px 0 0 10px;">
                                    <button class="btn btn-primary btn-lg" type="submit" id="searchButton" style="border-radius: 0 10px 10px 0;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request()->hasAny(['status', 'type', 'search']))
                                    <a href="{{ route('incidents') }}" class="btn btn-outline-secondary btn-lg ms-1" style="border-radius: 10px;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reports List -->
            <div class="card">
                <div class="card-body">
                    @if($incidents->count() > 0)
                        <div class="mb-3">
                            <p class="mb-0 text-muted">
                                Showing {{ $incidents->firstItem() }} to {{ $incidents->lastItem() }} of {{ $incidents->total() }} reports
                            </p>
                        </div>

                        @foreach($incidents as $incident)
                            <div class="card incident-card mb-3 {{ strtolower(str_replace(' ', '-', $incident->status?->value ?? '')) }} shadow-sm" style="border-radius: 15px; overflow: hidden;">
                                <div class="p-3 card-body p-md-4">
                                    <div class="row align-items-start">
                                        <div class="mb-3 col-12 col-md-8 mb-md-0">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-2 card-title fw-bold text-primary" style="font-size: 1.1rem;">{{ $incident->title }}</h5>
                                                    <p class="mb-2 text-muted small">
                                                        <i class="fas fa-hashtag me-1"></i> {{ $incident->reference_number }}
                                                        <span class="mx-2 d-none d-sm-inline">•</span>
                                                        <br class="d-sm-none">
                                                        <i class="fas fa-calendar me-1"></i> {{ $incident->incident_date->format('F j, Y') }}
                                                    </p>
                                                    <p class="mb-3 card-text">{{ \Illuminate\Support\Str::limit($incident->description, 120) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex-wrap gap-2 mb-3 d-flex">
                                                <span class="badge {{ $incident->status?->badgeClass() }} status-badge fw-semibold px-3 py-2" style="font-size: 0.75rem; border-radius: 20px;">
                                                    {{ $incident->status?->value }}
                                                </span>
                                                <span class="px-3 py-2 badge bg-secondary status-badge fw-semibold" style="font-size: 0.75rem; border-radius: 20px;">
                                                    {{ $incident->incident_type?->label() }}
                                                </span>
                                                <span class="badge {{ $incident->priority?->badgeClass() }} status-badge fw-semibold px-3 py-2" style="font-size: 0.75rem; border-radius: 20px;">
                                                    <i class="fas fa-flag me-1"></i> {{ $incident->priority?->value }}
                                                </span>
                                                @if($incident->mediaEvidence->count() > 0)
                                                    <span class="px-3 py-2 badge bg-info status-badge fw-semibold" style="font-size: 0.75rem; border-radius: 20px;">
                                                        <i class="fas fa-camera me-1"></i> {{ $incident->mediaEvidence->count() }} media
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="d-md-none">
                                                <small class="text-muted">
                                                    Reported {{ $incident->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-4 text-md-end">
                                            <div class="gap-2 d-flex flex-column flex-md-row justify-content-md-end">
                                                <button class="btn btn-outline-primary btn-lg d-block d-md-inline-block" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#incidentModal{{ $incident->id }}"
                                                        style="border-radius: 25px; font-weight: 600;">
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </button>
                                                @if($incident->mediaEvidence->count() > 0)
                                                    <button class="btn btn-outline-info btn-lg d-block d-md-inline-block" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#mediaModal{{ $incident->id }}"
                                                            style="border-radius: 25px; font-weight: 600;">
                                                        <i class="fas fa-images me-1"></i> View Media
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="mt-3 d-none d-md-block">
                                                <small class="text-muted">
                                                    Reported {{ $incident->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Incident Details Modal for each incident -->
                            <div class="modal fade" id="incidentModal{{ $incident->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content" style="border-radius: 15px;">
                                        <div class="text-white modal-header bg-primary" style="border-radius: 15px 15px 0 0;">
                                            <h5 class="modal-title fw-bold">
                                                <i class="fas fa-info-circle me-2"></i>Incident Details
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="p-4 modal-body">
                                            <div class="p-3 mb-4 rounded bg-light" style="border-left: 4px solid #007bff;">
                                                <h6 class="mb-2 text-primary">
                                                    <i class="fas fa-hashtag me-1"></i>Reference Number
                                                </h6>
                                                <p class="mb-0 fw-bold fs-5 text-primary">{{ $incident->reference_number }}</p>
                                            </div>

                                            <div class="mb-4 row g-3">
                                                <div class="col-12 col-sm-6">
                                                    <div class="p-3 border rounded">
                                                        <h6 class="mb-2 text-muted">
                                                            <i class="fas fa-tasks me-1"></i>Status
                                                        </h6>
                                                        <span class="badge {{ $incident->status?->badgeClass() }} fs-6 px-3 py-2">
                                                            {{ $incident->status?->value }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <div class="p-3 border rounded">
                                                        <h6 class="mb-2 text-muted">
                                                            <i class="fas fa-tag me-1"></i>Incident Type
                                                        </h6>
                                                        <p class="mb-0 fw-semibold">{{ $incident->incident_type?->label() }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4 row g-3">
                                                <div class="col-12 col-sm-6">
                                                    <div class="p-3 border rounded">
                                                        <h6 class="mb-2 text-muted">
                                                            <i class="fas fa-flag me-1"></i>Priority
                                                        </h6>
                                                        <span class="badge {{ $incident->priority?->badgeClass() }} fs-6 px-3 py-2">
                                                            {{ $incident->priority?->value }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <div class="p-3 border rounded">
                                                        <h6 class="mb-2 text-muted">
                                                            <i class="fas fa-calendar me-1"></i>Incident Date
                                                        </h6>
                                                        <p class="mb-0 fw-semibold">{{ $incident->incident_date->format('F j, Y') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <h6 class="mb-3 text-muted">
                                                    <i class="fas fa-clock me-1"></i>Incident Time
                                                </h6>
                                                <p class="p-3 rounded bg-light fw-semibold">{{ $incident->incident_time }}</p>
                                            </div>

                                            <div class="mb-4">
                                                <h6 class="mb-3 text-muted">
                                                    <i class="fas fa-align-left me-1"></i>Description
                                                </h6>
                                                <div class="p-3 rounded bg-light" style="border-left: 4px solid #6c757d;">
                                                    {{ $incident->description }}
                                                </div>
                                            </div>

                                            <div class="mb-4 row g-3">
                                                <div class="col-12 col-sm-6">
                                                    <div class="p-3 border rounded">
                                                        <h6 class="mb-2 text-muted">
                                                            <i class="fas fa-calendar-plus me-1"></i>Reported On
                                                        </h6>
                                                        <p class="mb-0 small">{{ $incident->created_at->format('F j, Y g:i A') }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <div class="p-3 border rounded">
                                                        <h6 class="mb-2 text-muted">
                                                            <i class="fas fa-edit me-1"></i>Last Updated
                                                        </h6>
                                                        <p class="mb-0 small">{{ $incident->updated_at->format('F j, Y g:i A') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($incident->assignedTo)
                                                <div class="mb-4">
                                                    <h6 class="mb-3 text-muted">
                                                        <i class="fas fa-user me-1"></i>Assigned To
                                                    </h6>
                                                    <div class="p-3 border rounded bg-success bg-opacity-10 border-success">
                                                        <p class="mb-0 fw-semibold text-success">{{ $incident->assignedTo->name }}</p>
                                                        <small class="text-muted">{{ $incident->assignedTo->email }}</small>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($incident->resolution_details)
                                                <div class="mb-4">
                                                    <h6 class="mb-3 text-success">
                                                        <i class="fas fa-check-circle me-1"></i>Resolution Details
                                                    </h6>
                                                    <div class="p-3 border rounded bg-success bg-opacity-10 border-success">
                                                        {{ $incident->resolution_details }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($incident->rejection_reason)
                                                <div class="mb-4">
                                                    <h6 class="mb-3 text-danger">
                                                        <i class="fas fa-times-circle me-1"></i>Rejection Reason
                                                    </h6>
                                                    <div class="p-3 border rounded bg-danger bg-opacity-10 border-danger">
                                                        {{ $incident->rejection_reason }}
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Follow-ups Section -->
                                            <div class="mt-4 pt-4 border-top">
                                                <h6 class="mb-3 text-muted">
                                                    <i class="fas fa-comments me-2"></i>Follow-ups
                                                </h6>

                                                @if($incident->followups && $incident->followups->count() > 0)
                                                    <div class="mb-4">
                                                        @foreach($incident->followups as $followup)
                                                            <div class="mb-3 card border-left-denr">
                                                                <div class="card-body">
                                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                                        <div>
                                                                            <h6 class="mb-1">{{ $followup->user->name ?? 'You' }}</h6>
                                                                            <small class="text-muted">{{ $followup->created_at->diffForHumans() }}</small>
                                                                        </div>
                                                                        <span class="badge bg-info">{{ $followup->follow_up_type }}</span>
                                                                    </div>
                                                                    <p class="mt-2 mb-0">{{ $followup->follow_up_text }}</p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-muted small">No follow-ups yet.</p>
                                                @endif

                                                <!-- Add Follow-up Form -->
                                                <div class="mt-4 card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="mb-3 text-primary fw-semibold">
                                                            <i class="fas fa-plus-circle me-1"></i>Add a Follow-up
                                                        </h6>

                                                        @if($errors->has('follow_up_text'))
                                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                                {{ $errors->first('follow_up_text') }}
                                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                            </div>
                                                        @endif

                                                        <form method="POST" action="{{ route('followups.store', $incident) }}">
                                                            @csrf
                                                            <textarea name="follow_up_text"
                                                                    class="form-control mb-2 @error('follow_up_text') is-invalid @enderror"
                                                                    rows="3"
                                                                    placeholder="Add an update..."
                                                                    required></textarea>
                                                            @error('follow_up_text')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-paper-plane me-1"></i>Post Follow-up
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                                            <button type="button" class="px-4 btn btn-secondary btn-lg" data-bs-dismiss="modal" style="border-radius: 25px;">
                                                <i class="fas fa-times me-1"></i>Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Media Modal for each incident -->
                            @if($incident->mediaEvidence->count() > 0)
                                <div class="modal fade" id="mediaModal{{ $incident->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content" style="border-radius: 15px;">
                                            <div class="text-white modal-header bg-info" style="border-radius: 15px 15px 0 0;">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="fas fa-images me-2"></i>Media Evidence
                                                    <span class="badge bg-light text-dark ms-2">{{ $incident->mediaEvidence->count() }}</span>
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="p-4 modal-body">
                                                <div class="p-3 mb-4 rounded bg-light" style="border-left: 4px solid #0dcaf0;">
                                                    <h6 class="mb-2 text-info">
                                                        <i class="fas fa-hashtag me-1"></i>{{ $incident->reference_number }}
                                                    </h6>
                                                    <p class="mb-1 fw-semibold">{{ $incident->title }}</p>
                                                    <p class="text-muted small">{{ \Illuminate\Support\Str::limit($incident->description, 100) }}</p>
                                                </div>

                                                @php
                                                    $images = $incident->mediaEvidence->filter(function($media) {
                                                        return str_starts_with($media->mime_type, 'image/');
                                                    });
                                                    $videos = $incident->mediaEvidence->filter(function($media) {
                                                        return str_starts_with($media->mime_type, 'video/');
                                                    });
                                                @endphp

                                                @if($images->count() > 0)
                                                    <div class="mb-4">
                                                        <h6 class="mb-3 text-primary">
                                                            <i class="fas fa-camera me-2"></i>Photos ({{ $images->count() }})
                                                        </h6>
                                                        <div class="row g-3">
                                                            @foreach($images as $image)
                                                                <div class="col-12 col-sm-6 col-md-4">
                                                                    <div class="shadow-sm card" style="border-radius: 10px; overflow: hidden;">
                                                                        <img src="{{ $image->url }}"
                                                                            class="card-img-top media-thumbnail"
                                                                            alt="Evidence photo"
                                                                            onclick="previewImage('{{ $image->url }}', '{{ $image->file_name }}')"
                                                                            style="height: 200px; object-fit: cover;">
                                                                        <div class="p-3 card-body">
                                                                            <small class="mb-2 text-muted d-block">
                                                                                <i class="fas fa-file-image me-1"></i>{{ $image->file_name }}
                                                                            </small>
                                                                            @if($image->latitude && $image->longitude)
                                                                                <small class="mb-2 text-muted d-block">
                                                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $image->address }}
                                                                                </small>
                                                                            @endif
                                                                            <button class="btn btn-outline-primary btn-sm w-100" onclick="downloadFile('{{ $image->url }}', '{{ $image->file_name }}')" style="border-radius: 20px;">
                                                                                <i class="fas fa-download me-1"></i>Download
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if($videos->count() > 0)
                                                    <div class="mb-4">
                                                        <h6 class="mb-3 text-primary">
                                                            <i class="fas fa-video me-2"></i>Videos ({{ $videos->count() }})
                                                        </h6>
                                                        <div class="row g-3">
                                                            @foreach($videos as $video)
                                                                <div class="col-12 col-md-6">
                                                                    <div class="shadow-sm card" style="border-radius: 10px; overflow: hidden;">
                                                                        <div class="video-container">
                                                                            <video controls class="w-100" style="max-height: 250px; border-radius: 10px 10px 0 0;">
                                                                                <source src="{{ $video->url }}" type="{{ $video->mime_type }}">
                                                                                Your browser does not support the video tag.
                                                                            </video>
                                                                        </div>
                                                                        <div class="p-3 card-body">
                                                                            <small class="mb-2 text-muted d-block">
                                                                                <i class="fas fa-file-video me-1"></i>{{ $video->file_name }}
                                                                            </small>
                                                                            @if($video->latitude && $video->longitude)
                                                                                <small class="mb-2 text-muted d-block">
                                                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $video->address }}
                                                                                </small>
                                                                            @endif
                                                                            <button class="btn btn-outline-primary btn-sm w-100" onclick="downloadFile('{{ $video->url }}', '{{ $video->file_name }}')" style="border-radius: 20px;">
                                                                                <i class="fas fa-download me-1"></i>Download
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                                                <button type="button" class="px-4 btn btn-secondary btn-lg" data-bs-dismiss="modal" style="border-radius: 25px;">
                                                    <i class="fas fa-times me-1"></i>Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <nav aria-label="Reports pagination" class="mt-4">
                            {{ $incidents->links('pagination::bootstrap-5') }}
                        </nav>
                    @else
                        <div class="py-5 text-center">
                            <i class="mb-3 fas fa-clipboard-list fa-3x text-muted"></i>
                            <h4 class="text-muted">No Reports Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'type', 'search']))
                                    No reports match your search criteria.
                                @else
                                    You haven't submitted any incident reports yet.
                                @endif
                            </p>
                            <a href="{{ route('report-incident') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Report Your First Incident
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Global Image Preview Modal -->
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

    <style>
        .incident-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border-left: 4px solid #6c757d;
        }

        .incident-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .incident-card.pending { border-left-color: #ffc107; }
        .incident-card.in-progress { border-left-color: #0dcaf0; }
        .incident-card.resolved { border-left-color: #198754; }
        .incident-card.rejected { border-left-color: #dc3545; }

        .status-badge {
            font-size: 0.75em;
        }

        /* Pagination Styles */
        .pagination {
            margin-bottom: 0;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }

        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid #dee2e6;
            color: #0d6efd;
            background-color: #fff;
            border-radius: 0.375rem;
            min-width: 2.5rem;
            text-align: center;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #0a58ca;
        }

        .media-thumbnail {
            height: 150px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }

        .media-thumbnail:hover {
            transform: scale(1.05);
        }

        .video-container {
            position: relative;
            background: #000;
        }

        .video-container video {
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .video-placeholder {
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .video-placeholder:hover {
            transform: scale(1.05);
        }

        .image-fallback {
            background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), 
                        linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #f8f9fa 75%), 
                        linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            height: 150px;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .pagination {
                gap: 0.15rem;
            }

            .pagination .page-link {
                padding: 0.375rem 0.5rem;
                font-size: 0.8rem;
                min-width: 2.25rem;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(imageUrl, fileName) {
            console.log('Previewing image:', imageUrl);
            
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

        // Function to play video in a modal
        function playVideoInModal(videoUrl, fileName) {
            // Create a modal for video playback
            const videoModal = document.createElement('div');
            videoModal.className = 'modal fade';
            videoModal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${fileName}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <video controls autoplay class="w-100" style="max-height: 70vh;">
                                <source src="${videoUrl}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="downloadFile('${videoUrl}', '${fileName}')">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(videoModal);
            const modal = new bootstrap.Modal(videoModal);
            modal.show();
            
            // Remove modal from DOM after it's hidden
            videoModal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(videoModal);
            });
        }

        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        
        // Handle search button click
        searchButton.addEventListener('click', function(e) {
            if (!searchInput.value.trim()) {
                e.preventDefault(); // Prevent form submission
                return false;
            }
        });
        
        // Handle enter key in search input
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (!this.value.trim()) {
                    e.preventDefault(); // Prevent form submission
                    return false;
                }
            }
        });
        
        // Auto-submit form when filter selects change
        const selects = filterForm.querySelectorAll('select[name="status"], select[name="type"]');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });

            // Add click handlers for video placeholders
            document.querySelectorAll('.video-placeholder').forEach(placeholder => {
                placeholder.addEventListener('click', function() {
                    const videoUrl = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                    const fileName = this.closest('.card').querySelector('small').textContent.trim();
                    playVideoInModal(videoUrl, fileName);
                });
            });

            // Fix image error handling - REMOVE PLACEHOLDER FALLBACK
            document.querySelectorAll('.media-thumbnail').forEach(img => {
                img.addEventListener('error', function() {
                    console.error('Failed to load image:', this.src);
                    // Don't set placeholder - just hide the broken image
                    this.style.display = 'none';
                    // Show a fallback icon instead
                    const fallback = document.createElement('div');
                    fallback.className = 'd-flex align-items-center justify-content-center bg-light';
                    fallback.style.height = '150px';
                    fallback.innerHTML = '<i class="fas fa-image text-muted fa-2x"></i>';
                    this.parentNode.insertBefore(fallback, this);
                });
                
                img.addEventListener('load', function() {
                    console.log('Successfully loaded image:', this.src);
                });
            });
        });
    </script>
@endsection