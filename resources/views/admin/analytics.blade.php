@extends('layouts.app')

@section('title', 'Analytics - EcoConnect')
@section('page-title', 'Analytics Dashboard')

@section('content')
    <!-- Date Range Filter -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Analytics Dashboard</h4>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.analytics', ['period' => 'all']) }}" class="btn btn-outline-primary {{ $currentPeriod == 'all' ? 'active' : '' }}">All Time</a>
                <a href="{{ route('admin.analytics', ['period' => '7d']) }}" class="btn btn-outline-primary {{ $currentPeriod == '7d' ? 'active' : '' }}">7 Days</a>
                <a href="{{ route('admin.analytics', ['period' => '30d']) }}" class="btn btn-outline-primary {{ $currentPeriod == '30d' ? 'active' : '' }}">30 Days</a>
                <a href="{{ route('admin.analytics', ['period' => '90d']) }}" class="btn btn-outline-primary {{ $currentPeriod == '90d' ? 'active' : '' }}">90 Days</a>
            </div>
        </div>
        <p class="mt-2 text-muted">Showing data for: <strong>{{ $periodLabels[$currentPeriod] }}</strong></p>
    </div>

    <!-- Overview Statistics -->
    <div class="mb-4 row g-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.incidents') }}" class="text-white text-decoration-none card bg-primary h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $incidentStats['total'] }}</h3>
                            <p class="mb-0 opacity-75 small">Total Incidents</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.incidents') }}?status=Pending" class="text-white text-decoration-none card bg-warning h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $incidentStats['pending'] }}</h3>
                            <p class="mb-0 opacity-75 small">Pending</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.incidents') }}?status=In+Progress" class="text-white text-decoration-none card bg-info h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $incidentStats['in_progress'] }}</h3>
                            <p class="mb-0 opacity-75 small">In Progress</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-tasks fa-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.incidents') }}?status=Resolved" class="text-white text-decoration-none card bg-success h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $incidentStats['resolved'] }}</h3>
                            <p class="mb-0 opacity-75 small">Resolved</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="mb-4 row g-3">
        <div class="col-4">
            <a href="{{ route('admin.citizens') }}" class="text-white text-decoration-none card bg-secondary h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $userStats['total_users'] }}</h3>
                            <p class="mb-0 opacity-75 small">Citizens</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-4">
            <a href="{{ route('admin.police') }}" class="text-white text-decoration-none card bg-dark h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ $userStats['total_police'] }}</h3>
                            <p class="mb-0 opacity-75 small">Police Officers</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt fa-lg"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-4">
            <div class="text-white card bg-danger h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h3 class="mb-1 fw-bold">{{ number_format($avgResolutionTime, 1) }}</h3>
                            <p class="mb-0 opacity-75 small">Avg Resolution (Days)</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4">
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

    <!-- Incidents by Municipality - Bar Chart -->
    <div class="mt-4 shadow-sm card">
        <div class="card-header bg-light">
            <h5 class="mb-0 card-title fw-semibold">Incidents by Municipality</h5>
        </div>
        <div class="card-body">
            <canvas id="incidentsByMunicipalityChart" height="100"></canvas>
        </div>
    </div>

    <!-- Monthly Trends - Line Chart -->
    <div class="mt-4 shadow-sm card">
        <div class="card-header bg-light">
            <h5 class="mb-0 card-title fw-semibold">Monthly Incident & User Trends (Last 12 Months)</h5>
        </div>
        <div class="card-body">
            <canvas id="monthlyTrendsChart" height="100"></canvas>
        </div>
    </div>

    <!-- Police Performance -->
    <div class="mt-4 shadow-sm card">
        <div class="card-header bg-light">
            <h5 class="mb-0 card-title fw-semibold">Police Performance (Top 10)</h5>
        </div>
        <div class="card-body">
            @if($policePerformance->count() > 0)
                <div class="table-responsive">
                    <table class="table mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Officer</th>
                                <th class="fw-semibold">Municipality</th>
                                <th class="fw-semibold">Resolved</th>
                                <th class="fw-semibold">Total Assigned</th>
                                <th class="fw-semibold">Efficiency (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policePerformance as $officer)
                                <tr>
                                    <td class="fw-semibold">{{ $officer['name'] }}</td>
                                    <td class="fw-semibold">{{ $officer['municipality'] }}</td>
                                    <td>{{ $officer['resolved'] }}</td>
                                    <td>{{ $officer['total'] }}</td>
                                    <td>
                                        <span class="badge {{ $officer['efficiency'] >= 80 ? 'bg-success' : ($officer['efficiency'] >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $officer['efficiency'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <canvas id="policePerformanceChart" height="100"></canvas>
                </div>
            @else
                <p class="mb-0 text-muted">No data available</p>
            @endif
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
    const incidentsByMunicipality = @json($incidentsByMunicipality);
    const monthlyIncidents = @json($monthlyIncidents->pluck('count', 'month')->toArray());
    const monthlyUsers = @json($monthlyUsers->pluck('count', 'month')->toArray());
    const monthlyLabels = @json($monthlyIncidents->pluck('month')->toArray());
    const policePerformance = @json($policePerformance->pluck('efficiency', 'name')->toArray());

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
                backgroundColor: ['#FFCE56', '#4BC0C0', '#28a745', '#FF6666FF']  // Pending yellow, In Progress blue, Resolved green, Rejected purple
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
            }, {
                label: 'New Users',
                data: Object.values(monthlyUsers),
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
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

    // Incidents by Municipality - Bar Chart
    const municipalityCtx = document.getElementById('incidentsByMunicipalityChart').getContext('2d');

    // Sort municipalities by incident count (descending) and take top 10
    const sortedMunicipalities = Object.entries(incidentsByMunicipality)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 10);

    const municipalityLabels = sortedMunicipalities.map(item => item[0]);
    const municipalityData = sortedMunicipalities.map(item => item[1]);

    new Chart(municipalityCtx, {
        type: 'bar',
        data: {
            labels: municipalityLabels,
            datasets: [{
                label: 'Number of Incidents',
                data: municipalityData,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Incidents'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Municipalities'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Incidents: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    });

    // Police Performance - Bar Chart
    const policeCtx = document.getElementById('policePerformanceChart').getContext('2d');
    new Chart(policeCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(policePerformance),
            datasets: [{
                label: 'Efficiency (%)',
                data: Object.values(policePerformance),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
});
</script>
@endpush
