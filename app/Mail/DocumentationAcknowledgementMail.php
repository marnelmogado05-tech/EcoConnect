<?php

namespace App\Mail;

use App\Models\Incident;
use App\Models\IncidentAcknowledgement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DocumentationAcknowledgementMail extends Mailable
{
    use SerializesModels;

    public $incident;
    public $officer;
    public $acknowledgement;

    public function __construct(Incident $incident, User $officer, IncidentAcknowledgement $acknowledgement)
    {
        $this->incident = $incident;
        $this->officer = $officer;
        $this->acknowledgement = $acknowledgement;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Documentation Acknowledgement - Incident ' . $this->incident->reference_number,
        );
    }

    public function content(): Content
    {
        Log::info("Sending documentation acknowledgement email for incident {$this->incident->reference_number} to {$this->officer->role} {$this->officer->email}");

        return new Content(
            view: 'emails.documentation-acknowledgement',
            with: [
                'incident' => $this->incident,
                'officer' => $this->officer,
                'acknowledgement' => $this->acknowledgement,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
