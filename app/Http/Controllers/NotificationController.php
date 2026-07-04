<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Save FCM token for a user
     */
    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        try {
            $token = FcmToken::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'token' => $request->token,
                ],
                [
                    'device_name' => $request->device_name ?? 'Unknown Device',
                    'is_active' => true,
                ]
            );

            Log::info('FCM token saved', ['user_id' => auth()->id(), 'device' => $request->device_name]);

            return response()->json([
                'success' => true,
                'message' => 'Notification token saved successfully',
                'token_id' => $token->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save FCM token', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save notification token',
            ], 500);
        }
    }

    /**
     * Remove specific FCM token
     */
    public function removeFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            FcmToken::where('user_id', auth()->id())
                ->where('token', $request->token)
                ->delete();

            Log::info('FCM token removed', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'Notification token removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove FCM token', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove notification token',
            ], 500);
        }
    }

    /**
     * Remove all FCM tokens for current user
     */
    public function removeAllTokens()
    {
        try {
            FcmToken::where('user_id', auth()->id())->delete();

            Log::info('All FCM tokens removed for user', ['user_id' => auth()->id()]);

            return response()->json([
                'success' => true,
                'message' => 'All notification tokens removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to remove all FCM tokens', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove notification tokens',
            ], 500);
        }
    }

    /**
     * Get notification status for current user
     */
    public function getNotificationStatus()
    {
        try {
            $tokenCount = FcmToken::where('user_id', auth()->id())
                ->where('is_active', true)
                ->count();

            return response()->json([
                'success' => true,
                'enabled' => $tokenCount > 0,
                'token_count' => $tokenCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get notification status', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification status',
            ], 500);
        }
    }
}
