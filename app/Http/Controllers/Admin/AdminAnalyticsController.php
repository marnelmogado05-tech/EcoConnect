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
        $monthlyIncidents = $this->countByMonth(
            Incident::where('created_at', '>=', $monthlyStartDate)
        );

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
        $monthlyUsers = $this->countByMonth(
            User::where('created_at', '>=', $userMonthlyStartDate)
        );

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

        // Average resolution time in days.
        //
        // DATEDIFF is MySQL-only, so this page could not run on any other connection and
        // its test was skipped outside MySQL. Computed in PHP over the resolved rows
        // instead, which is portable and — since only resolved incidents are read — no
        // more expensive in practice.
        $resolvedDates = (clone $incidentQuery)
            ->whereNotNull('resolved_date')
            ->get(['created_at', 'resolved_date']);

        $avgResolutionTime = $resolvedDates->isEmpty()
            ? 0
            : round(
                $resolvedDates->avg(
                    fn ($incident) => $incident->created_at->startOfDay()
                        ->diffInDays($incident->resolved_date->startOfDay())
                ),
                1
            );

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
     * Group a query into monthly counts without database-specific date functions.
     *
     * YEAR() and MONTH() are MySQL-only. Grouping on the stored timestamp's date prefix
     * works on every supported driver, and the month count here is bounded (twelve, or
     * fewer for a filtered period), so building the labels in PHP costs nothing.
     *
     * @return \Illuminate\Support\Collection<int, array{month: string, count: int}>
     */
    private function countByMonth($query)
    {
        return $query->reorder()
            ->get(['created_at'])
            ->groupBy(fn ($row) => $row->created_at->format('Y-m'))
            ->map(fn ($rows, $month) => [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'count' => $rows->count(),
            ])
            ->sortKeys()
            ->values();
    }

    /**
     * Incident counts per municipality.
     *
     * One grouped query over a column resolved at write time. This previously loaded up
     * to 100 incidents and reverse-geocoded each one against Nominatim with a 200ms
     * sleep between calls, so the analytics page took upwards of twenty seconds and was
     * capped at 100 incidents — meaning the chart was wrong as soon as the table grew
     * past that.
     *
     * @return array<string, int>
     */
    private function getIncidentsByMunicipality(): array
    {
        return Incident::query()
            ->whereNotNull('municipality_name')
            ->selectRaw('municipality_name, COUNT(*) as total')
            ->groupBy('municipality_name')
            ->orderByDesc('total')
            ->pluck('total', 'municipality_name')
            ->toArray();
    }
}
