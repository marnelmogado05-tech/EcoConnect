@extends('layouts.app')

@section('title', 'Dashboard - EcoConnect')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="mb-4 row">
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
    </div>

    <!-- Average Resolution Time -->
    <div class="mb-4">
        <div class="text-white card bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $avgResolutionTime }}</h4>
                        <p class="mb-0">Avg Resolution (Days)</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 card-title">Recent Activity</h5>
            <a href="{{ route('police.incidents') }}" class="btn btn-outline-primary btn-sm">View All</a>
        </div>

        <div class="p-0 card-body">
            @if($recentIncidents->count() > 0)
                <div class="table-responsive">
                    <table class="table mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Report ID</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentIncidents as $incident)
                            <tr>
                                <td class="fw-semibold">#{{ $incident->reference_number }}</td>
                                <td>
                                    @php
                                        $incidentTypes = [
                                            'Illegal Logging' => 'Illegal Logging',
                                            'Pollution' => 'Pollution',
                                            'Wildlife Crime' => 'Wildlife Crime',
                                            'Illegal Waste Disposal' => 'Illegal Waste Disposal',
                                            'Other' => 'Other'
                                        ];
                                    @endphp
                                    {{ $incidentTypes[$incident->incident_type] ?? $incident->incident_type }}
                                </td>
                                <td>
                                    @if($incident->mediaEvidence->count() > 0)
                                        @php
                                            $images = $incident->mediaEvidence->filter(function($media) {
                                                return str_starts_with($media->mime_type, 'image/');
                                            });
                                            $videos = $incident->mediaEvidence->filter(function($media) {
                                                return str_starts_with($media->mime_type, 'video/');
                                            });
                                        @endphp
                                        @if($images->count() > 0)
                                            @foreach ($images as $image)
                                                @if($image->latitude && $image->longitude)
                                                    <small class="text-muted"> {{ $image->address }}</small>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            @endforeach
                                        @endif
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
                                    <span class="badge {{ $statusClasses[$incident->status] ?? 'bg-secondary' }}">
                                        {{ $incident->status }}
                                    </span>
                                </td>
                                <td>{{ $incident->created_at->format('M j, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-4 text-center">
                    <i class="mb-3 fas fa-clipboard-list fa-3x text-muted"></i>
                    <p class="mb-0 text-muted">No incidents matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mt-4 row g-4">
        <!-- Incidents by Type - Pie Chart -->
        <div class="col-lg-6">
            <div class="shadow-sm card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 card-title fw-semibold">Incidents by Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="incidentsByTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Incidents by Status - Doughnut Chart -->
        <div class="col-lg-6">
            <div class="shadow-sm card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 card-title fw-semibold">Incidents by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="incidentsByStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends - Line Chart -->
    <div class="mt-4 shadow-sm card">
        <div class="card-header bg-light">
            <h5 class="mb-0 card-title fw-semibold">Monthly Incident Trends (Last 12 Months)</h5>
        </div>
        <div class="card-body">
            <canvas id="monthlyTrendsChart" height="100"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 card-title">Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <a href="{{ route('police.incidents') }}" class="py-3 btn btn-outline-primary w-100 h-100">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <i class="mb-2 fas fa-search fa-2x"></i>
                            <span>View Incident Reports</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('profile.edit') }}" class="py-3 btn btn-outline-secondary w-100 h-100">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <i class="mb-2 fas fa-user-edit fa-2x"></i>
                            <span>Update Profile</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data for charts
    const incidentsByType = @json($incidentsByType);
    const incidentsByStatus = @json($incidentsByStatus);
    const monthlyIncidents = @json($monthlyIncidents);
    const monthlyLabels = @json(array_keys($monthlyIncidents));

    // Incidents by Type - Pie Chart
    const typeCtx = document.getElementById('incidentsByTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(incidentsByType),
            datasets: [{
                data: Object.values(incidentsByType),
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Incidents by Status - Doughnut Chart
    const statusCtx = document.getElementById('incidentsByStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(incidentsByStatus),
            datasets: [{
                data: Object.values(incidentsByStatus),
                backgroundColor: ['#FFCE56', '#36A2EB', '#28a745']  // Pending yellow, In Progress blue, Resolved green
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Monthly Trends - Line Chart
    const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Incidents',
                data: Object.values(monthlyIncidents),
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
