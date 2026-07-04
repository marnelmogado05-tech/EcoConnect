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
     * Search incident by reference number
     */
    public function search(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string|max:50'
        ]);

        $referenceNumber = $request->reference_number;

        // Search for incident by reference number
        $incident = Incident::with(['user', 'assignedTo', 'mediaEvidence', 'followups'])
                           ->where('reference_number', $referenceNumber)
                           ->first();

        if (!$incident) {
            return redirect()->route('track.index')->with('error', 'No incident found with reference number: ' . $referenceNumber);
        }

        return redirect()->route('track.index')->with('incident', $incident);
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

        if (!$incident) {
            return redirect()->route('track.index')->with('error', 'Incident not found.');
        }

        // For public users, store follow-up with NULL user_id
        $followup = IncidentFollowup::create([
            'incident_id' => $incident->id,
            'user_id' => auth()->id,
            'follow_up_text' => $request->follow_up_text,
            'follow_up_type' => 'User Update'
        ]);

        // Notify assigned staff
        if ($incident->assigned_to) {
            $officer = User::find($incident->assigned_to);
            if ($officer) {
                try {
                    Mail::queue(new IncidentFollowupMail($incident, $officer, $followup));
                } catch (\Exception $e) {
                    // Log error but don't fail the request
                    Log::error('Failed to send followup email: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('track.index')
            ->with('incident', $incident->load('followups'))
            ->with('success', 'Follow-up added successfully!');
    }
}
