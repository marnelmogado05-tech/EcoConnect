<?php

namespace App\Observers;

use App\Events\IncidentStatusChanged;
use App\Models\Incident;

class IncidentObserver
{
    /**
     * Assign a reference number before the row is written.
     */
    public function creating(Incident $incident): void
    {
        if (blank($incident->reference_number)) {
            $incident->reference_number = Incident::generateReferenceNumber();
        }
    }

    /**
     * Announce a status change once the row has actually been updated.
     *
     * This replaces a boot() hook that called static::updated() from inside
     * static::updating(). That registered an additional listener on every save: the
     * listeners accumulated for the lifetime of the process, each one closing over the
     * status values from the save that created it, and every one of them fired for every
     * incident updated afterwards. On a long-running queue worker the effect compounded
     * indefinitely and the notifications described transitions that never happened.
     */
    public function updated(Incident $incident): void
    {
        if (! $incident->wasChanged('status')) {
            return;
        }

        IncidentStatusChanged::dispatch(
            $incident,
            (string) $incident->getOriginal('status'),
            (string) $incident->status,
        );
    }
}
