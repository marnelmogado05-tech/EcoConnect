@extends('layouts.mobile-user')

@section('title', 'Dashboard - EcoConnect')

@section('content')
    <!-- Statistics Cards -->
    <div class="mb-4 row g-3">
        <div class="col-6 col-md-3">
            <div class="text-white card bg-primary h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $stats['total'] }}</h3>
                            <p class="mb-0 opacity-75 small">Total Reports</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-white card bg-warning h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $stats['pending'] }}</h3>
                            <p class="mb-0 opacity-75 small">Pending</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-white card bg-info h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $stats['in_progress'] }}</h3>
                            <p class="mb-0 opacity-75 small">In Progress</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-tasks fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="text-white card bg-success h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $stats['resolved'] }}</h3>
                            <p class="mb-0 opacity-75 small">Resolved</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="shadow-sm card">
        <div class="gap-2 card-header bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
            <h5 class="mb-0 card-title fw-semibold">Recent Activity</h5>
            <a href="{{ route('incidents') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-right me-1"></i>View All
            </a>
        </div>

        <div class="p-0 card-body">
            @if($recentIncidents->count() > 0)
                <!-- Desktop Table View -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Report ID</th>
                                    <th class="fw-semibold">Type</th>
                                    <th class="fw-semibold">Location</th>
                                    <th class="fw-semibold">Status</th>
                                    <th class="fw-semibold">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentIncidents as $incident)
                                <tr>
                                    <td class="fw-semibold text-primary">#{{ $incident->reference_number }}</td>
                                    <td>
                                        @php
                                            $incidentTypes = [
                                                'illegal_logging' => 'Illegal Logging',
                                                'pollution' => 'Pollution',
                                                'wildlife_crime' => 'Wildlife Crime',
                                                'illegal_waste_disposal' => 'Illegal Waste Disposal',
                                                'other' => 'Other'
                                            ];
                                        @endphp
                                        {{ $incident->incident_type?->label() }}
                                    </td>
                                    <td>
                                        @if($incident->latitude && $incident->longitude)
                                            <small class="text-muted">{{ number_format($incident->latitude, 4) }}, {{ number_format($incident->longitude, 4) }}</small>
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'Pending' => 'bg-warning text-dark',
                                                'In Progress' => 'bg-info text-white',
                                                'Resolved' => 'bg-success text-white',
                                                'Rejected' => 'bg-danger text-white'
                                            ];
                                        @endphp
                                        <span class="badge {{ $incident->status?->badgeClass() }} fw-semibold">
                                            {{ $incident->status?->value }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $incident->created_at->format('M j, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="d-md-none">
                    @foreach($recentIncidents as $incident)
                        <div class="p-3 border-bottom">
                            <div class="mb-2 d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-primary">#{{ $incident->reference_number }}</h6>
                                    <p class="mb-1 small text-muted">
                                        @php
                                            $incidentTypes = [
                                                'illegal_logging' => 'Illegal Logging',
                                                'pollution' => 'Pollution',
                                                'wildlife_crime' => 'Wildlife Crime',
                                                'illegal_waste_disposal' => 'Illegal Waste Disposal',
                                                'other' => 'Other'
                                            ];
                                        @endphp
                                        {{ $incident->incident_type?->label() }}
                                    </p>
                                    @if($incident->latitude && $incident->longitude)
                                        <p class="mb-1 small text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>{{ number_format($incident->latitude, 4) }}, {{ number_format($incident->longitude, 4) }}
                                        </p>
                                    @endif
                                    <small class="text-muted">{{ $incident->created_at->format('M j, Y') }}</small>
                                </div>
                                <div class="flex-shrink-0 ms-2">
                                    @php
                                        $statusClasses = [
                                            'Pending' => 'bg-warning text-dark',
                                            'In Progress' => 'bg-info text-white',
                                            'Resolved' => 'bg-success text-white',
                                            'Rejected' => 'bg-danger text-white'
                                        ];
                                    @endphp
                                    <span class="badge {{ $incident->status?->badgeClass() }} fw-semibold px-2 py-1">
                                        {{ $incident->status?->value }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-5 text-center">
                    <i class="mb-3 fas fa-clipboard-list fa-3x text-muted"></i>
                    <h6 class="mb-2 text-muted">No incidents reported yet</h6>
                    <p class="mb-3 text-muted small">Start by reporting your first environmental incident</p>
                    <a href="{{ route('report-incident') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Report Your First Incident
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-4 shadow-sm card">
        <div class="card-header bg-light">
            <h5 class="mb-0 card-title fw-semibold">Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <a href="{{ route('report-incident') }}" class="py-4 btn btn-primary w-100 h-100 d-flex flex-column align-items-center text-decoration-none">
                        <i class="mb-2 fas fa-plus-circle fa-2x"></i>
                        <span class="fw-semibold">Report Incident</span>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <a href="{{ route('incidents') }}" class="py-4 btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center text-decoration-none">
                        <i class="mb-2 fas fa-search fa-2x"></i>
                        <span class="fw-semibold">Track Reports</span>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <a href="{{ route('profile.edit') }}" class="py-4 btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center text-decoration-none">
                        <i class="mb-2 fas fa-user-edit fa-2x"></i>
                        <span class="fw-semibold">Update Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
