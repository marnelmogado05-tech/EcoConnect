<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Incident;
use App\Mail\IncidentRejectedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoRejectPendingIncidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incidents:auto-reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reject pending incidents that have exceeded the grace period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gracePeriodDays = config('incidents.grace_period.auto_reject_days', 30);
        $autoRejectReason = config('incidents.grace_period.auto_reject_reason');
        $sendNotifications = config('incidents.grace_period.send_notifications', true);
        $batchSize = 50;

        if (!$gracePeriodDays) {
            $this->error('Grace period is not configured. Please set INCIDENT_AUTO_REJECT_DAYS in your .env file.');
            return Command::FAILURE;
        }

        $cutoffDate = Carbon::now()->subDays($gracePeriodDays);

        $this->info("Finding pending incidents older than {$gracePeriodDays} days (before {$cutoffDate->format('Y-m-d H:i:s')})");

        // Get count first to avoid loading all records into memory
        $totalPendingIncidents = Incident::where('status', 'Pending')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($totalPendingIncidents === 0) {
            $this->info('No pending incidents found that exceed the grace period.');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalPendingIncidents} pending incidents to process:");

        $bar = $this->output->createProgressBar($totalPendingIncidents);
        $bar->start();

        $processed = 0;
        $errors = 0;

        // Use chunk() instead of skip/take - much more memory efficient
        Incident::where('status', 'Pending')
            ->where('created_at', '<', $cutoffDate)
            ->select('id', 'reference_number', 'status', 'created_at', 'user_id')
            ->with('user:id,email,fname,lname')
            ->chunk($batchSize, function ($incidents) use (
                &$processed,
                &$errors,
                $autoRejectReason,
                $sendNotifications,
                $bar
            ) {
                foreach ($incidents as $incident) {
                    try {
                        $this->line("Processing incident {$incident->reference_number} (created: {$incident->created_at->format('Y-m-d')})");

                        // Update the incident
                        $incident->update([
                            'status' => 'Rejected',
                            'rejection_reason' => $autoRejectReason,
                            'assigned_to' => null,
                        ]);

                        // Queue notification if enabled (async instead of blocking)
                        if ($sendNotifications && $incident->user) {
                            try {
                                Mail::to($incident->user->email)
                                    ->queue(new IncidentRejectedMail($incident, $incident->user));
                            } catch (\Exception $e) {
                                Log::error("Failed to queue auto-reject notification for incident {$incident->id}: " . $e->getMessage());
                            }
                        }

                        Log::info("Auto-rejected incident {$incident->reference_number} due to grace period expiration");

                        $processed++;

                    } catch (\Exception $e) {
                        $errors++;
                        Log::error("Failed to auto-reject incident {$incident->id}: " . $e->getMessage());
                        $this->error("Error processing incident {$incident->reference_number}: " . $e->getMessage());
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("Successfully processed {$processed} incidents");
        if ($errors > 0) {
            $this->warn("{$errors} incidents had errors during processing");
        }
        return Command::SUCCESS;
    }
}
