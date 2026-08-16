<?php

namespace App\Http\Controllers\Police;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Incident;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncidentsExport;
use Illuminate\Support\Facades\Http;
use App\Mail\IncidentResolvedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\IncidentAcknowledgement;
use App\Mail\DocumentationAcknowledgementMail;

class PoliceIncidentController extends Controller
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
        //
        // This previously started from Incident::all(), which loaded every incident in
        // the system into memory and then called query-builder methods on the resulting
        // Collection. Collection::where() with a closure filters by callback, so the
        // search branch matched every row, and the officer scope was dropped entirely —
        // an officer saw system-wide counts beside their own list.
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
            'assigned' => (clone $statsQuery)->where('status', 'Assigned')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'resolved' => (clone $statsQuery)->where('status', 'Resolved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'Rejected')->count(),
            'pending' => (clone $statsQuery)->where('status', 'Pending')->count(),
        ];

        // Badge classes for status and priority
        $statusBadgeClasses = [
            'Pending' => 'bg-warning',
            'In Progress' => 'bg-info',
            'Resolved' => 'bg-success',
            'Rejected' => 'bg-danger',
            'Assigned' => 'bg-primary',
        ];

        $priorityBadgeClasses = [
            'Normal' => 'bg-secondary',
            'High' => 'bg-warning',
            'Urgent' => 'bg-danger'
        ];

        // Incident type mapping (for display purposes)
        $incidentTypes = [
            'Illegal Logging' => 'Illegal Logging',
            'Pollution' => 'Pollution',
            'Wildlife Crime' => 'Wildlife Crime',
            'Illegal Waste Disposal' => 'Illegal Waste Disposal',
            'Other' => 'Other'
        ];

        return view('police.incidents', compact(
            'incidents',
            'stats',
            'statusBadgeClasses',
            'priorityBadgeClasses',
            'incidentTypes',
        ));
    }

    // public function resolve(Request $request, $id)
    // {
    //     $incident = Incident::findOrFail($id);

    //     // For reject method
    //     $validated = $request->validate([
    //         'resolution_details' => 'required|string'
    //     ]);

    //     try {
    //         // Store previous status for logging
    //         $previousStatus = $incident->status;

    //         $incident->update([
    //             'status' => 'In Progress',
    //             'resolution_details' => $validated['resolution_details'],
    //             'date_taken_into_action' => now(),
    //         ]);

    //         Mail::to($incident->user->email)->send(new IncidentResolvedMail($incident, $incident->user));

    //         return redirect()->back()->with('success', 'Incident successfully resolved.');

    //     } catch (\Exception $e) {
    //         Log::error('Error resolving incident: ' . $e->getMessage());
    //         return redirect()->back()->with('error', 'Failed to resolve incident. Please try again.');
    //     }
    // }

    public function taken(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);

        // Without this, any officer could act on any incident — upload evidence to it,
        // change its status, and attach their own documentation — just by changing the
        // id in the URL. The route's 'police' middleware only established the role.
        $this->authorize('takeAction', $incident);

        // Validate documentation notes
        $validated = $request->validate([
            'resolution_details' => 'required|string|min:10',
            'evidence_images' => 'sometimes|array',
            'evidence_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        try {
            // Store previous status for logging
            $previousStatus = $incident->status;

            // Count uploaded evidence
            $evidenceCount = 0;

            // Handle image uploads
            if ($request->hasFile('evidence_images')) {
                foreach ($request->file('evidence_images') as $image) {
                    if ($image->isValid()) {
                        // Store image with custom path structure
                        $path = $image->store(
                            'incidents/' . $incident->reference_number,
                            'public'
                        );

                        // Create media evidence record
                        $incident->mediaEvidence()->create([
                            'file_path' => $path,
                            'file_name' => $image->getClientOriginalName(),
                            'mime_type' => $image->getMimeType(),
                            'file_size' => $image->getSize(),
                        ]);

                        $evidenceCount++;
                    }
                }
            }

            // Update the incident to mark as taken
            $incident->update([
                'status' => 'In Progress',
                'resolution_details' => $validated['resolution_details'],
                'date_taken_into_action' => now(),
            ]);

            // Create or update acknowledgement record
            $acknowledgement = IncidentAcknowledgement::updateOrCreate(
                [
                    'incident_id' => $incident->id,
                    'officer_id' => auth()->user()->id,
                ],
                [
                    'acknowledged_at' => now(),
                    'documentation' => $validated['resolution_details'],
                    'evidence_count' => $evidenceCount,
                ]
            );

            // Send acknowledgement receipt to officer
            Mail::to(Auth::user()->email)->send(
                new DocumentationAcknowledgementMail($incident, Auth::user(), $acknowledgement)
            );

            // Send notification to reporter
            // Mail::to($incident->user->email)->send(
            //     new IncidentResolvedMail($incident, $incident->user)
            // );

            return redirect()->back()->with('success', 'Incident successfully taken into action. Acknowledgement receipt sent to your email.');

        } catch (\Exception $e) {
            Log::error('Error taking incident into action: ' . $e->getMessage());
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

        return view('police.incidents.print', $data);
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
}
