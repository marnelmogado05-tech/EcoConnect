<?php

namespace App\Listeners;

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
            'incident_type' => $incident->incident_type,
            'reporter' => $incident->user?->name ?? 'Unknown',
        ]);

        try {
            // Get all admins, police, and BFP staff who should be notified
            $admins = User::whereIn('role', ['admin', 'police', 'bfp'])
                ->where('status', 'active')
                ->get();

            if ($admins->isEmpty()) {
                Log::warning('No active admin/police/bfp users found to notify');
                return;
            }

            $title = '🚨 New Incident Reported';
            $location = $incident->location ?? 'Unknown Location';
            $reporter = $incident->user?->name ?? 'Unknown';
            $body = "{$incident->incident_type} reported in {$location} by {$reporter}";
            $data = [
                'incident_id' => $incident->id,
                'incident_type' => $incident->incident_type,
                'reporter_id' => $incident->user_id,
                'status' => $incident->status,
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
