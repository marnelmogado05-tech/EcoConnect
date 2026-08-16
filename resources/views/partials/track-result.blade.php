{{--
    Public incident status result.

    This page needs no authentication — anyone holding a reference number reaches it.
    The version this replaced rendered the reporter's full record, the assigned officer's
    name and email address, and every uploaded evidence file, all to an anonymous
    visitor. On a platform for reporting environmental crime, that exposes both the
    person who filed the report and the officer handling it.

    $incident is a plain array built by TrackIncidentController::publicView(), not an
    Eloquent model, so there is no relation here that could be walked back to a person.
--}}
@php
    $statusBadgeClasses = [
        'Pending' => 'bg-secondary',
        'Assigned' => 'bg-primary',
        'In Progress' => 'bg-primary',
        'Resolved' => 'bg-success',
        'Rejected' => 'bg-danger',
    ];
@endphp

<div class="modal fade" id="incidentModal" tabindex="-1" aria-labelledby="incidentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-denr-green" id="incidentModalLabel">
                    <i class="fas fa-clipboard-list me-2"></i>Incident Status - {{ $incident['reference_number'] }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="mb-4 row">
                    <div class="col-md-6">
                        <h6 class="mb-1 text-denr-green">Reference Number</h6>
                        <p class="mb-3 font-monospace">{{ $incident['reference_number'] }}</p>

                        <h6 class="mb-1 text-denr-green">Type</h6>
                        <p class="mb-3">{{ $incident['incident_type'] }}</p>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-1 text-denr-green">Status</h6>
                        <p class="mb-3">
                            <span class="badge {{ $statusBadgeClasses[$incident['status']] ?? 'bg-secondary' }}">
                                {{ $incident['status'] }}
                            </span>
                        </p>

                        <h6 class="mb-1 text-denr-green">Date Reported</h6>
                        <p class="mb-3">{{ $incident['reported_at'] ?? '—' }}</p>
                    </div>
                </div>

                @if($incident['status'] === 'Resolved' && $incident['resolution_details'])
                    <div class="mb-4">
                        <h6 class="mb-1 text-denr-green">Resolution</h6>
                        <div class="p-3 border rounded bg-light">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <span>{{ $incident['resolution_details'] }}</span>
                            @if($incident['resolved_date'])
                                <div class="mt-2 small text-muted">Resolved on {{ $incident['resolved_date'] }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($incident['status'] === 'Rejected' && $incident['rejection_reason'])
                    <div class="mb-4">
                        <h6 class="mb-1 text-denr-green">Reason</h6>
                        <div class="p-3 border rounded bg-light">
                            <i class="fas fa-times-circle me-2 text-danger"></i>
                            <span>{{ $incident['rejection_reason'] }}</span>
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <h6 class="mb-3 text-denr-green fw-semibold">Updates</h6>

                    @forelse($incident['followups'] as $followup)
                        <div class="mb-3 card border-left-denr">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="badge bg-light text-dark">{{ $followup['follow_up_type'] }}</span>
                                    <small class="text-muted">{{ $followup['created_at'] }}</small>
                                </div>
                                <p class="mt-2 mb-0">{{ $followup['follow_up_text'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No updates have been posted yet.</p>
                    @endforelse
                </div>

                <p class="mt-4 mb-0 small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Report details, evidence, and the contact information of assigned personnel
                    are only available to signed-in account holders.
                </p>
            </div>

            <div class="modal-footer">
                <a href="{{ route('track.index') }}" class="btn btn-denr-green">Search Again</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('incidentModal')).show();
    });
</script>
