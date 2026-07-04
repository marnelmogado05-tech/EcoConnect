<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $storagePath = storage_path('app/firebase-auth.json');
        $factory = (new Factory)->withServiceAccount($storagePath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(['title' => $title, 'body' => $body])
            ->withData($data);

        try {
            $this->messaging->send($message);
        } catch (\Exception $e) {
            // Handle the error, e.g., log it
            \Log::error('Failed to send Firebase notification: ' . $e->getMessage());
        }
    }
}
