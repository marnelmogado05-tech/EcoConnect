<?php

namespace App\Events;

use App\Enums\IncidentStatus;
use App\Models\Incident;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncidentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Incident $incident,
        public ?IncidentStatus $oldStatus,
        public ?IncidentStatus $newStatus,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('incident.' . $this->incident->id),
        ];
    }
}
