<?php

namespace App\Listeners;

use App\Events\IncidentStatusChanged;
use App\Jobs\SendPushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendIncidentStatusNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(IncidentStatusChanged $event): void
    {
        $incident = $event->incident;
        $user = $incident->user;

        $title = 'Incident Status Updated';
        $body = "Your incident report status has been changed to: {$incident->status?->label()}";
        $data = [
            'incident_id' => $incident->id,
            'status' => $incident->status?->value,
            'type' => 'incident_status_change',
        ];

        SendPushNotification::dispatch($user->id, $title, $body, $data);
    }
}
