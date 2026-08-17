<?php

namespace App\Services;

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Mail\DocumentationAcknowledgementMail;
use App\Mail\IncidentAssignedMail;
use App\Mail\IncidentRejectedMail;
use App\Mail\IncidentResolvedMail;
use App\Models\Incident;
use App\Models\IncidentAcknowledgement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

/**
 * Owns the incident lifecycle.
 *
 * Every status transition used to be hand-written inline in whichever controller
 * happened to need it — four controllers, each with slightly different behaviour, none
 * agreeing on which transitions were legal. The model carried assignTo(),
 * markAsResolved() and reject() methods that no controller ever called.
 *
 * Each method here does the whole transition: the state change, the record-keeping and
 * the notification, inside one transaction. Controllers validate input, call one of
 * these, and render the result.
 */
class IncidentWorkflowService
{
    /**
     * Which statuses each transition may be entered from.
     *
     * Previously nothing checked this at all, so a resolved incident could be resolved
     * again, and a rejected one reassigned.
     *
     * @var array<string, array<int, IncidentStatus>>
     */
    private const ALLOWED_FROM = [
        'assign' => [IncidentStatus::Pending, IncidentStatus::Assigned, IncidentStatus::InProgress],
        'takeAction' => [IncidentStatus::Assigned, IncidentStatus::InProgress],
        'resolve' => [IncidentStatus::Assigned, IncidentStatus::InProgress],
        'reject' => [IncidentStatus::Pending, IncidentStatus::Assigned, IncidentStatus::InProgress],
    ];

    /**
     * Assign an incident to a responder and set its priority.
     */
    public function assign(
        Incident $incident,
        User $responder,
        IncidentPriority $priority,
        ?User $actor = null,
    ): Incident {
        $this->guard($incident, 'assign');

        if (! $responder->role->isResponder()) {
            throw new RuntimeException('Incidents may only be assigned to a police or BFP account.');
        }

        DB::transaction(function () use ($incident, $responder, $priority) {
            $incident->update([
                'assigned_to' => $responder->id,
                'priority' => $priority,
                'status' => IncidentStatus::Assigned,
                'assigned_at' => now(),
            ]);
        });

        $this->notify(
            fn () => Mail::to($responder->email)
                ->send(new IncidentAssignedMail($incident, $responder, $actor ?? $responder)),
            "assignment notice for incident {$incident->id}"
        );

        return $incident;
    }

    /**
     * A responder takes an assigned incident into action and files documentation.
     */
    public function takeAction(
        Incident $incident,
        User $responder,
        string $documentation,
        int $evidenceCount = 0,
    ): IncidentAcknowledgement {
        $this->guard($incident, 'takeAction');

        $acknowledgement = DB::transaction(function () use ($incident, $responder, $documentation, $evidenceCount) {
            $incident->update([
                'status' => IncidentStatus::InProgress,
                'resolution_details' => $documentation,
                'date_taken_into_action' => now(),
            ]);

            return IncidentAcknowledgement::updateOrCreate(
                ['incident_id' => $incident->id, 'officer_id' => $responder->id],
                [
                    'acknowledged_at' => now(),
                    'documentation' => $documentation,
                    'evidence_count' => $evidenceCount,
                ]
            );
        });

        $this->notify(
            fn () => Mail::to($responder->email)
                ->send(new DocumentationAcknowledgementMail($incident, $responder, $acknowledgement)),
            "acknowledgement receipt for incident {$incident->id}"
        );

        return $acknowledgement;
    }

    /**
     * Store uploaded evidence against an incident and return how many were kept.
     *
     * This loop was written out three times across the police and BFP controllers, and
     * all three copies wrote to the `public` disk — so evidence attached by responders
     * stayed world-readable by URL even after citizen uploads were moved to private
     * storage.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    public function attachEvidence(Incident $incident, array $files): int
    {
        $stored = 0;

        foreach ($files as $file) {
            if (! $file->isValid()) {
                continue;
            }

            $path = $file->store('incidents/'.$incident->reference_number, 'local');

            $incident->mediaEvidence()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * Close an incident as resolved.
     */
    public function resolve(Incident $incident, string $resolutionDetails): Incident
    {
        $this->guard($incident, 'resolve');

        DB::transaction(function () use ($incident, $resolutionDetails) {
            $incident->update([
                'status' => IncidentStatus::Resolved,
                'resolution_details' => $resolutionDetails,
                'resolved_date' => now(),
            ]);
        });

        if ($reporter = $incident->user) {
            $this->notify(
                fn () => Mail::to($reporter->email)->send(new IncidentResolvedMail($incident, $reporter)),
                "resolution notice for incident {$incident->id}"
            );
        }

        return $incident;
    }

    /**
     * Dismiss an incident, clearing any assignment.
     */
    public function reject(Incident $incident, string $reason): Incident
    {
        $this->guard($incident, 'reject');

        DB::transaction(function () use ($incident, $reason) {
            $incident->update([
                'status' => IncidentStatus::Rejected,
                'rejection_reason' => $reason,
                'assigned_to' => null,
            ]);
        });

        if ($reporter = $incident->user) {
            $this->notify(
                fn () => Mail::to($reporter->email)->send(new IncidentRejectedMail($incident, $reporter)),
                "rejection notice for incident {$incident->id}"
            );
        }

        return $incident;
    }

    /**
     * Refuse a transition that does not make sense from the current state.
     */
    private function guard(Incident $incident, string $transition): void
    {
        $allowed = self::ALLOWED_FROM[$transition];

        if (! in_array($incident->status, $allowed, true)) {
            throw new RuntimeException(
                "An incident that is {$incident->status->label()} cannot be {$transition}ed."
            );
        }
    }

    /**
     * Send a notification without letting a mail failure undo committed work.
     *
     * The controllers previously sent mail inside the same try/catch as the update but
     * without a transaction, so a failing send produced "Failed to resolve incident"
     * while the incident had in fact already been resolved.
     */
    private function notify(callable $send, string $description): void
    {
        try {
            $send();
        } catch (\Throwable $e) {
            Log::error("Failed to send {$description}: ".$e->getMessage());
        }
    }
}
