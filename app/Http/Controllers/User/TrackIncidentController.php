<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Incident;
use App\Models\IncidentFollowup;
use Illuminate\Support\Facades\Mail;
use App\Mail\IncidentFollowupMail;
use Illuminate\Support\Facades\Log;

class TrackIncidentController extends Controller
{
    /**
     * Show track incident form
     */
    public function create()
    {
        return view('track');
    }

    /**
     * Search incident by reference number.
     *
     * This page is public: anyone holding a reference number reaches it without signing
     * in. It previously eager-loaded the reporter and flashed the whole Eloquent model
     * into the session, which meant the reporter's name, email, phone, address and their
     * uploaded government ID were all serialised into the session store and handed to
     * whoever had the number. For a platform where people report environmental crime and
     * may face retaliation for it, reporter identity is the one thing that must not leak.
     *
     * Only the status of the report is public now.
     */
    public function search(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string|max:50'
        ]);

        $incident = Incident::with(['followups'])
            ->where('reference_number', $request->reference_number)
            ->first();

        if (! $incident) {
            return redirect()->route('track.index')
                ->with('error', 'No incident found with reference number: '.$request->reference_number);
        }

        return redirect()->route('track.index')
            ->with('incident', $this->publicView($incident));
    }

    /**
     * The subset of an incident that is safe to show without authentication.
     *
     * A plain array, not a model: flashing an Eloquent instance to the session
     * serialises every attribute it happens to be carrying, whatever the view uses.
     *
     * @return array<string, mixed>
     */
    private function publicView(Incident $incident): array
    {
        return [
            'reference_number' => $incident->reference_number,
            'incident_type' => $incident->incident_type?->value,
            'status' => $incident->status?->value,
            'priority' => $incident->priority?->value,
            'incident_date' => $incident->incident_date?->format('Y-m-d'),
            'reported_at' => $incident->created_at?->format('Y-m-d'),
            'resolved_date' => $incident->resolved_date?->format('Y-m-d'),
            'resolution_details' => $incident->resolution_details,
            'rejection_reason' => $incident->rejection_reason,
            'followups' => $incident->followups->map(fn (IncidentFollowup $followup) => [
                'follow_up_text' => $followup->follow_up_text,
                // Author roles only — naming the staff member or the reporter would
                // reintroduce the identity leak through the thread.
                'follow_up_type' => $followup->follow_up_type,
                'created_at' => $followup->created_at?->format('Y-m-d H:i'),
            ])->all(),
        ];
    }

    /**
     * Add a follow-up to an incident (public, no auth required)
     */
    public function addFollowup(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string|max:50',
            'follow_up_text' => 'required|string|max:1000'
        ]);

        $incident = Incident::where('reference_number', $request->reference_number)->first();

        if (! $incident) {
            return redirect()->route('track.index')->with('error', 'Incident not found.');
        }

        // Public follow-ups have no user; auth()->id() is null for anonymous visitors.
        // This previously read auth()->id — the property, not the method — which is
        // undefined on the auth manager and wrote null into a NOT NULL foreign key.
        $followup = IncidentFollowup::create([
            'incident_id' => $incident->id,
            'user_id' => auth()->id(),
            'follow_up_text' => $request->follow_up_text,
            'follow_up_type' => 'User Update'
        ]);

        // Notify assigned staff
        if ($incident->assigned_to) {
            $officer = User::find($incident->assigned_to);
            if ($officer) {
                try {
                    Mail::to($officer->email)->queue(new IncidentFollowupMail($incident, $officer, $followup));
                } catch (\Exception $e) {
                    // Log error but don't fail the request
                    Log::error('Failed to send followup email: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('track.index')
            ->with('incident', $this->publicView($incident->load('followups')))
            ->with('success', 'Follow-up added successfully!');
    }
}
