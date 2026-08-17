<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\User;
use App\Services\LocationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Get date range filter
        $period = $request->get('period', 'all');
        $dateFrom = null;

        switch ($period) {
            case '7d':
                $dateFrom = Carbon::now()->subDays(7);
                break;
            case '30d':
                $dateFrom = Carbon::now()->subDays(30);
                break;
            case '90d':
                $dateFrom = Carbon::now()->subDays(90);
                break;
            default:
                $dateFrom = null; // All time
        }

        // Base query for incidents with date filter
        $incidentQuery = Incident::when($dateFrom, function ($query) use ($dateFrom) {
            return $query->where('created_at', '>=', $dateFrom);
        });

        // Incident statistics
        $incidentStats = [
            'total' => (clone $incidentQuery)->count(),
            'pending' => (clone $incidentQuery)->where('status', IncidentStatus::Pending)->count(),
            'in_progress' => (clone $incidentQuery)->where('status', IncidentStatus::InProgress)->count(),
            'resolved' => (clone $incidentQuery)->where('status', IncidentStatus::Resolved)->count(),
            'rejected' => (clone $incidentQuery)->where('status', IncidentStatus::Rejected)->count(),
        ];

        // Incidents by type
        $incidentsByType = (clone $incidentQuery)->selectRaw('incident_type, COUNT(*) as count')
            ->groupBy('incident_type')
            ->get()
            // Keyed by the stored value: incident_type is cast to an enum, and an
            // enum instance cannot be used as an array key.
            ->mapWithKeys(fn ($row) => [$row->incident_type->value => $row->count])
            ->toArray();

        // Incidents by status - include all statuses even if 0
        // Keyed by the stored value: an enum instance cannot be an array key.
        $incidentsByStatus = [];
        foreach (IncidentStatus::cases() as $status) {
            $incidentsByStatus[$status->value] = (clone $incidentQuery)
                ->where('status', $status)
                ->count();
        }

        // Monthly incidents for the last 12 months (or filtered period if shorter)
        $monthlyStartDate = $dateFrom ?: Carbon::now()->subMonths(12);
        $monthlyIncidents = Incident::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', $monthlyStartDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create($item->year, $item->month)->format('M Y'),
                    'count' => $item->count
                ];
            });

        // User statistics (filtered by date if applicable)
        $userQuery = User::when($dateFrom, function ($query) use ($dateFrom) {
            return $query->where('created_at', '>=', $dateFrom);
        });

        $userStats = [
            'total_users' => (clone $userQuery)->where('role', 'user')->count(),
            'total_police' => (clone $userQuery)->where('role', 'police')->count(),
            'total_admins' => (clone $userQuery)->where('role', 'admin')->count(),
        ];

        // Monthly user registrations for the last 12 months (or filtered period)
        $userMonthlyStartDate = $dateFrom ?: Carbon::now()->subMonths(12);
        $monthlyUsers = User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', $userMonthlyStartDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create($item->year, $item->month)->format('M Y'),
                    'count' => $item->count
                ];
            });

        // Incidents by municipality
        $incidentsByMunicipality = $this->getIncidentsByMunicipality();

        // Police performance (resolved incidents by police)
        $policePerformance = User::where('role', 'police')
            ->with('municipality')
            ->withCount(['assignedIncidents as resolved_count' => function ($query) {
                $query->where('status', IncidentStatus::Resolved);
            }])
            ->withCount(['assignedIncidents as total_assigned'])
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->fname . ' ' . $user->lname,
                    'municipality' => $user->municipality ? $user->municipality->name : 'N/A',
                    'resolved' => $user->resolved_count,
                    'total' => $user->total_assigned,
                    'efficiency' => $user->total_assigned > 0 ? round(($user->resolved_count / $user->total_assigned) * 100, 2) : 0
                ];
            })
            ->sortByDesc('resolved')
            ->take(10);

        // Average resolution time (in days) - filtered by date
        $avgResolutionTime = (clone $incidentQuery)->whereNotNull('resolved_date')
            ->selectRaw('AVG(DATEDIFF(resolved_date, created_at)) as avg_days')
            ->first()
            ->avg_days ?? 0;

        // Current period for display
        $currentPeriod = $period;
        $periodLabels = [
            'all' => 'All Time',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            '90d' => 'Last 90 Days'
        ];

        return view('admin.analytics', compact(
            'incidentStats',
            'incidentsByType',
            'incidentsByStatus',
            'monthlyIncidents',
            'userStats',
            'monthlyUsers',
            'incidentsByMunicipality',
            'policePerformance',
            'avgResolutionTime',
            'currentPeriod',
            'periodLabels'
        ));
    }

    /**
     * Get incidents grouped by municipality using LocationService
     */
    private function getIncidentsByMunicipality()
    {
        $locationService = app(LocationService::class);
        $municipalityCounts = collect();

        // Get incidents with media evidence that have coordinates
        $incidents = Incident::with(['mediaEvidence' => function($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        }])->whereHas('mediaEvidence', function($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        })->limit(100) // Limit for performance
          ->get();

        foreach ($incidents as $incident) {
            foreach ($incident->mediaEvidence as $media) {
                try {
                    $municipality = $locationService->getMunicipalityFromCoordinates(
                        $media->latitude,
                        $media->longitude
                    );

                    if ($municipality) {
                        $municipalityCounts[$municipality] = ($municipalityCounts[$municipality] ?? 0) + 1;
                        break; // Count once per incident
                    }
                } catch (\Exception $e) {
                    Log::warning("Geocoding failed for media {$media->id}: " . $e->getMessage());
                    continue;
                }
            }
            // Small delay to respect API rate limits
            usleep(200000); // 200ms delay
        }

        return $municipalityCounts->toArray();
    }
}
