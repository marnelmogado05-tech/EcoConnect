@extends('layouts.app')
@section('title', 'Citizen Details | EcoConnect - DENR CENRO Sanchez Mira')
@section('content')
    <section id="citizen-details" class="py-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Citizen Details</h2>
                            <p class="mb-0 text-muted">{{ $citizen->fname }} {{ $citizen->lname }}</p>
                        </div>
                        <div class="gap-2 d-flex">
                            <a href="{{ route('admin.citizens') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Citizens
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Citizen Information -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0 card-title">Profile Information</h5>
                        </div>
                        <div class="text-center card-body">
                            <div class="mx-auto mb-3 text-white avatar bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 1.5rem;">
                                {{ strtoupper(substr($citizen->fname, 0, 1)) }}{{ strtoupper(substr($citizen->lname, 0, 1)) }}
                            </div>
                            <h4>{{ $citizen->fname }} {{ $citizen->lname }}</h4>
                            <span class="badge {{ $citizen->status == 'Active' ? 'bg-success' : 'bg-danger' }} mb-3">
                                {{ ucfirst($citizen->status) }}
                            </span>

                            <div class="mt-4 text-start">
                                <div class="mb-3">
                                    <strong><i class="fas fa-envelope me-2"></i>Email:</strong>
                                    <p class="mb-0">{{ $citizen->email }}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-phone me-2"></i>Phone:</strong>
                                    <p class="mb-0">{{ $citizen->phone ?? 'Not provided' }}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-calendar me-2"></i>Registered:</strong>
                                    <p class="mb-0">{{ $citizen->created_at->format('F j, Y') }}</p>
                                </div>

                                {{-- <div class="mb-3">
                                    <strong><i class="fas fa-map me-2"></i>Address:</strong>
                                    <p class="mb-0">{{ $citizen->barangay->name }}, {{ $citizen->municipality->name }}, Cagayan</p>
                                </div> --}}

                                {{-- <div class="mb-3">
                                    <strong><i class="fas fa-clock me-2"></i>Last Login:</strong>
                                    <p class="mb-0">{{ $citizen->last_login_at ? $citizen->last_login_at->format('F j, Y g:i A') : 'Never' }}</p>
                                </div> --}}

                                <div class="mb-3 id-card-preview">
                                    @if($citizen->id_card_path)
                                        <a href="{{ route('users.id-card', $citizen) }}" target="_blank" rel="noopener">
                                            <img src="{{ route('users.id-card', $citizen) }}"
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

                    <!-- Quick Actions -->
                    <div class="mt-4 card">
                        <div class="card-header">
                            <h5 class="mb-0 card-title">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="gap-2 d-grid">
                                <button class="btn btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#statusModal">
                                    <i class="fas fa-user-cog me-2"></i>Change Status
                                </button>
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
                                    <h3 class="mb-0">{{ $citizen->total_reports }}</h3>
                                    <p class="mb-0">Total Reports</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-white card bg-warning">
                                <div class="text-center card-body">
                                    <h3 class="mb-0">{{ $citizen->pending_reports }}</h3>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-white card bg-success">
                                <div class="text-center card-body">
                                    <h3 class="mb-0">{{ $citizen->resolved_reports }}</h3>
                                    <p class="mb-0">Resolved</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Reports -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 card-title">Recent Incident Reports</h5>
                            <a href="{{ route('admin.incidents') }}?search={{ $citizen->email }}" class="btn btn-sm btn-outline-primary">
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
                                                <td>{{ $incident->incident_type }}</td>
                                                <td>
                                                    <span class="badge
                                                        @if($incident->status == 'Resolved') bg-success
                                                        @elseif($incident->status == 'In Progress') bg-primary
                                                        @elseif($incident->status == 'Rejected') bg-danger
                                                        @else bg-secondary @endif">
                                                        {{ $incident->status }}
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

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Status - {{ $citizen->fname }} {{ $citizen->lname }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.citizens.update-status', $citizen->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">Account Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Active" {{ $citizen->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Suspended" {{ $citizen->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                Suspended citizens cannot submit new incident reports.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Reset Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password - {{ $citizen->fname }} {{ $citizen->lname }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.citizens.reset-password', $citizen->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle"></i>
                                The citizen will need to use this new password to login.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .avatar {
            font-weight: 600;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
@endsection
