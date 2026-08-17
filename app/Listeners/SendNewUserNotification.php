<?php

namespace App\Listeners;

use App\Enums\UserStatus;
use App\Events\UserRegistered;
use App\Jobs\SendPushNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewUserNotification implements ShouldQueue
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
    public function handle(UserRegistered $event): void
    {
        // Send notification to all staff about new registration
        $staff = User::whereIn('role', ['admin', 'police', 'bfp'])
            ->where('status', UserStatus::Active)
            ->get();

        if ($staff->isNotEmpty()) {
            $newUser = $event->user;
            $title = '👤 New User Registration';
            $body = "{$newUser->name} has registered on EcoConnect";
            $data = [
                'user_id' => $newUser->id,
                'user_email' => $newUser->email,
                'type' => 'new_user_registration',
            ];

            foreach ($staff as $staffMember) {
                SendPushNotification::dispatch($staffMember->id, $title, $body, $data);
            }
        }
    }
}
