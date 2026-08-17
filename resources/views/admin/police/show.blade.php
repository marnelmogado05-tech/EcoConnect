@extends('layouts.app')
@section('title', 'Police Details | EcoConnect - DENR CENRO Sanchez Mira')
@section('content')
    <section id="police-details" class="py-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Police Details</h2>
                            <p class="mb-0 text-muted">{{ $police->fname }} {{ $police->lname }}</p>
                        </div>
                        <div class="gap-2 d-flex">
                            <a href="{{ route('admin.police') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Manage Police
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- police Information -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 card-title">Profile Information</h5>
                        </div>
                        <div class="text-center card-body">
                            <div class="mx-auto mb-3 text-white avatar bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 1.5rem;">
                                {{ strtoupper(substr($police->fname, 0, 1)) }}{{ strtoupper(substr($police->lname, 0, 1)) }}
                            </div>
                            <h4>{{ $police->fname }} {{ $police->lname }}</h4>
                            <span class="badge {{ $police->status?->value === 'Active' ? 'bg-success' : 'bg-danger' }} mb-3">
                                {{ $police->status?->label() }}
                            </span>

                            <div class="mt-4 text-start">
                                <div class="mb-3">
                                    <strong><i class="fas fa-envelope me-2"></i>Email:</strong>
                                    <p class="mb-0">{{ $police->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-phone me-2"></i>Phone:</strong>
                                    <p class="mb-0">{{ $police->phone ?? 'Not provided' }}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-calendar me-2"></i>Registered:</strong>
                                    <p class="mb-0">{{ $police->created_at->format('F j, Y') }}</p>
                                </div>

                                <div class="mb-3">
                                    <strong><i class="fas fa-map me-2"></i>Address:</strong>
                                    <p class="mb-0">{{ $police->barangay->name ?? ''}}, {{ $police->municipality->name ?? '' }}, Cagayan</p>
                                </div>

                                {{-- <div class="mb-3">
                                    <strong><i class="fas fa-clock me-2"></i>Last Login:</strong>
                                    <p class="mb-0">{{ $police->last_login_at ? $police->last_login_at->format('F j, Y g:i A') : 'Never' }}</p>
                                </div> --}}

                                <div class="mb-3 id-card-preview">
                                    @if($police->id_card_path)
                                        <a href="{{ route('users.id-card', $police) }}" target="_blank" rel="noopener">
                                            <img src="{{ route('users.id-card', $police) }}"
                                                alt="ID Card"
                                                class="border rounded id-card-image img-fluid"
                                                style="max-height: 200px; cursor: pointer;">
                                        </a>
                                    @else
                                        <p class="mb-0 text-muted">No ID card on file.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Statistics -->
                <div class="col-lg-8">
                    <!-- Report Statistics -->
                    <div class="mb-4 row">
                        <div class="col-md-4">
                            <div class="text-white card bg-primary">
                                <div class="text-center card-body">
                                    <h3 class="mb-0">{{ $police->total_reports }}</h3>
                                    <p class="mb-0">Total Assigned Reports</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-white card bg-warning">
                                <div class="text-center card-body">
                                    <h3 class="mb-0">{{ $police->in_progress_reports }}</h3>
                                    <p class="mb-0">In Progress</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-white card bg-success">
                                <div class="text-center card-body">
                                    <h3 class="mb-0">{{ $police->resolved_reports }}</h3>
                                    <p class="mb-0">Resolved</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Reports -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 card-title">Recent Incident Reports</h5>
                            <a href="{{ route('admin.incidents') }}?search={{ $police->email }}" class="btn btn-sm btn-outline-primary">
                                View All Reports
                            </a>
                        </div>
                        <div class="card-body">
                            @if($recentIncidents->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Reference No.</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Date Reported</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentIncidents as $incident)
                                            <tr>
                                                <td>{{ $incident->reference_number }}</td>
                                                <td>{{ $incident->incident_type?->value }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($incident->status?->value === 'Resolved') bg-success
                                                        @elseif($incident->status?->value === 'In Progress') bg-primary
                                                        @elseif($incident->status?->value === 'Rejected') bg-danger
                                                        @else bg-secondary @endif">
                                                        {{ $incident->status?->value }}
                                                    </span>
                                                </td>
                                                <td>{{ $incident->created_at->format('M j, Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.incidents') }}?search={{ $incident->reference_number }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="py-4 text-center">
                                    <i class="mb-3 fas fa-clipboard-list fa-2x text-muted"></i>
                                    <p class="mb-0 text-muted">No incident reports found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .avatar {
            font-weight: 600;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
@endsection