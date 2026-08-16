@extends('layouts.app')
@section('title', 'Manage BFP Officers | EcoConnect - DENR CENRO Sanchez Mira')
@section('page-title', 'BFP Officers Management')
@section('content')
    <section id="policeOfficers" class="py-4">
        <div class="container-fluid">
            <!-- Header -->
            <div class="mb-4 row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">BFP Officers Management</h2>
                            <p class="mb-0 text-muted">Manage all registered BFP Officers and their reports</p>
                        </div>
                        <!-- Add Police Officer Button -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBfpOfficerModal">
                            <i class="fas fa-plus me-2"></i> Add BFP Officer
                        </button>
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
                                    <p class="mb-0">Total BFP Officers</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-users fa-2x"></i>
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
                                    <h4 class="mb-0">{{ $stats['assigned_reports'] }}</h4>
                                    <p class="mb-0">Total Assigned Reports</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-clipboard-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="text-white card bg-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $stats['not_yet_responded_reports'] }}</h4>
                                    <p class="mb-0">Not Yet Responded</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-hourglass-start fa-2x"></i>
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
                                    <h4 class="mb-0">{{ $stats['in_progress_reports'] }}</h4>
                                    <p class="mb-0">In Progress Reports</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-tasks fa-2x"></i>
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
                                    <h4 class="mb-0">{{ $stats['resolved_reports'] }}</h4>
                                    <p class="mb-0">Resolved Reports</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="opacity-50 fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="mb-4 card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.bfp') }}" id="filterForm">
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
                                    <a href="{{ route('admin.bfp') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Police Officers Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title">All Police Officers</h5>
                    <div class="gap-2 d-flex">
                        <span class="text-muted">
                            Showing {{ $bfp->firstItem() }} to {{ $bfp->lastItem() }} of {{ $bfp->total() }} bfpOfficers
                        </span>
                    </div>
                </div>
                <div class="p-0 card-body">
                    @if($bfp->count() > 0)
                        <div class="table-responsive">
                            <!-- Police Table -->
                            <table class="table mb-0 table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="200">BFP Officer</th>
                                        <th width="150">Contact Info</th>
                                        <th width="100">Municipality</th>
                                        <th width="80">Total</th>
                                        <th width="80">Not Yet Responded</th>
                                        <th width="80">In Progress</th>
                                        <th width="80">Resolved</th>
                                        <th width="100">Status</th>
                                        <th width="120" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bfp as $officer)
                                    <tr class="align-middle">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="text-white avatar bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($officer->fname, 0, 1)) }}{{ strtoupper(substr($officer->lname, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $officer->lname }}, {{ $officer->fname }} {{ substr($officer->mname, 0, 1) ?? ''}} {{ $officer->extname ?? ''}}</div>
                                                    <small class="text-muted">ID: {{ $officer->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $officer->email }}</div>
                                            <small class="text-muted">{{ $officer->phone ?? 'No phone' }}</small>
                                        </td>
                                        <td>
                                            {{ $officer->municipality->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">
                                                {{ $officer->total_assigned_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                {{ $officer->not_yet_responded_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning rounded-pill">
                                                {{ $officer->in_progress_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success rounded-pill">
                                                {{ $officer->resolved_reports }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $officer->status == 'Active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($officer->status) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.bfp.show', $officer->id) }}"
                                                class="btn btn-outline-primary"
                                                title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <!-- Edit BFP Officer Button (in your table actions) -->
                                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editBfpOfficerModal{{ $officer->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-warning"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#statusModal{{ $officer->id }}"
                                                        title="Change Status">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Status Change Modal -->
                                    <div class="modal fade" id="statusModal{{ $officer->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Change Status - {{ $officer->fname }} {{ $officer->lname }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.bfp.update-status', $officer->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Account Status</label>
                                                            <select class="form-select" id="status" name="status" required>
                                                                <option value="Active" {{ $officer->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                                <option value="Suspended" {{ $officer->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                                            </select>
                                                        </div>
                                                        <div class="alert alert-info">
                                                            <small>
                                                                <i class="fas fa-info-circle"></i>
                                                                Suspended officers cannot be assigned to incident reports.
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

                                    <!-- Edit Police Officer Modal -->
                                    <div class="modal fade" id="editBfpOfficerModal{{ $officer->id }}" tabindex="-1" aria-labelledby="editBfpOfficerModalLabel{{ $officer->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editBfpOfficerModalLabel{{ $officer->id }}">
                                                        <i class="fas fa-edit me-2"></i>Edit BFP Officer
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('admin.bfp.update-profile', $officer->id) }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <!-- Name Fields -->
                                                        <div class="mb-3 row">
                                                            <div class="col-md-6">
                                                                <label for="edit_fname_{{ $officer->id }}" class="form-label">First Name *</label>
                                                                <input type="text" class="form-control" id="edit_fname_{{ $officer->id }}" name="fname" value="{{ old('fname', $officer->fname) }}" required>
                                                                @error('fname')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="edit_mname_{{ $officer->id }}" class="form-label">Middle Name</label>
                                                                <input type="text" class="form-control" id="edit_mname_{{ $officer->id }}" name="mname" value="{{ old('mname', $officer->mname) }}">
                                                                @error('mname')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="mb-3 row">
                                                            <div class="col-md-6">
                                                                <label for="edit_lname_{{ $officer->id }}" class="form-label">Last Name *</label>
                                                                <input type="text" class="form-control" id="edit_lname_{{ $officer->id }}" name="lname" value="{{ old('lname', $officer->lname) }}" required>
                                                                @error('lname')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="edit_extname_{{ $officer->id }}" class="form-label">Suffix</label>
                                                                <select class="form-select" id="edit_extname_{{ $officer->id }}" name="extname">
                                                                    <option value="">None</option>
                                                                    <option value="Jr." {{ old('extname', $officer->extname) == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                                                    <option value="Sr." {{ old('extname', $officer->extname) == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                                                    <option value="II" {{ old('extname', $officer->extname) == 'II' ? 'selected' : '' }}>II</option>
                                                                    <option value="III" {{ old('extname', $officer->extname) == 'III' ? 'selected' : '' }}>III</option>
                                                                    <option value="IV" {{ old('extname', $officer->extname) == 'IV' ? 'selected' : '' }}>IV</option>
                                                                </select>
                                                                @error('extname')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <!-- Contact Information -->
                                                        <div class="mb-3 row">
                                                            <div class="col-md-6">
                                                                <label for="phone" class="form-label">Phone Number *</label>
                                                                <input type="tel" class="form-control phone-input" id="phone" name="phone" value="{{ old('phone', $officer->phone) }}" required placeholder="09XX-XXX-XXXX">
                                                                @error('phone')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="edit_email_{{ $officer->id }}" class="form-label">Email Address *</label>
                                                                <input type="email" class="form-control" id="edit_email_{{ $officer->id }}" name="email" value="{{ old('email', $officer->email) }}" required>
                                                                @error('email')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <!-- Location Fields -->
                                                        <div class="mb-3 row">
                                                            <div class="col-md-6">
                                                                <label for="edit_municipality_id_{{ $officer->id }}" class="form-label">Municipality *</label>
                                                                <select class="form-select" id="edit_municipality_id_{{ $officer->id }}" name="municipality_id" required>
                                                                    <option value="">Select Municipality</option>
                                                                    @foreach($municipalities as $municipality)
                                                                        <option value="{{ $municipality->id }}"
                                                                            {{ $officer->municipality_id == $municipality->id ? 'selected' : '' }}>
                                                                            {{ $municipality->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('municipality_id')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <!-- Status Field -->
                                                        <div class="mb-3 row">
                                                            <div class="col-md-6">
                                                                <label for="edit_status_{{ $officer->id }}" class="form-label">Status *</label>
                                                                <select class="form-select" id="edit_status_{{ $officer->id }}" name="status" required>
                                                                    <option value="active" {{ old('status', $officer->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="suspended" {{ old('status', $officer->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                                </select>
                                                                @error('status')
                                                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <!-- ID Card Upload (Optional for update) -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Update ID Card (Optional)</label>

                                                            <!-- Current ID Card Preview -->
                                                            @if($officer->id_card_path)
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted">Current ID Card:</label>
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ route('users.id-card', $officer) }}" alt="Current ID Card" class="rounded me-3" style="max-width: 100px; max-height: 80px;">
                                                                    <span class="text-muted small">Current ID card</span>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            <div class="p-4 text-center border-2 border-dashed rounded file-upload-box" id="editFileUploadBox{{ $officer->id }}">
                                                                <div class="mb-2 file-upload-icon">📷</div>
                                                                <div class="mb-3 file-upload-text">
                                                                    <h6 class="mb-1">Upload New ID Card</h6>
                                                                    <p class="mb-0 text-muted">JPEG, JPG, or PNG (Max: 5MB)</p>
                                                                </div>
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('edit_id_card_{{ $officer->id }}').click()">
                                                                    Choose File
                                                                </button>
                                                                <input type="file" id="edit_id_card_{{ $officer->id }}" name="id_card" class="d-none" accept=".jpeg,.jpg,.png">
                                                            </div>
                                                            <div class="mt-2 file-preview" id="editFilePreview{{ $officer->id }}" style="display: none;">
                                                                <div class="d-flex align-items-center">
                                                                    <img id="editPreviewImage{{ $officer->id }}" src="" alt="New ID Card Preview" class="rounded me-3" style="max-width: 80px; max-height: 60px;">
                                                                    <div class="file-info">
                                                                        <span id="editFileName{{ $officer->id }}" class="d-block"></span>
                                                                        <button type="button" class="mt-1 btn btn-sm btn-outline-danger" onclick="removeEditFile({{ $officer->id }})">
                                                                            Remove
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @error('id_card')
                                                                <div class="mt-1 text-danger small">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save me-2"></i>Update BFP Officer
                                                        </button>
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
                                Showing {{ $bfp->firstItem() }} to {{ $bfp->lastItem() }} of {{ $bfp->total() }} entries
                            </div>
                            <div>
                                {{ $bfp->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        <div class="py-5 text-center">
                            <i class="mb-3 fas fa-users fa-3x text-muted"></i>
                            <h4 class="text-muted">No BFP Officers Found</h4>
                            <p class="text-muted">
                                @if(request()->hasAny(['status', 'search']))
                                    No BFP Officers match your search criteria.
                                @else
                                    No BFP Officers are registered yet.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Add BFP Officer Modal -->
    <div class="modal fade" id="addBfpOfficerModal" tabindex="-1" aria-labelledby="addBfpOfficerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBfpOfficerModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Add BFP Officer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.bfp.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <!-- Name Fields -->
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="fname" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="fname" name="fname" value="{{ old('fname') }}" required autofocus>
                                @error('fname')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="mname" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="mname" name="mname" value="{{ old('mname') }}">
                                @error('mname')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="lname" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="lname" name="lname" value="{{ old('lname') }}" required>
                                @error('lname')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="extname" class="form-label">Suffix</label>
                                <select class="form-select" id="extname" name="extname">
                                    <option value="">None</option>
                                    <option value="Jr." {{ old('extname') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                    <option value="Sr." {{ old('extname') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                    <option value="II" {{ old('extname') == 'II' ? 'selected' : '' }}>II</option>
                                    <option value="III" {{ old('extname') == 'III' ? 'selected' : '' }}>III</option>
                                    <option value="IV" {{ old('extname') == 'IV' ? 'selected' : '' }}>IV</option>
                                </select>
                                @error('extname')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control phone-input" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="09XX-XXX-XXXX">
                                @error('phone')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location Fields -->
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="municipality_id" class="form-label">Municipality *</label>
                                <select class="form-select" id="municipality_id" name="municipality_id" required>
                                    <option value="">Select Municipality</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality->id }}" {{ old('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                            {{ $municipality->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('municipality_id')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- ID Card Upload -->
                        <div class="mb-3">
                            <label class="form-label">ID Card Upload *</label>
                            <div class="p-4 text-center border-2 border-dashed rounded file-upload-box" id="fileUploadBox">
                                <div class="mb-2 file-upload-icon">📷</div>
                                <div class="mb-3 file-upload-text">
                                    <h6 class="mb-1">Upload Police ID Card</h6>
                                    <p class="mb-0 text-muted">JPEG, JPG, or PNG (Max: 5MB)</p>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('id_card').click()">
                                    Choose File
                                </button>
                                <input type="file" id="id_card" name="id_card" class="d-none" accept=".jpeg,.jpg,.png" required>
                            </div>
                            <div class="mt-2 file-preview" id="filePreview" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <img id="previewImage" src="" alt="ID Card Preview" class="rounded me-3" style="max-width: 80px; max-height: 60px;">
                                    <div class="file-info">
                                        <span id="fileName" class="d-block"></span>
                                        <button type="button" class="mt-1 btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('id_card')
                                <div class="mt-1 text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Fields -->
                        <div class="mb-3 row">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                @error('password')
                                    <div class="mt-1 text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <!-- Role is automatically set to police -->
                        <input type="hidden" name="role" value="police">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Police Officer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

        .file-upload-box {
            border-color: #dee2e6;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-box:hover {
            border-color: var(--denr-green);
            background-color: #f0f9f0;
        }

        .file-upload-box.dragover {
            border-color: var(--denr-green);
            background-color: #e8f5e8;
        }

        .file-upload-icon {
            font-size: 2rem;
            color: var(--denr-green);
        }

        .file-preview img {
            border: 1px solid #dee2e6;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize phone formatting when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializePhoneFormatting();

            // Auto-submit form when filters change
            const filterForm = document.getElementById('filterForm');
            const statusSelect = filterForm.querySelector('select[name="status"]');

            statusSelect.addEventListener('change', function() {
                filterForm.submit();
            });

            // File upload functionality
            const fileInput = document.getElementById('id_card');
            const fileUploadBox = document.getElementById('fileUploadBox');
            const filePreview = document.getElementById('filePreview');
            const previewImage = document.getElementById('previewImage');
            const fileName = document.getElementById('fileName');

            // File input change event
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!validTypes.includes(file.type)) {
                        alert('Please upload only JPEG, JPG, or PNG files.');
                        this.value = '';
                        return;
                    }

                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB.');
                        this.value = '';
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        fileName.textContent = file.name;
                        filePreview.style.display = 'block';
                        fileUploadBox.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Drag and drop functionality
            fileUploadBox.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            fileUploadBox.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            fileUploadBox.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        });

        // Phone formatting function
        function initializePhoneFormatting() {
            const phoneInputs = document.querySelectorAll('.phone-input');

            phoneInputs.forEach(function(phoneInput) {
                // Only add event listener if not already added
                if (!phoneInput.hasAttribute('data-phone-formatted')) {
                    phoneInput.setAttribute('data-phone-formatted', 'true');

                    phoneInput.addEventListener('input', function(e) {
                        let digits = e.target.value.replace(/\D/g, '').slice(0, 11);
                        let formatted = digits;

                        if (digits.length > 0) {
                            formatted = digits;
                            if (digits.length > 3) {
                                formatted = digits.slice(0, 4) + '-' + digits.slice(4, 7);
                                if (digits.length > 7) {
                                    formatted += '-' + digits.slice(7, 11);
                                }
                            }
                        }

                        e.target.value = formatted;
                    });

                    // Format existing value on initialization
                    if (phoneInput.value) {
                        let digits = phoneInput.value.replace(/\D/g, '').slice(0, 11);
                        let formatted = digits;
                        if (digits.length > 0) {
                            formatted = digits;
                            if (digits.length > 3) {
                                formatted = digits.slice(0, 4) + '-' + digits.slice(4, 7);
                                if (digits.length > 7) {
                                    formatted += '-' + digits.slice(7, 11);
                                }
                            }
                        }
                        phoneInput.value = formatted;
                    }
                }
            });
        }

        // Remove file function
        function removeFile() {
            const fileInput = document.getElementById('id_card');
            const filePreview = document.getElementById('filePreview');
            const fileUploadBox = document.getElementById('fileUploadBox');

            fileInput.value = '';
            filePreview.style.display = 'none';
            fileUploadBox.style.display = 'block';
        }

        // Remove edit file function
        function removeEditFile(officerId) {
            const fileInput = document.getElementById('edit_id_card_' + officerId);
            const filePreview = document.getElementById('editFilePreview' + officerId);
            const fileUploadBox = document.getElementById('editFileUploadBox' + officerId);

            fileInput.value = '';
            filePreview.style.display = 'none';
            fileUploadBox.style.display = 'block';
        }

        // Initialize file upload for edit modals when they open
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for edit modals when they are shown
            document.querySelectorAll('[id^="editBfpOfficerModal"]').forEach(function(modal) {
                modal.addEventListener('shown.bs.modal', function() {
                    const officerId = this.id.replace('editBfpOfficerModal', '');
                    initializeEditFileUpload(officerId);
                });
            });
        });

        // Initialize file upload for specific edit modal
        function initializeEditFileUpload(officerId) {
            const fileInput = document.getElementById('edit_id_card_' + officerId);
            const fileUploadBox = document.getElementById('editFileUploadBox' + officerId);
            const filePreview = document.getElementById('editFilePreview' + officerId);
            const previewImage = document.getElementById('editPreviewImage' + officerId);
            const fileName = document.getElementById('editFileName' + officerId);

            if (fileInput && fileUploadBox) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (!validTypes.includes(file.type)) {
                            alert('Please upload only JPEG, JPG, or PNG files.');
                            this.value = '';
                            return;
                        }

                        // Validate file size (5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File size must be less than 5MB.');
                            this.value = '';
                            return;
                        }

                        // Show preview
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            fileName.textContent = file.name;
                            filePreview.style.display = 'block';
                            fileUploadBox.style.display = 'none';
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // Drag and drop for edit modals
                fileUploadBox.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                fileUploadBox.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                fileUploadBox.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });
            }
        }
    </script>
@endsection
