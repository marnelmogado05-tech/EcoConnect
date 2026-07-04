@extends('layouts.app')
@section('title', 'Manage Citizens | EcoConnect - DENR CENRO Sanchez Mira')
@section('page-title', 'Manage Citizens')
@section('content')
    <section id="citizens" class="py-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Citizens Management</h2>
                            <p class="mb-0 text-muted">Manage all registered citizens and their reports</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="mb-4 row">
                <div class="col-xl-3 col-md-6">
                    <div class="text-white card bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0">Total Citizens</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="text-white card bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['active'] }}</h4>
                                    <p class="mb-0">Active Citizens</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-user-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="text-white card bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['suspended'] }}</h4>
                                    <p class="mb-0">Suspended</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-user-slash fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="text-white card bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['reports'] }}</h4>
                                    <p class="mb-0">Total Reports</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-clipboard-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="mb-4 card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.citizens') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request()->hasAny(['status', 'search']))
                                    <a href="{{ route('admin.citizens') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <a href="{{ route('admin.citizens') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Citizens Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title">All Citizens</h5>
                    <div class="gap-2 d-flex">
                        <span class="text-muted">
                            Showing {{ $citizens->firstItem() }} to {{ $citizens->lastItem() }} of {{ $citizens->total() }} citizens
                        </span>
                    </div>
                </div>
                <div class="p-0 card-body">
                    @if($citizens->count() > 0)
                        <div class="table-responsive">
                            <table class="table mb-0 table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="200">Citizen</th>
                                        <th width="150">Contact Info</th>
                                        <th width="120">Reports</th>
                                        <th width="100">Pending</th>
                                        <th width="100">Resolved</th>
                                        <th width="100">Status</th>
                                        <th width="150">Registered</th>
                                        <th width="120" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($citizens as $citizen)
                                    <tr class="align-middle">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="text-white avatar bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($citizen->fname, 0, 1)) }}{{ strtoupper(substr($citizen->lname, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $citizen->lname }}, {{ $citizen->fname }} {{ substr($citizen->mname, 0, 1) ?? '' }} {{ $citizen->extname ?? '' }}</div>
                                                    <small class="text-muted">ID: {{ $citizen->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $citizen->email }}</div>
                                            <small class="text-muted">{{ $citizen->phone ?? 'No phone' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">
                                                {{ $citizen->total_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning rounded-pill">
                                                {{ $citizen->pending_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success rounded-pill">
                                                {{ $citizen->resolved_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $citizen->status == 'Active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($citizen->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $citizen->created_at->format('M j, Y') }}</div>
                                            <small class="text-muted">{{ $citizen->created_at->format('g:i A') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.citizens.show', $citizen->id) }}" 
                                                   class="btn btn-outline-primary" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#statusModal{{ $citizen->id }}"
                                                        title="Change Status">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Status Change Modal -->
                                    <div class="modal fade" id="statusModal{{ $citizen->id }}" tabindex="-1">
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
                                    <div class="modal fade" id="passwordModal{{ $citizen->id }}" tabindex="-1">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $citizens->firstItem() }} to {{ $citizens->lastItem() }} of {{ $citizens->total() }} entries
                            </div>
                            <div>
                                {{ $citizens->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        <div class="py-5 text-center">
                            <i class="mb-3 fas fa-users fa-3x text-muted"></i>
                            <h4 class="text-muted">No Citizens Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'search']))
                                    No citizens match your search criteria.
                                @else
                                    No citizens are registered yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

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

        .avatar {
            font-weight: 600;
            font-size: 0.875rem;
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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const statusSelect = filterForm.querySelector('select[name="status"]');
            
            statusSelect.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    </script>
@endsection