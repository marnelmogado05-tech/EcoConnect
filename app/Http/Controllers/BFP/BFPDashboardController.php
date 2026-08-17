<?php

namespace App\Http\Controllers\BFP;

use App\Enums\IncidentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BFPDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->id;

        // Get statistics for the authenticated user
        $stats = [
            'total' => Incident::where('assigned_to', $userId)->count(),
            'pending' => Incident::where('assigned_to', $userId)->where('status', IncidentStatus::Pending)->count(),
            'in_progress' => Incident::where('assigned_to', $userId)->where('status', IncidentStatus::InProgress)->count(),
            'resolved' => Incident::where('assigned_to', $userId)->where('status', IncidentStatus::Resolved)->count(),
        ];

        // Get recent incidents (last 5)
        $recentIncidents = Incident::with(['mediaEvidence'])
            ->where('assigned_to', $userId)
            ->latest()
            ->take(5)
            ->get();

        // Analytics data
        $incidentsByType = Incident::where('assigned_to', $userId)
            ->select('incident_type', DB::raw('count(*) as count'))
            ->groupBy('incident_type')
            ->pluck('count', 'incident_type')
            ->toArray();

        // Incidents by status - include all statuses even if 0
        $possibleStatuses = IncidentStatus::cases();
        $incidentsByStatus = IncidentStatus::cases();
        foreach ($possibleStatuses as $status) {
            $incidentsByStatus[$status] = Incident::where('assigned_to', $userId)->where('status', $status)->count();
        }

        // Monthly incidents last 12 months
        $monthlyIncidents = Incident::where('assigned_to', $userId)
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Avg resolution time for resolved incidents
        $resolvedIncidents = Incident::where('assigned_to', $userId)->where('status', IncidentStatus::Resolved)->get();
        $avgResolutionTime = 0;
        if ($resolvedIncidents->count() > 0) {
            $totalDays = 0;
            foreach ($resolvedIncidents as $incident) {
                $totalDays += $incident->created_at->diffInDays($incident->updated_at);
            }
            $avgResolutionTime = round($totalDays / $resolvedIncidents->count(), 1);
        }

        return view('bfp.dashboard', compact('stats', 'recentIncidents', 'incidentsByType', 'incidentsByStatus', 'monthlyIncidents', 'avgResolutionTime'));
    }
}
