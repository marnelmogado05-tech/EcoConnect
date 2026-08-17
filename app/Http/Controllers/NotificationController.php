<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Manages a browser's push subscription for the signed-in user.
 *
 * These were named around FCM tokens; nothing here has ever been an FCM token. The
 * payload is the JSON document the browser's PushManager hands back.
 */
class NotificationController extends Controller
{
    /**
     * Record a subscription for the current user and device.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscription' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $subscription = PushSubscription::updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'subscription' => $validated['subscription'],
                ],
                [
                    'device_name' => $validated['device_name'] ?? 'Unknown device',
                    'is_active' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notifications enabled on this device.',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save push subscription', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'We could not enable notifications on this device.',
            ], 500);
        }
    }

    /**
     * Forget one subscription.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscription' => ['required', 'string'],
        ]);

        try {
            PushSubscription::where('user_id', $request->user()->id)
                ->where('subscription', $validated['subscription'])
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifications disabled on this device.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove push subscription', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'We could not disable notifications on this device.',
            ], 500);
        }
    }

    /**
     * Forget every subscription for the current user.
     */
    public function unsubscribeAll(Request $request): JsonResponse
    {
        try {
            PushSubscription::where('user_id', $request->user()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifications disabled on all devices.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove push subscriptions', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'We could not disable notifications.',
            ], 500);
        }
    }

    /**
     * Whether the current user has any active subscription.
     */
    public function status(Request $request): JsonResponse
    {
        $count = PushSubscription::activeFor($request->user()->id)->count();

        return response()->json([
            'success' => true,
            'enabled' => $count > 0,
            'subscription_count' => $count,
        ]);
    }
}
