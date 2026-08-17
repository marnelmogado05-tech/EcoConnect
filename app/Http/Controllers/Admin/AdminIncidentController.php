<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Services\IncidentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
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
        $policeUsers = User::whereIn('role', UserRole::responderValues())
                            ->where('status', UserStatus::Active)
                            ->get();

        $bfpUsers = User::where('role', UserRole::Bfp)
                            ->where('status', UserStatus::Active)
                            ->get();

        // `user` is eager-loaded because the table renders the reporter's name and
        // email; without it each row issued its own query.
        $query = Incident::with(['user', 'mediaEvidence', 'assignedTo', 'followups'])
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

        // Municipality is resolved once at write time by ResolveIncidentLocation, so
        // filtering is an indexed comparison. This used to load every incident with
        // coordinates, reverse-geocode each one against Nominatim, and usleep(200000)
        // between calls to respect the rate limit — a filter that took minutes and
        // discarded the result at the end of the request.
        if ($request->filled('municipality')) {
            $query->where('municipality_name', $request->municipality);
        }

        $incidents = $query->paginate(10)->withQueryString();

        // The municipalities that incidents have actually been resolved to — one indexed
        // DISTINCT, where this used to reverse-geocode every unique coordinate pair in
        // the table with sleep(1) between calls. A 24-hour cache hid the cost from most
        // requests but not from the unlucky one that had to rebuild it, and a timeout
        // meant the cache was never written and the next admin paid it again.
        $municipalities = Incident::query()
            ->whereNotNull('municipality_name')
            ->distinct()
            ->orderBy('municipality_name')
            ->pluck('municipality_name');

        // Badge classes and labels come from the enums now. They were previously
        // copy-pasted into five controllers, and this one keyed incidentTypes by
        // snake_case values that the database never contained, so the type filter on
        // this page could not match anything.
        $incidentTypes = IncidentType::options();

        return view('admin.incidents', compact(
            'incidents',
            'incidentTypes',
            'policeUsers',
            'bfpUsers',
            'municipalities'
        ));
    }

    /*
     * getIncidentIdsWithCoordinates(), findIncidentsByMunicipality(),
     * getAvailableMunicipalities() and municipalityMatches() all lived here. Between
     * them they reverse-geocoded the incident table on demand while rendering, with
     * sleep(1) and usleep(200000) calls to stay under the Nominatim rate limit.
     * ResolveIncidentLocation does that work once, off the request path, and
     * LocationService now normalises the "Pamplona, Cagayan" case at the point of
     * resolution instead of at every comparison.
     */

    /*
     * getIncidentMunicipality() lived here: a public controller method that was never
     * routed and never called, geocoding one incident on demand. M4 replaces this whole
     * approach with a municipality resolved once at write time.
     */

    public function resolve(Request $request, IncidentWorkflowService $workflow, $id)
    {
        $incident = Incident::findOrFail($id);

        $this->authorize('manage', $incident);

        $validated = $request->validate([
            'resolution_details' => 'required|string'
        ]);

        try {
            $workflow->resolve($incident, $validated['resolution_details']);

            return redirect()->back()->with('success', 'Incident successfully resolved.');
        } catch (RuntimeException $e) {
            // A refused transition is worth telling the user about verbatim: it says
            // what state the incident is actually in.
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error resolving incident: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to resolve incident. Please try again.');
        }
    }

    public function assign(Request $request, IncidentWorkflowService $workflow, $id)
    {
        $incident = Incident::findOrFail($id);

        $this->authorize('manage', $incident);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'priority' => ['required', Rule::enum(IncidentPriority::class)],
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
                              ->whereIn('role', UserRole::responderValues())
                              ->firstOrFail();

            $workflow->assign(
                $incident,
                $assignedOfficer,
                IncidentPriority::from($validated['priority']),
                auth()->user(),
            );

            return redirect()->back()->with('success', 'Incident successfully assigned to police officer.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error assigning incident: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to assign incident. Please try again.');
        }
    }

    public function reject(Request $request, IncidentWorkflowService $workflow, $id)
    {
        $incident = Incident::findOrFail($id);

        $this->authorize('manage', $incident);

        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        try {
            $workflow->reject($incident, $validated['rejection_reason']);

            return redirect()->back()->with('success', 'Incident successfully dismissed.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error rejecting incident: '.$e->getMessage());

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

    /*
     * respondToFollowup() lived here: unrouted, unvalidated, unauthorised, and it
     * returned nothing at all — so had anything reached it, the caller would have got a
     * blank page. IncidentFollowupController::respond() is the real implementation.
     */
}
