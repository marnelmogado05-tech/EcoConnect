<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Incident Grace Period Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic actions for pending incidents that exceed certain time limits.
    |
    */

    'grace_period' => [
        /*
        |--------------------------------------------------------------------------
        | Auto-Reject Pending Incidents
        |--------------------------------------------------------------------------
        |
        | Automatically reject pending incidents after a specified number of days.
        | Set to null to disable auto-rejection.
        |
        */
        'auto_reject_days' => env('INCIDENT_AUTO_REJECT_DAYS', 30),

        /*
        |--------------------------------------------------------------------------
        | Auto-Reject Reason
        |--------------------------------------------------------------------------
        |
        | The default reason used when automatically rejecting old pending incidents.
        |
        */
        'auto_reject_reason' => env('INCIDENT_AUTO_REJECT_REASON', 'Automatically rejected due to exceeding the grace period for pending incidents.'),

        /*
        |--------------------------------------------------------------------------
        | Notification Settings
        |--------------------------------------------------------------------------
        |
        | Whether to send notifications when incidents are auto-rejected.
        |
        */
        'send_notifications' => env('INCIDENT_AUTO_REJECT_NOTIFICATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Incident Processing Settings
    |--------------------------------------------------------------------------
    |
    | General settings for incident processing and management.
    |
    */

    'processing' => [
        /*
        |--------------------------------------------------------------------------
        | Batch Size for Auto-Processing
        |--------------------------------------------------------------------------
        |
        | Number of incidents to process in each batch when running automated tasks.
        |
        */
        'batch_size' => env('INCIDENT_BATCH_SIZE', 50),

        /*
        |--------------------------------------------------------------------------
        | Processing Frequency
        |--------------------------------------------------------------------------
        |
        | How often to run automated incident processing (in minutes).
        | This affects how frequently the auto-reject command should be run.
        |
        */
        'frequency_minutes' => env('INCIDENT_PROCESSING_FREQUENCY', 1440), // 24 hours
    ],

];
