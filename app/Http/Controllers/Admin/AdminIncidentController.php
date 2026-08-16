<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\User;
use App\Models\MediaEvidence;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncidentsExport;
use Illuminate\Support\Facades\Http;
use App\Mail\IncidentRejectedMail;
use App\Mail\IncidentAssignedMail;
use App\Mail\IncidentResolvedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\LocationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\IncidentFollowup;

class AdminIncidentController extends Controller
{
    public function index(Request $request)
    {
        // whereIn, not where()->orWhere(): the ungrouped version parsed as
        // (role = 'police') OR (role = 'bfp' AND status = 'Active'), which put inactive
        // police officers into the assignment dropdown.
        $policeUsers = User::whereIn('role', ['police', 'bfp'])
                            ->where('status', 'Active')
                            ->get();

        $bfpUsers = User::where('role', 'bfp')
                            ->where('status', 'Active')
                            ->get();

        $query = Incident::with(['mediaEvidence', 'assignedTo', 'followups'])
            ->latest();

        // Apply basic filters first
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Date range
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $startDate = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : null;
            $endDate = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : null;

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('created_at', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        // Municipality filter - Only process if explicitly selected
        if ($request->filled('municipality')) {
            $municipality = $request->municipality;

            // Get a limited set of incidents with coordinates for geocoding
            $incidentIdsWithCoords = $this->getIncidentIdsWithCoordinates();

            if ($incidentIdsWithCoords->isNotEmpty()) {
                $matchingIncidentIds = $this->findIncidentsByMunicipality($incidentIdsWithCoords, $municipality);

                if ($matchingIncidentIds->isNotEmpty()) {
                    $query->whereIn('id', $matchingIncidentIds);
                } else {
                    // No matches found, return empty results
                    $query->where('id', 0);
                }
            } else {
                // No incidents with coordinates
                $query->where('id', 0);
            }
        }

        $incidents = $query->paginate(10)->withQueryString();

        // Get municipalities for dropdown (cached for performance)
        $municipalities = cache()->remember('municipalities_list', 86400, function () { // Cache for 24 hours
            return $this->getAvailableMunicipalities();
        });

        // Badge classes for status and priority
        $statusBadgeClasses = [
            'Pending' => 'bg-warning',
            'In Progress' => 'bg-info',
            'Resolved' => 'bg-success',
            'Rejected' => 'bg-danger',
            'Assigned' => 'bg-primary'
        ];

        $priorityBadgeClasses = [
            'Normal' => 'bg-secondary',
            'High' => 'bg-warning',
            'Urgent' => 'bg-danger'
        ];

        // Incident type mapping
        $incidentTypes = [
            'illegal_logging' => 'Illegal Logging',
            'pollution' => 'Pollution',
            'wildlife_crime' => 'Wildlife Crime',
            'illegal_waste_disposal' => 'Illegal Waste Disposal',
            'other' => 'Other'
        ];

        return view('admin.incidents', compact(
            'incidents',
            'statusBadgeClasses',
            'priorityBadgeClasses',
            'incidentTypes',
            'policeUsers',
            'bfpUsers',
            'municipalities'
        ));
    }

    /**
     * Get incidents that have media evidence with coordinates
     */
    private function getIncidentIdsWithCoordinates()
    {
        return Incident::whereHas('mediaEvidence', function($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        })->pluck('id');
    }

    /**
     * Find incidents by municipality with strict limits to prevent timeout
     */
    private function findIncidentsByMunicipality($incidentIds, $targetMunicipality)
    {
        $locationService = app(LocationService::class);
        $matchingIds = collect();

        // Get incidents with their media evidence
        $incidents = Incident::with(['mediaEvidence' => function($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        }])->whereIn('id', $incidentIds)
          ->get();

        foreach ($incidents as $incident) {
            foreach ($incident->mediaEvidence as $media) {
                try {
                    $municipality = $locationService->getMunicipalityFromCoordinates(
                        $media->latitude,
                        $media->longitude
                    );

                    if ($municipality && $this->municipalityMatches($municipality, $targetMunicipality)) {
                        $matchingIds->push($incident->id);
                        break; // Found match, move to next incident
                    }
                } catch (\Exception $e) {
                    Log::warning("Geocoding failed for media {$media->id}: " . $e->getMessage());
                    continue;
                }
            }
            // Small delay to respect API rate limits
            usleep(200000); // 200ms delay
        }
        return $matchingIds->unique();
    }

    /**
     * Get available municipalities from existing data
     */
    private function getAvailableMunicipalities()
    {
        $locationService = app(LocationService::class);
        $municipalities = collect();

        // Get unique coordinates to minimize API calls
        $uniqueCoordinates = MediaEvidence::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('latitude', 'longitude')
            ->distinct()
            ->get();

        foreach ($uniqueCoordinates as $coords) {
            try {
                $municipality = $locationService->getMunicipalityFromCoordinates(
                    $coords->latitude,
                    $coords->longitude
                );

                if ($municipality && !$municipalities->contains($municipality)) {
                    $municipalities->push($municipality);
                }

                // Respect API rate limits - 1 request per second
                sleep(1);

            } catch (\Exception $e) {
                Log::warning("Geocoding failed for coordinates {$coords->latitude},{$coords->longitude}: " . $e->getMessage());
                continue;
            }
        }

        return $municipalities->sort()->values();
    }

    /**
     * Check if municipality names match, handling variations like "Pamplona" vs "Pamplona, Navarre"
     */
    private function municipalityMatches($returnedMunicipality, $targetMunicipality)
    {
        $returned = trim(strtolower($returnedMunicipality));
        $target = trim(strtolower($targetMunicipality));

        // Exact match
        if ($returned === $target) {
            return true;
        }

        // Check if target is the primary municipality name (before comma)
        // e.g., "Pamplona" matches "Pamplona, Navarre"
        $primaryMunicipality = explode(',', $returned)[0];
        return $primaryMunicipality === $target;
    }

    /**
     * Get municipality for a specific incident (for display in table)
     */
    public function getIncidentMunicipality($incidentId)
    {
        try {
            $incident = Incident::with(['mediaEvidence' => function($q) {
                $q->whereNotNull('latitude')->whereNotNull('longitude');
            }])->findOrFail($incidentId);

            if ($incident->mediaEvidence->isEmpty()) {
                return null;
            }

            $locationService = app(LocationService::class);

            // Use the first media evidence with coordinates
            $media = $incident->mediaEvidence->first();
            $municipality = $locationService->getMunicipalityFromCoordinates(
                $media->latitude,
                $media->longitude
            );

            return $municipality;

        } catch (\Exception $e) {
            Log::error('Error getting municipality for incident: ' . $e->getMessage());
            return null;
        }
    }

    public function resolve(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        // For reject method
        $validated = $request->validate([
            'resolution_details' => 'required|string'
        ]);

        try {
            // Store previous status for logging
            $previousStatus = $incident->status;

            // Update the incident
            $incident->update([
                'status' => 'Resolved',
                'resolution_details' => $validated['resolution_details'],
                'resolved_date' => now(),
            ]);

            Mail::to($incident->user->email)->send(new IncidentResolvedMail($incident, $incident->user));

            return redirect()->back()->with('success', 'Incident successfully resolved.');

        } catch (\Exception $e) {
            Log::error('Error resolving incident: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to resolve incident. Please try again.');
        }
    }

    public function assign(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        // For assign method
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:Normal,High,Urgent',
        ], [
            'assigned_to.required' => 'Please select a police officer to assign.',
            'assigned_to.exists' => 'The selected police officer does not exist.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Please select a valid priority level.',
        ]);

        try {

            // The ungrouped form was (id = X AND role = 'police') OR role = 'bfp', so this
            // could return an arbitrary BFP user unrelated to the requested id — and then
            // email the assignment to them.
            $assignedOfficer = User::where('id', $validated['assigned_to'])
                              ->whereIn('role', ['police', 'bfp'])
                              ->firstOrFail();

            // Update the incident
            $incident->update([
                'assigned_to' => $validated['assigned_to'],
                'priority' => $validated['priority'],
                'status' => 'Assigned',
                'assigned_at' => now()
            ]);

            Mail::to($assignedOfficer->email)->send(new IncidentAssignedMail($incident, $assignedOfficer, auth()->user()));

            return redirect()->back()->with('success', 'Incident successfully assigned to police officer.');

        } catch (\Exception $e) {
            Log::error('Error assigning incident: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to assign incident. Please try again.');
        }
    }

    public function reject(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        // For reject method
        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        try {
            // Store previous status for logging
            $previousStatus = $incident->status;

            // Update the incident
            $incident->update([
                'status' => 'Rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'assigned_to' => null // Remove assignment if any
            ]);



            Mail::to($incident->user->email)->send(new IncidentRejectedMail($incident, $incident->user));
            Log::info('Incident rejected');
            return redirect()->back()->with('success', 'Incident successfully dismissed.');

        } catch (\Exception $e) {
            Log::error('Error rejecting incident: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject incident. Please try again.');
        }
    }

    public function print(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'priority' => $request->get('priority'),
            'search' => $request->get('search')
        ];

        $incidents = $this->getFilteredIncidents($filters);

        $data = [
            'incidents' => $incidents->get(),
            'filters' => $filters,
            'printDate' => now()->format('F j, Y g:i A'),
            'totalCount' => $incidents->count()
        ];

        return view('admin.incidents.print', $data);
    }

    public function export(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'priority' => $request->get('priority'),
            'search' => $request->get('search')
        ];

        $format = $request->get('format', 'xlsx');

        $filename = 'incident-reports-' . date('Y-m-d-H-i-s') . '.' . $format;

        switch ($format) {
            case 'csv':
                return Excel::download(new IncidentsExport($filters), $filename, \Maatwebsite\Excel\Excel::CSV);
            case 'pdf':
                return Excel::download(new IncidentsExport($filters), $filename, \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return Excel::download(new IncidentsExport($filters), $filename, \Maatwebsite\Excel\Excel::XLSX);
        }
    }

    private function getFilteredIncidents($filters = [])
    {
        $query = Incident::with(['user', 'assignedTo', 'mediaEvidence'])
                        ->latest();

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply type filter
        if (!empty($filters['type'])) {
            $query->where('incident_type', $filters['type']);
        }

        // Apply priority filter
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                // No `location` column exists on incidents; searching it threw an
                // unknown-column error on every filtered export and print.
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    public function respondToFollowup(Request $request, Incident $incident) {
        IncidentFollowup::create([
            'incident_id' => $incident->id,
            'user_id' => auth()->id(),
            'follow_up_text' => $request->response,
            'follow_up_type' => 'Staff Response'
        ]);
    }
}
