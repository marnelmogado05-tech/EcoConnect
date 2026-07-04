<?php

namespace App\Services;

use App\Models\FcmToken;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class WebPushNotificationService
{
    protected $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ]
        ];

        $this->webPush = new WebPush($auth);
    }

    /**
     * Send a web push notification to a user
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        $tokens = FcmToken::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        if ($tokens->isEmpty()) {
            Log::warning('No web push tokens found for user', ['user_id' => $userId]);
            return false;
        }

        $successCount = 0;

        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        Log::info('Web push notifications sent', [
            'user_id' => $userId,
            'total' => $tokens->count(),
            'sent' => $successCount,
        ]);

        return $successCount > 0;
    }

    /**
     * Send notification to a specific web push subscription token
     */
    public function sendToToken($token, $title, $body, $data = [])
    {
        try {
            // Parse the subscription token (it's stored as JSON)
            $subscriptionData = is_string($token->token)
                ? json_decode($token->token, true)
                : $token->token;

            if (!isset($subscriptionData['endpoint'])) {
                Log::warning('Invalid subscription data', ['token_id' => $token->id]);
                $token->delete();
                return false;
            }

            // Create Minishlink subscription object
            $subscription = Subscription::create([
                'endpoint' => $subscriptionData['endpoint'],
                'publicKey' => $subscriptionData['keys']['p256dh'] ?? null,
                'authToken' => $subscriptionData['keys']['auth'] ?? null,
            ]);

            // Prepare payload
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => asset('logo.png'),
                'badge' => asset('logo.png'),
                'data' => $data,
            ]);

            // Send the notification
            $this->webPush->sendOneNotification($subscription, $payload);

            Log::info('Web push notification sent successfully', [
                'endpoint' => substr($subscriptionData['endpoint'], 0, 50) . '...',
                'title' => $title,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send web push notification', [
                'token_id' => $token->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // Mark token as inactive if there's an error
            if (isset($token) && $token->exists) {
                $token->update(['is_active' => false]);
            }

            return false;
        }
    }

    /**
     * Send notification to multiple users
     */
    public function sendToMultipleUsers($userIds, $title, $body, $data = [])
    {
        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = $this->sendToUser($userId, $title, $body, $data);
        }
        return $results;
    }

    /**
     * Flush and handle any pending requests
     */
    public function flush()
    {
        try {
            foreach ($this->webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri();
                if ($report->isSuccess()) {
                    Log::info('Web push flush success', ['endpoint' => substr($endpoint, 0, 50)]);
                } else {
                    Log::error('Web push flush failed', [
                        'endpoint' => substr($endpoint, 0, 50),
                        'error' => $report->getReason(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Web push flush error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Test the web push service
     */
    public function testConnection()
    {
        try {
            // Verify VAPID keys are configured
            if (!env('VAPID_PUBLIC_KEY') || !env('VAPID_PRIVATE_KEY')) {
                Log::error('VAPID keys not configured');
                return false;
            }

            Log::info('Web push service test successful');
            return true;
        } catch (\Exception $e) {
            Log::error('Web push service test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
