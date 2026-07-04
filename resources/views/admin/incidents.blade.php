@extends('layouts.app')
@section('title', 'Incident Reports | EcoConnect - DENR CENRO Sanchez Mira')
@section('page-title', 'Incident Reports Management')
@section('content')
    <section id="reports" class="py-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Incident Reports Management</h2>
                            <p class="mb-0 text-muted">Manage all environmental incident reports</p>
                        </div>
                        <div class="gap-2 d-flex">
                            <!-- Export Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.incidents.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                                            <i class="fas fa-file-excel text-success me-2"></i>Excel (.xlsx)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.incidents.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
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
                            <a href="{{ route('admin.incidents.print', request()->query()) }}" class="btn btn-outline-secondary" target="_blank">
                                <i class="fas fa-print"></i> Print
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="mb-4 card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.incidents') }}" id="filterForm">
                        <div class="row g-3">
                            <!-- Status Filter -->
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                </select>
                            </div>

                            <!-- Type Filter -->
                            <div class="col-md-2">
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

                            <!-- Priority Filter -->
                            <div class="col-md-2">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                                    <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>

                            <!-- Date Range Filters -->
                            <div class="col-md-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Date From</label>
                                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Date To</label>
                                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Municipality Filter -->
                            <div class="col-md-2">
                                <label class="form-label">Municipality</label>
                                <select class="form-select" name="municipality">
                                    <option value="">All Municipalities</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>
                                            {{ $municipality }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search -->
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search reports...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request()->hasAny(['status', 'type', 'priority', 'municipality', 'date_from', 'date_to', 'search']))
                                    <a href="{{ route('admin.incidents') }}" class="btn btn-secondary" title="Clear Filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Active Filters Display -->
                        @if(request()->hasAny(['status', 'type', 'priority', 'municipality', 'date_from', 'date_to', 'search']))
                        <div class="p-2 mt-3 rounded bg-light">
                            <small class="text-muted fw-semibold">Active Filters:</small>
                            @if(request('status'))
                                <span class="badge bg-primary ms-2">Status: {{ request('status') }}</span>
                            @endif
                            @if(request('type'))
                                <span class="badge bg-primary ms-2">Type: {{ request('type') }}</span>
                            @endif
                            @if(request('priority'))
                                <span class="badge bg-primary ms-2">Priority: {{ request('priority') }}</span>
                            @endif
                            @if(request('municipality'))
                                <span class="badge bg-primary ms-2">Municipality: {{ request('municipality') }}</span>
                            @endif
                            @if(request('date_from') || request('date_to'))
                                <span class="badge bg-primary ms-2">
                                    Date: {{ request('date_from') ?: 'Start' }} to {{ request('date_to') ?: 'End' }}
                                </span>
                            @endif
                            @if(request('search'))
                                <span class="badge bg-primary ms-2">Search: "{{ request('search') }}"</span>
                            @endif
                        </div>
                        @endif
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
                                        <th width="150">Reporter</th>
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
                                        <td>
                                            <div class="fw-medium">{{ $incident->user->fname }} {{ substr($incident->user->mname, 0, 1) ?? '' }} {{ $incident->user->lname }} {{ $incident->user->extname ?? '' }}</div>
                                            <small class="text-muted">{{ $incident->user->email ?? '' }}</small>
                                        </td>
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

                                                @if($incident->status === 'Pending')
                                                    <!-- Assign to Police Button -->
                                                    <button class="btn btn-outline-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#assignModal{{ $incident->id }}"
                                                            title="Assign to Police/BFP">
                                                        <i class="fas fa-user-shield"></i>
                                                    </button>

                                                    <!-- Resolve Button -->
                                                    <button class="btn btn-outline-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#resolveModal{{ $incident->id }}"
                                                            title="Resolve Report">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>

                                                    <!-- Reject Button -->
                                                    <button class="btn btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal{{ $incident->id }}"
                                                            title="Reject Report">
                                                        <i class="fas fa-times-circle"></i>
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

                                                    @if($incident->rejection_reason)
                                                        <div class="mt-3 row">
                                                            <div class="col-12">
                                                                <h6>Rejection Reason</h6>
                                                                <p class="p-3 rounded bg-light text-danger">{{ $incident->rejection_reason }}</p>
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

                                                                <form method="POST" action="{{ route('admin.incidents.respond', $incident) }}">
                                                                    @csrf
                                                                    <textarea name="follow_up_text"
                                                                            class="form-control mb-2 @error('follow_up_text') is-invalid @enderror"
                                                                            rows="3"
                                                                            placeholder="Post a staff response..."
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

                                    <!-- Assign to Police Modal -->
                                    <div class="modal fade" id="assignModal{{ $incident->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Assign to Police/BFP - {{ $incident->reference_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.incidents.assign', $incident->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="police_user" class="form-label">Select Police/BFP Officer</label>
                                                            <select class="form-select" id="police_user" name="assigned_to" required>
                                                                <option value="">-- Select Police/BFP Officer --</option>
                                                                @foreach($policeUsers as $police)
                                                                    <option value="{{ $police->id }}"
                                                                            {{ $incident->assigned_to == $police->id ? 'selected' : '' }}>
                                                                        {{ $police->fname }} {{ $police->mname ?? ''}} {{ $police->lname }} {{ $police->extname ?? ''}} ({{ $police->email }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="priority" class="form-label">Priority Level</label>
                                                            <select class="form-select" id="priority" name="priority" required>
                                                                <option value="Normal" {{ $incident->priority == 'Normal' ? 'selected' : '' }}>Normal</option>
                                                                <option value="High" {{ $incident->priority == 'High' ? 'selected' : '' }}>High</option>
                                                                <option value="Urgent" {{ $incident->priority == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Assign to Police/BFP</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Report Modal -->
                                    <div class="modal fade" id="rejectModal{{ $incident->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Report - {{ $incident->reference_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.incidents.reject', $incident->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Warning:</strong> This action cannot be undone. The reporter will be notified of the rejection.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                                                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" placeholder="Please provide a clear reason for rejecting this report..." required>{{ old('rejection_reason') }}</textarea>
                                                            <div class="form-text">
                                                                This reason will be shared with the reporter.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject Report</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Resolve Report Modal -->
                                    <div class="modal fade" id="resolveModal{{ $incident->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Resolve Report Report - {{ $incident->reference_number }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.incidents.resolve', $incident->id) }}" method="POST">
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
                                                                                <img src="{{ asset('storage/' . $image->file_path) }}"
                                                                                    class="card-img-top media-thumbnail"
                                                                                    alt="Evidence photo"
                                                                                    onclick="previewImage('{{ asset('storage/' . $image->file_path) }}', '{{ $image->file_name }}')"
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
                                                                                    <div class="mt-3">
                                                                                        <a href="https://www.google.com/maps/dir/18.567201,121.2254053/{{ $image->latitude }},{{ $image->longitude }}"
                                                                                        target="_blank"
                                                                                        class="btn btn-sm btn-outline-primary">
                                                                                            <i class="bi bi-geo-alt me-1"></i> Show Directions
                                                                                        </a>
                                                                                    </div>
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
                                                                                        <source src="{{ asset('storage/' . $video->file_path) }}" type="{{ $video->mime_type }}">
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
                                                                                    <div class="mt-3">
                                                                                        <a href="https://www.google.com/maps/dir/18.567201,121.2254053/{{ $video->latitude }},{{ $video->longitude }}"
                                                                                        target="_blank"
                                                                                        class="btn btn-sm btn-outline-primary">
                                                                                            <i class="bi bi-geo-alt me-1"></i> Show Directions
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
