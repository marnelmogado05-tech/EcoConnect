<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\IncidentFollowup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $incident;
    public $officer;
    public $followup;

    /**
     * Create a new message instance.
     */
    public function __construct(Incident $incident, User $officer, IncidentFollowup $followup)
    {
        $this->incident = $incident;
        $this->officer = $officer;
        $this->followup = $followup;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Follow-up on Incident - ' . $this->incident->reference_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.incident-followup',
            with: [
                'incident' => $this->incident,
                'officer' => $this->officer,
                'followup' => $this->followup,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
