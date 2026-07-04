<?php

namespace App\Events;

use App\Models\Incident;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Incident $incident;
    public string $oldStatus;
    public string $newStatus;

    public function __construct(Incident $incident, string $oldStatus, string $newStatus)
    {
        $this->incident = $incident;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('incident.' . $this->incident->id),
        ];
    }
}
