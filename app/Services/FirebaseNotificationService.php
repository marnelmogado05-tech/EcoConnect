<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials'));

        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send a notification to a user via FCM
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        $tokens = FcmToken::getActiveTokensForUser($userId);

        if (empty($tokens)) {
            Log::warning('No FCM tokens found for user', ['user_id' => $userId]);
            return false;
        }

        try {
            foreach ($tokens as $token) {
                $this->sendToToken($token, $title, $body, $data);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send FCM notification', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send a notification to a specific FCM token
     */
    public function sendToToken($token, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            if (!empty($data)) {
                $message = $message->withData($data);
            }

            $this->messaging->send($message);

            Log::info('FCM notification sent successfully', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send FCM message to token', [
                'token' => substr($token, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);

            // Remove invalid token
            FcmToken::where('token', $token)->delete();

            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultipleUsers($userIds, $title, $body, $data = [])
    {
        foreach ($userIds as $userId) {
            $this->sendToUser($userId, $title, $body, $data);
        }
    }

    /**
     * Test notification service
     */
    public function testConnection()
    {
        try {
            // This will verify the Firebase configuration is correct
            $this->messaging->projectId();
            Log::info('Firebase connection test successful');
            return true;
        } catch (\Exception $e) {
            Log::error('Firebase connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
