<?php

namespace App\Listeners;

use App\Enums\UserStatus;
use App\Events\IncidentReported;
use App\Jobs\SendPushNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAdminIncidentNotification implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(IncidentReported $event): void
    {
        $incident = $event->incident;

        Log::info('SendAdminIncidentNotification: New incident reported', [
            'incident_id' => $incident->id,
            'incident_type' => $incident->incident_type?->value,
            'reporter' => $incident->user?->name ?: 'Unknown',
        ]);

        try {
            // Get all admins, police, and BFP staff who should be notified
            $admins = User::whereIn('role', ['admin', 'police', 'bfp'])
                ->where('status', UserStatus::Active)
                ->get();

            if ($admins->isEmpty()) {
                Log::warning('No active admin/police/bfp users found to notify');
                return;
            }

            // Incidents have no `location` column, so the old message always read
            // "reported in Unknown Location by Unknown". The reference number is the
            // identifier staff actually work from.
            $title = '🚨 New Incident Reported';
            $reporter = $incident->user?->name ?: 'an anonymous reporter';
            $body = "{$incident->incident_type?->value} reported by {$reporter} ({$incident->reference_number})";
            $data = [
                'incident_id' => $incident->id,
                'incident_type' => $incident->incident_type?->value,
                'reporter_id' => $incident->user_id,
                'status' => $incident->status?->value,
                'type' => 'new_incident',
                'reference_number' => $incident->reference_number,
            ];

            // Send notification to each admin/staff
            foreach ($admins as $admin) {
                SendPushNotification::dispatch(
                    $admin->id,
                    $title,
                    $body,
                    $data
                );

                Log::info('Incident notification dispatched', [
                    'admin_id' => $admin->id,
                    'admin_role' => $admin->role,
                    'incident_id' => $incident->id,
                ]);
            }

            Log::info('SendAdminIncidentNotification: Notifications sent', [
                'incident_id' => $incident->id,
                'notification_count' => $admins->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('SendAdminIncidentNotification: Error sending notifications', [
                'incident_id' => $incident->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
