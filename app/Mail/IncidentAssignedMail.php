<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IncidentAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $incident;
    public $assignedOfficer;
    public $assigner;

    public function __construct(Incident $incident, User $assignedOfficer, User $assigner = null)
    {
        $this->incident = $incident;
        $this->assignedOfficer = $assignedOfficer;
        $this->assigner = $assigner;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Incident Assigned - ' . $this->incident->reference_number,
        );
    }

    public function content(): Content
    {
        Log::info("Sending assignment email for incident {$this->incident->reference_number} to officer {$this->assignedOfficer->email}");

        return new Content(
            view: 'emails.incident-assigned',
            with: [
                'incident' => $this->incident,
                'assignedOfficer' => $this->assignedOfficer,
                'assigner' => $this->assigner,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function failed(\Exception $e)
    {
        Log::error("Failed to send assignment email for incident {$this->incident->reference_number}: " . $e->getMessage());
    }
}