@extends('layouts.app')
@section('title', 'Incident Reports | EcoConnect - DENR CENRO Sanchez Mira')
@section('content')
    <section id="reports" class="py-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Incident Reports Management</h2>
                            <p class="mb-0 text-muted">Manage all assigned environmental incident reports</p>
                        </div>
                        <div class="gap-2 d-flex">
                            <!-- Export Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('police.incidents.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                                            <i class="fas fa-file-excel text-success me-2"></i>Excel (.xlsx)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('police.incidents.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                                            <i class="fas fa-file-csv text-primary me-2"></i>CSV (.csv)
                                        </a>
                                    </li>
                                    {{-- <li>
                                        <a class="dropdown-item" href="{{ route('admin.incidents.export', array_merge(request()->query(), ['format' => 'pdf'])) }}">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>PDF (.pdf)
                                        </a>
                                    </li> --}}
                                </ul>
                            </div>
                            <a href="{{ route('police.incidents.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="mb-4 card">
                <div class="card-body">
                    <form method="GET" action="{{ route('police.incidents') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="type">
                                    <option value="">All Types</option>
                                    <option value="Illegal Logging" {{ request('type') == 'Illegal Logging' ? 'selected' : '' }}>Illegal Logging</option>
                                    <option value="Pollution" {{ request('type') == 'Pollution' ? 'selected' : '' }}>Pollution</option>
                                    <option value="Wildlife Crime" {{ request('type') == 'Wildlife Crime' ? 'selected' : '' }}>Wildlife Crime</option>
                                    <option value="Illegal Waste Disposal" {{ request('type') == 'Illegal Waste Disposal' ? 'selected' : '' }}>Illegal Waste Disposal</option>
                                    <option value="Other" {{ request('type') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search reports...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request()->hasAny(['status', 'type', 'priority', 'search']))
                                    <a href="{{ route('police.incidents') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title">All Incident Reports</h5>
                    <div class="gap-2 d-flex">
                        <span class="text-muted">
                            Showing {{ $incidents->firstItem() }} to {{ $incidents->lastItem() }} of {{ $incidents->total() }} reports
                        </span>
                    </div>
                </div>
                <div class="p-0 card-body">
                    @if($incidents->count() > 0)
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="120">Report ID</th>
                                        {{-- <th width="150">Reporter</th> --}}
                                        <th width="120">Type</th>
                                        <th>Description</th>
                                        <th width="100">Priority</th>
                                        <th width="100">Status</th>
                                        <th width="120">Date Reported</th>
                                        <th width="120" class="text-center">Media</th>
                                        <th width="150" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incidents as $incident)
                                    <tr class="align-middle">
                                        <td>
                                            <div class="fw-semibold text-primary">{{ $incident->reference_number }}</div>
                                            <small class="text-muted">#{{ $incident->id }}</small>
                                        </td>
                                        {{-- <td>
                                            <div class="fw-medium">{{ $incident->user->fname }} {{ substr($incident->user->mname, 0, 1) ?? '' }} {{ $incident->user->lname }} {{ $incident->user->extname ?? '' }}</div>
                                            <small class="text-muted">{{ $incident->user->email ?? '' }}</small>
                                        </td> --}}
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $incidentTypes[$incident->incident_type] ?? $incident->incident_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="{{ $incident->description }}">
                                                {{ \Illuminate\Support\Str::limit($incident->description, 80) }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $incident->incident_date->format('M j, Y') }} at {{ $incident->incident_time }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $priorityBadgeClasses[$incident->priority] }}">
                                                <i class="fas fa-flag me-1"></i>{{ $incident->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadgeClasses[$incident->status] }}">
                                                {{ $incident->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $incident->created_at->format('M j, Y') }}</div>
                                            <small class="text-muted">{{ $incident->created_at->format('g:i A') }}</small>
                                        </td>
                                        <td class="text-center">
                                            @if($incident->mediaEvidence->count() > 0)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-camera me-1"></i>{{ $incident->mediaEvidence->count() }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        {{-- <td class="text-center">
                                            @if($incident->documentation)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-file-alt me-1"></i>Documented
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td> --}}
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#incidentModal{{ $incident->id }}"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($incident->mediaEvidence->count() > 0)
                                                    <button class="btn btn-outline-info"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#mediaModal{{ $incident->id }}"
                                                            title="View Media">
                                                        <i class="fas fa-images"></i>
                                                    </button>
                                                @endif

                                                @if($incident->status !== 'Resolved')
                                                    <button class="btn btn-outline-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#takenModal{{ $incident->id }}"
                                                            title="Mark as Taken">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Incident Details Modal -->
                                    <div class="modal fade" id="incidentModal{{ $incident->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Incident Details - {{ $incident->reference_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Reference Number</h6>
                                                            <p class="text-primary fw-bold">{{ $incident->reference_number }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Status</h6>
                                                            <p>
                                                                <span class="badge {{ $statusBadgeClasses[$incident->status] }}">
                                                                    {{ $incident->status }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 row">
                                                        <div class="col-md-6">
                                                            <h6>Incident Type</h6>
                                                            <p>{{ $incidentTypes[$incident->incident_type] ?? $incident->incident_type }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Priority</h6>
                                                            <p>
                                                                <span class="badge {{ $priorityBadgeClasses[$incident->priority] }}">
                                                                    {{ $incident->priority }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 row">
                                                        <div class="col-12">
                                                            <h6>Description</h6>
                                                            <p class="p-3 rounded bg-light">{{ $incident->description }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 row">
                                                        <div class="col-md-6">
                                                            <h6>Incident Date</h6>
                                                            <p>{{ $incident->incident_date->format('F j, Y') }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Incident Time</h6>
                                                            <p>{{ $incident->incident_time }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 row">
                                                        <div class="col-md-6">
                                                            <h6>Reported On</h6>
                                                            <p>{{ $incident->created_at->format('F j, Y g:i A') }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Last Updated</h6>
                                                            <p>{{ $incident->updated_at->format('F j, Y g:i A') }}</p>
                                                        </div>
                                                    </div>

                                                    @if($incident->assignedTo)
                                                        <div class="mt-3 row">
                                                            <div class="col-12">
                                                                <h6>Assigned To</h6>
                                                                <p>{{ $incident->assignedTo->lname }}, {{ $incident->assignedTo->fname }} {{ substr($incident->assignedTo->mname, 0, 1) ?? ''}} {{ $incident->assignedTo->extname ?? ''}} ({{ $incident->assignedTo->email }})</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($incident->resolution_details)
                                                        <div class="mt-3 row">
                                                            <div class="col-12">
                                                                <h6>Resolution Details</h6>
                                                                <p class="p-3 rounded bg-light text-success">{{ $incident->resolution_details }}</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($incident->resolution_details)
                                                        <div class="mt-3 row">
                                                            <div class="col-12">
                                                                <h6>Rejection Reason</h6>
                                                                <p class="p-3 rounded bg-light text-danger">{{ $incident->resolution_details }}</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- Follow-ups Section -->
                                                    <div class="mt-4 pt-4 border-top">
                                                        <h6 class="mb-3"><i class="fas fa-comments me-2"></i>Follow-ups</h6>

                                                        @if($incident->followups && $incident->followups->count() > 0)
                                                            <div class="mb-4">
                                                                @foreach($incident->followups as $followup)
                                                                    <div class="mb-3 card border-left">
                                                                        <div class="card-body">
                                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                                <div>
                                                                                    <h6 class="mb-1">{{ $followup->user->fname ?? 'Unknown' }} {{ $followup->user->lname ?? '' }}</h6>
                                                                                    <small class="text-muted">{{ $followup->created_at->diffForHumans() }}</small>
                                                                                </div>
                                                                                @if($followup->follow_up_type === 'Staff Response')
                                                                                    <span class="badge bg-success">{{ $followup->follow_up_type }}</span>
                                                                                @elseif($followup->follow_up_type === 'User Update')
                                                                                    <span class="badge bg-info">{{ $followup->follow_up_type }}</span>
                                                                                @else
                                                                                    <span class="badge bg-secondary">{{ $followup->follow_up_type }}</span>
                                                                                @endif
                                                                            </div>
                                                                            <p class="mt-2 mb-0">{{ $followup->follow_up_text }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <p class="text-muted small">No follow-ups yet.</p>
                                                        @endif

                                                        <!-- Add Staff Response Form -->
                                                        <div class="mt-4 card bg-light">
                                                            <div class="card-body">
                                                                <h6 class="mb-3">
                                                                    <i class="fas fa-reply me-1"></i>Post Staff Response
                                                                </h6>

                                                                <form method="POST" action="{{ route('followups.respond', $incident) }}">
                                                                    @csrf
                                                                    <textarea name="follow_up_text"
                                                                            class="form-control mb-2 @error('follow_up_text') is-invalid @enderror"
                                                                            rows="3"
                                                                            placeholder="Post a response..."
                                                                            required></textarea>
                                                                    @error('follow_up_text')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                    <button type="submit" class="btn btn-success btn-sm">
                                                                        <i class="fas fa-paper-plane me-1"></i>Post Response
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mark as Taken Modal -->
                                    <div class="modal fade" id="takenModal{{ $incident->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Mark as Taken - {{ $incident->reference_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('police.incidents.taken', $incident->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            <strong>Documentation:</strong> Please document that this incident has been taken and is now under your department's investigation/action.
                                                        </div>

                                                        <!-- Documentation Notes -->
                                                        <div class="mb-3">
                                                            <label for="resolution_details{{ $incident->id }}" class="form-label">
                                                                Documentation Notes <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea class="form-control"
                                                                    id="resolution_details{{ $incident->id }}"
                                                                    name="resolution_details"
                                                                    rows="4"
                                                                    placeholder="Document details about taking this incident (e.g., initial response, actions taken, next steps)..."
                                                                    required
                                                                    minlength="10">{{ old('resolution_details') }}</textarea>
                                                            <div class="form-text">
                                                                These notes will be shared with the reporter.
                                                            </div>
                                                        </div>

                                                        <!-- Evidence Images Upload -->
                                                        <div class="mb-3">
                                                            <label for="evidence_images{{ $incident->id }}" class="form-label">
                                                                <i class="fas fa-images me-2"></i>Evidence Photos <span class="text-muted">(Optional)</span>
                                                            </label>
                                                            <div class="border-dashed card bg-light">
                                                                <div class="text-center card-body">
                                                                    <input type="file"
                                                                        id="evidence_images{{ $incident->id }}"
                                                                        name="evidence_images[]"
                                                                        class="form-control d-none"
                                                                        multiple
                                                                        accept="image/*"
                                                                        onchange="updateImagePreview(this, 'preview{{ $incident->id }}')">
                                                                    <label for="evidence_images{{ $incident->id }}" class="mb-0 cursor-pointer">
                                                                        <i class="mb-2 fas fa-cloud-upload-alt fa-2x text-primary d-block"></i>
                                                                        <p class="mb-1">Click to upload or drag and drop</p>
                                                                        <p class="mb-0 text-muted small">PNG, JPG, GIF up to 5MB each</p>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div id="preview{{ $incident->id }}" class="mt-3"></div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-check-circle me-2"></i>Mark as Taken
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- <!-- Resolve Report Modal -->
                                    <div class="modal fade" id="resolveModal{{ $incident->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Resolve Report Report - {{ $incident->reference_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('police.incidents.resolve', $incident->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Warning:</strong> This action cannot be undone. The reporter will be notified of the resolution details.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="resolution_details" class="form-label">Resolution Details*</label>
                                                            <textarea class="form-control" id="resolution_details" name="resolution_details" rows="4" placeholder="Please provide a clear resolution details on how the report is resolved..." required minlength="10">{{ old('resolution_details') }}</textarea>
                                                            <div class="form-text">
                                                                This resolution details will be shared with the reporter.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Resolve Report</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div> --}}

                                    <!-- Media Modal for each incident -->
                                    @if($incident->mediaEvidence->count() > 0)
                                        <div class="modal fade" id="mediaModal{{ $incident->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Media Evidence - {{ $incident->reference_number }}
                                                            <span class="badge bg-primary ms-2">{{ $incident->mediaEvidence->count() }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-4">
                                                            <p class="mb-0 text-muted">{{ $incident->title }}</p>
                                                            <p class="text-muted">{{ \Illuminate\Support\Str::limit($incident->description, 100) }}</p>
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
                                                                <h6>Photos ({{ $images->count() }})</h6>
                                                                <div class="row g-3">
                                                                    @foreach($images as $image)
                                                                        <div class="col-md-4 col-sm-6">
                                                                            <div class="card">
                                                                                <img src="{{ asset('storage/' . $image->file_path) }}"
                                                                                    class="card-img-top media-thumbnail"
                                                                                    alt="Evidence photo"
                                                                                    onclick="previewImage('{{ asset('storage/' . $image->file_path) }}', '{{ $image->file_name }}')">
                                                                                <div class="p-2 card-body">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-file-image"></i> {{ $image->file_name }}
                                                                                    </small>
                                                                                    @if($image->latitude && $image->longitude)
                                                                                        <br><small class="text-muted">
                                                                                            <i class="fas fa-map-marker-alt"></i> {{ $image->address }}
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
                                                                <h6>Videos ({{ $videos->count() }})</h6>
                                                                <div class="row g-3">
                                                                    @foreach($videos as $video)
                                                                        <div class="col-md-6">
                                                                            <div class="card">
                                                                                <div class="video-container">
                                                                                    <video controls class="w-100" style="max-height: 300px;">
                                                                                        <source src="{{ asset('storage/' . $video->file_path) }}" type="{{ $video->mime_type }}">
                                                                                        Your browser does not support the video tag.
                                                                                    </video>
                                                                                </div>
                                                                                <div class="p-2 card-body">
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-file-video"></i> {{ $video->file_name }}
                                                                                    </small>
                                                                                    @if($video->latitude && $video->longitude)
                                                                                        <br><small class="text-muted">
                                                                                            <i class="fas fa-map-marker-alt"></i> {{ $image->address }}
                                                                                        </small>
                                                                                    @endif
                                                                                    <div class="mt-2">
                                                                                        <button class="btn btn-sm btn-outline-primary" onclick="downloadFile('{{ asset('storage/' . $video->file_path) }}', '{{ $video->file_name }}')">
                                                                                            <i class="fas fa-download"></i> Download
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $incidents->firstItem() }} to {{ $incidents->lastItem() }} of {{ $incidents->total() }} entries
                            </div>
                            <div>
                                {{ $incidents->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        <div class="py-5 text-center">
                            <i class="mb-3 fas fa-clipboard-list fa-3x text-muted"></i>
                            <h4 class="text-muted">No Reports Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'type', 'priority', 'search']))
                                    No reports match your search criteria.
                                @else
                                    No incident reports have been submitted yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

        <!-- Global Image Preview Modal - UPDATED -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewTitle" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="border-0 modal-header">
                    <h6 class="text-white modal-title" id="imagePreviewTitle">Image Preview</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="p-0 modal-body d-flex justify-content-center align-items-center">
                    <img id="previewImage" src="" class="img-fluid" style="max-height: 80vh; object-fit: contain; cursor: zoom-out;" alt="Preview" onclick="closeImagePreview()">
                </div>
                <div class="border-0 modal-footer justify-content-center">
                    <a id="downloadImage" href="#" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Image upload preview and drag-drop functionality
        function updateImagePreview(input, previewId) {
            const preview = document.getElementById(previewId);
            preview.innerHTML = '';

            if (input.files && input.files.length > 0) {
                const container = document.createElement('div');
                container.className = 'row g-2';

                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-4 col-sm-6';

                        col.innerHTML = `
                            <div class="card">
                                <img src="${e.target.result}" class="card-img-top" alt="Preview" style="height: 150px; object-fit: cover;">
                                <div class="p-2 card-body">
                                    <small class="text-muted d-block text-truncate" title="${file.name}">
                                        ${file.name}
                                    </small>
                                    <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                                </div>
                            </div>
                        `;

                        container.appendChild(col);
                    };

                    reader.readAsDataURL(file);
                });

                preview.appendChild(container);
            }
        }

        // Drag and drop functionality for image uploads
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('input[name="evidence_images[]"]').forEach(input => {
                const parent = input.closest('.card');

                parent.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    parent.classList.add('bg-primary', 'bg-opacity-10');
                });

                parent.addEventListener('dragleave', () => {
                    parent.classList.remove('bg-primary', 'bg-opacity-10');
                });

                parent.addEventListener('drop', (e) => {
                    e.preventDefault();
                    parent.classList.remove('bg-primary', 'bg-opacity-10');
                    input.files = e.dataTransfer.files;

                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                });
            });
        });
        function previewImage(imageUrl, fileName) {
            console.log('Previewing image:', imageUrl);

            const previewImage = document.getElementById('previewImage');
            const downloadLink = document.getElementById('downloadImage');
            const title = document.getElementById('imagePreviewTitle');

            // Set content
            previewImage.src = imageUrl;
            downloadLink.href = imageUrl;
            downloadLink.download = fileName || 'image.jpg';
            title.textContent = fileName || 'Image Preview';

            // Get modal element
            const modalElement = document.getElementById('imagePreviewModal');

            // Create modal with focus trap disabled
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: false // Disable focus trap
            });

            // Manually handle focus when modal opens
            modalElement.addEventListener('shown.bs.modal', function() {
                // Focus on close button but don't trap
                const closeBtn = modalElement.querySelector('[data-bs-dismiss="modal"]');
                if (closeBtn) {
                    closeBtn.focus();
                }
            });

            // Show modal
            modal.show();
        }

        // Alternative: Use a custom modal without focus trap
        function previewImageCustom(imageUrl, fileName) {
            const modalElement = document.getElementById('imagePreviewModal');

            // Remove any existing Bootstrap modal instance
            const existingModal = bootstrap.Modal.getInstance(modalElement);
            if (existingModal) {
                existingModal.dispose();
            }

            // Manually show modal without Bootstrap's focus trap
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            modalElement.setAttribute('aria-hidden', 'false');

            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);

            // Add body classes
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
            document.body.style.paddingRight = '0px';

            // Set content
            document.getElementById('previewImage').src = imageUrl;
            document.getElementById('downloadImage').href = imageUrl;
            document.getElementById('imagePreviewTitle').textContent = fileName || 'Image Preview';

            // Add custom close handler
            function closeModal() {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                modalElement.setAttribute('aria-hidden', 'true');

                // Remove backdrop
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());

                // Remove body classes
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';

                // Remove event listeners
                document.removeEventListener('keydown', handleEscape);
                backdrop.removeEventListener('click', closeModal);
            }

            // Handle escape key
            function handleEscape(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            }

            // Add event listeners
            document.addEventListener('keydown', handleEscape);
            backdrop.addEventListener('click', closeModal);

            // Add close button listeners
            const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', closeModal);
            });
        }
    </script>

    <style>
        .border-dashed {
            border: 2px dashed #dee2e6 !important;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card.bg-light.border-dashed:hover {
            border-color: #0d6efd !important;
            background-color: rgba(13, 110, 253, 0.05) !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .table th {
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }

        @media (max-width: 768px) {
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-group-sm > .btn {
                margin-bottom: 2px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
