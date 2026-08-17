<?php

namespace App\Http\Controllers\BFP;

use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Http\Controllers\Controller;
use App\Services\IncidentWorkflowService;
use Illuminate\Http\Request;
use RuntimeException;
use App\Models\User;
use App\Models\Incident;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncidentsExport;
use Illuminate\Support\Facades\Http;
use App\Mail\IncidentResolvedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\DocumentationAcknowledgementMail;
use App\Models\IncidentAcknowledgement;
use Illuminate\Support\Facades\Auth;


class BFPIncidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Incident::with(['mediaEvidence', 'assignedTo', 'followups'])
                            ->where('assigned_to', auth()->user()->id)
                            ->latest();

        // Apply filters - use filled() instead of has() for better validation
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $incidents = $query->paginate(10);

        // Statistics - same filters, scoped to this officer's own assignments.
        // See the note in PoliceIncidentController: this started from Incident::all(),
        // which loaded the whole table into memory and then treated the Collection as a
        // query builder, matching everything and ignoring the officer scope.
        $statsQuery = Incident::where('assigned_to', Auth::id());

        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $statsQuery->where('incident_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'assigned' => (clone $statsQuery)->where('status', IncidentStatus::Assigned)->count(),
            'pending' => (clone $statsQuery)->where('status', IncidentStatus::Pending)->count(),
            'in_progress' => (clone $statsQuery)->where('status', IncidentStatus::InProgress)->count(),
            'resolved' => (clone $statsQuery)->where('status', IncidentStatus::Resolved)->count(),
        ];

        // Badge classes come from the enums; the views ask the value for its own class.
        $incidentTypes = IncidentType::options();

        return view('bfp.incidents', compact(
            'incidents',
            'stats',
            'incidentTypes',
        ));
    }


    /*
     * resolve() used to sit here: unrouted, unauthorised, and despite its name it set the
     * status to "In Progress" rather than resolving anything — a near-copy of taken()
     * that also wrote evidence to the public disk. Resolution is an admin action; this
     * was dead weight carrying two defects.
     */

    public function taken(Request $request, IncidentWorkflowService $workflow, $id)
    {
        $incident = Incident::findOrFail($id);

        // See PoliceIncidentController::taken — the role middleware established that the
        // caller is a BFP officer, not that this incident is theirs.
        $this->authorize('takeAction', $incident);

        $validated = $request->validate([
            'resolution_details' => 'required|string|min:10',
            'evidence_images' => 'sometimes|array',
            'evidence_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        try {
            $evidenceCount = $workflow->attachEvidence(
                $incident,
                $request->file('evidence_images') ?? []
            );

            $workflow->takeAction(
                $incident,
                Auth::user(),
                $validated['resolution_details'],
                $evidenceCount,
            );

            return redirect()->back()->with('success', 'Incident successfully taken into action. Acknowledgement receipt sent to your email.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error taking incident into action: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to take action on incident. Please try again.');
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

        return view('bfp.incidents.print', $data);
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
                        ->where('assigned_to', auth()->user()->id)
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
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }
}
