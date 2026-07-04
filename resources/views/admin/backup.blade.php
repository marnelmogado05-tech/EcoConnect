@extends('layouts.app')

@section('title', 'Database Backup - EcoConnect')
@section('page-title', 'Database Backup')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="shadow-sm card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 card-title fw-semibold">Database Backup Management</h5>
                    <form method="POST" action="{{ route('admin.backup.create') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>Create New Backup
                        </button>
                    </form>
                </div>

                <div class="card-body">
                    @if($backups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size</th>
                                        <th>Created At</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                    <tr>
                                        <td class="fw-semibold">{{ $backup['filename'] }}</td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($backup['date'])->format('M d, Y H:i') }}</div>
                                            <div class="text-muted small">{{ $backup['age'] }}</div>
                                        </td>
                                        <td>
                                            @if($backup['path'] === ($lastBackup['path'] ?? null))
                                                <span class="badge bg-success">Latest</span>
                                            @else
                                                <span class="badge bg-secondary">Archive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.backup.download', basename($backup['filename'])) }}"
                                                class="btn btn-sm btn-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>

                                                @if($backup['path'] !== ($lastBackup['path'] ?? null))
                                                <form method="POST" action="{{ route('admin.backup.delete', basename($backup['filename'])) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this backup?')"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                <button class="btn btn-sm btn-info"
                                                        onclick="showBackupInfo('{{ basename($backup['filename']) }}')"
                                                        title="View Details">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $backups->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        <div class="py-5 text-center">
                            <i class="mb-3 fas fa-database fa-3x text-muted"></i>
                            <h6 class="text-muted">No backup files found</h6>
                            <p class="text-muted small">Create your first backup to get started</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Info Modal -->
    <div class="modal fade" id="backupInfoModal" tabindex="-1" aria-labelledby="backupInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="backupInfoModalLabel">Backup Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="backupInfoContent">
                        <!-- Backup details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Information -->
    <div class="mt-4 row">
        <div class="col-12">
            <div class="shadow-sm card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 card-title fw-semibold">Backup Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>What gets backed up?</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Database (MySQL)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Application files</li>
                                <li><i class="fas fa-check text-success me-2"></i>Configuration files</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Backup Settings</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-info-circle text-info me-2"></i>Stored in: storage/app/Laravel</li>
                                <li><i class="fas fa-info-circle text-info me-2"></i>Format: ZIP archive</li>
                                <li><i class="fas fa-info-circle text-info me-2"></i>Compression: Enabled</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store backups data in JavaScript
        const backupsData = @json($backups);

        function showBackupInfo(filename) {
            // Find the backup by filename
            const backup = backupsData.find(b => b.filename === filename);

            if (!backup) {
                document.getElementById('backupInfoContent').innerHTML = '<p class="text-danger">Backup not found.</p>';
                new bootstrap.Modal(document.getElementById('backupInfoModal')).show();
                return;
            }

            const backupInfo = `
                <div class="backup-details">
                    <h6>Backup Information</h6>
                    <p><strong>Filename:</strong> ${backup.filename}</p>
                    <p><strong>Created:</strong> ${backup.date}</p>
                    <p><strong>Size:</strong> ${backup.size}</p>
                    <p><strong>Age:</strong> ${backup.age}</p>
                    <p><strong>Type:</strong> Database Backup</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Completed</span></p>
                    <p><strong>Path:</strong> ${backup.path}</p>
                </div>
            `;

            document.getElementById('backupInfoContent').innerHTML = backupInfo;
            new bootstrap.Modal(document.getElementById('backupInfoModal')).show();
        }
    </script>
@endsection
