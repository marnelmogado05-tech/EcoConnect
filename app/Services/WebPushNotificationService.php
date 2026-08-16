<?php

namespace App\Services;

use App\Models\FcmToken;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class WebPushNotificationService
{
    protected ?WebPush $webPush = null;

    /**
     * Build the client on demand.
     *
     * This used to happen in the constructor. WebPush validates the VAPID key pair as it
     * is constructed and throws when the keys are missing or malformed, so on any
     * environment where push had not been configured the mere act of resolving this
     * service threw — which meant every incident status change failed, because changing a
     * status notifies the reporter. Push being unconfigured should disable push, not
     * block the workflow it is attached to.
     *
     * Reading from config rather than env() also keeps this working under config:cache,
     * where env() returns null and push silently stopped in production.
     */
    protected function client(): ?WebPush
    {
        if ($this->webPush instanceof WebPush) {
            return $this->webPush;
        }

        $publicKey = config('firebase.vapid_public_key');
        $privateKey = config('firebase.vapid_private_key');

        if (blank($publicKey) || blank($privateKey)) {
            Log::warning('Web push is not configured (VAPID keys absent); skipping notification.');

            return null;
        }

        return $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);
    }

    /**
     * Send a web push notification to a user
     */
    public function sendToUser($userId, $title, $body, $data = [])
    {
        if (! $this->client()) {
            return false;
        }

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
        $client = $this->client();

        if (! $client) {
            return false;
        }

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
            $client->sendOneNotification($subscription, $payload);

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
        $client = $this->client();

        if (! $client) {
            return;
        }

        try {
            foreach ($client->flush() as $report) {
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
            if (! $this->client()) {
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
