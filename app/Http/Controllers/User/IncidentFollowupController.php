<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\IncidentFollowup;
use Illuminate\Support\Facades\Mail;
use App\Mail\IncidentFollowupMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class IncidentFollowupController extends Controller
{
    /**
     * Store a new follow-up for authenticated users
     */
    public function store(Request $request, Incident $incident)
    {
        // Previously this route sat behind 'auth' alone, so any signed-in citizen could
        // append to any incident by changing the id in the URL.
        $this->authorize('addFollowup', $incident);

        $request->validate(['follow_up_text' => 'required|string|max:1000']);

        $followup = IncidentFollowup::create([
            'incident_id' => $incident->id,
            'user_id' => Auth::id(),
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
                    Log::error('Failed to send followup email: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('incidents')
            ->with('success', 'Follow-up added successfully!');
    }

    /**
     * Show follow-ups for an incident
     */
    public function show(Incident $incident)
    {
        // This returned any incident's entire follow-up thread to any authenticated
        // caller — the reporter's own account of an environmental crime included.
        $this->authorize('view', $incident);

        $followups = $incident->followups()->with('user')->get();

        return response()->json([
            'success' => true,
            'followups' => $followups
        ]);
    }

    /**
     * Staff/Admin responds to follow-ups
     */
    public function respond(Request $request, Incident $incident)
    {
        // "Staff Response" carries authority. Restricted to admins and the assigned
        // responder; previously any signed-in citizen could author one on any incident.
        $this->authorize('respond', $incident);

        $request->validate(['follow_up_text' => 'required|string|max:1000']);

        $followup = IncidentFollowup::create([
            'incident_id' => $incident->id,
            'user_id' => Auth::id(),
            'follow_up_text' => $request->follow_up_text,
            'follow_up_type' => 'Staff Response'
        ]);

        // Notify the incident reporter
        if ($incident->user_id) {
            $reporter = User::find($incident->user_id);
            if ($reporter) {
                try {
                    Mail::to($reporter->email)->queue(new IncidentFollowupMail($incident, $reporter, $followup));
                } catch (\Exception $e) {
                    Log::error('Failed to send staff response email: ' . $e->getMessage());
                }
            }
        }

        // Redirect based on where the request came from
        $routeName = Auth::user()?->isAdmin() ? 'admin.incidents' : 'incidents';

        return redirect()->route($routeName)
            ->with('success', 'Staff response posted successfully!');
    }
}
