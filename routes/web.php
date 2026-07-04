<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserIncidentController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\TrackIncidentController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\IncidentFollowupController;

// Debug route - test VAPID key loading and web push status
Route::get('/debug-vapid', function () {
    $vapidPublic = env('VAPID_PUBLIC_KEY');
    $vapidPrivate = env('VAPID_PRIVATE_KEY');
    
    return response()->json([
        'vapid_public_key_present' => !empty($vapidPublic),
        'vapid_public_key_length' => strlen($vapidPublic ?? ''),
        'vapid_private_key_present' => !empty($vapidPrivate),
        'vapid_private_key_length' => strlen($vapidPrivate ?? ''),
        'app_env' => env('APP_ENV'),
        'app_url' => env('APP_URL'),
        'web_push_library' => class_exists(\Minishlink\WebPush\WebPush::class) ? 'installed' : 'missing',
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'user_subscriptions' => auth()->check() ? \App\Models\FcmToken::where('user_id', auth()->id())->where('is_active', true)->count() : 0,
    ]);
});

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/otp-verify', [RegisteredUserController::class, 'showOtpForm'])->name('otp.verify');

Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

Route::post('/otp-verify', [RegisteredUserController::class, 'verifyOtp'])->name('otp.verify.submit');

Route::post('/otp-resend', [RegisteredUserController::class, 'resendOtp'])->name('otp.resend');

Route::get('/hotspots', function () {
    return view('hotspots');
})->name('hotspots');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');

Route::post('/otp-verify', [RegisteredUserController::class, 'verifyOtp'])->name('otp.verify.submit');

Route::post('/otp-resend', [RegisteredUserController::class, 'resendOtp'])->name('otp.resend');

Route::get('/api/municipalities/{municipality}/barangays', function ($municipalityId) {
    $barangays = \App\Models\Barangay::where('municipality_id', $municipalityId)->get();
    return response()->json($barangays);
});

Route::get('/track-incident', [TrackIncidentController::class, 'create'])->name('track.index');
Route::post('/track-incident', [TrackIncidentController::class, 'search'])->name('track.search');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile/id-card/download', [ProfileController::class, 'downloadIdCard'])->name('profile.id-card.download');
    Route::get('/profile/id-card/view', [ProfileController::class, 'viewIdCard'])->name('profile.id-card.view');
});

require __DIR__.'/auth.php';

require __DIR__.'/admin.php';

require __DIR__.'/police.php';

require __DIR__.'/bfp.php';

Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::get('/new-report', [UserIncidentController::class, 'create'])->name('report-incident');
    Route::post('/new-report', [UserIncidentController::class, 'store'])->name('incidents.store');
    Route::put('/new-report/{id}', [UserIncidentController::class, 'update'])->name('incidents.update');

    Route::get('/incidents', [UserIncidentController::class, 'index'])->name('incidents');
    Route::get('/incidents/{incident}', [UserIncidentController::class, 'show'])->name('incidents.show');
});

Route::post('/track-incident/followup', [TrackIncidentController::class, 'addFollowup'])->name('track.followup');

Route::middleware(['auth'])->group(function () {
    Route::post('/incidents/{incident}/followups', [IncidentFollowupController::class, 'store'])->name('followups.store');
    Route::post('/incidents/{incident}/respond', [IncidentFollowupController::class, 'respond'])->name('followups.respond');
    Route::get('/incidents/{incident}/followups', [IncidentFollowupController::class, 'show'])->name('followups.show');

    // Notification Routes
    Route::post('/api/notifications/save-token', [NotificationController::class, 'saveFcmToken'])->name('notifications.save-token');
    Route::post('/api/notifications/remove-token', [NotificationController::class, 'removeFcmToken'])->name('notifications.remove-token');
    Route::post('/api/notifications/remove-all', [NotificationController::class, 'removeAllTokens'])->name('notifications.remove-all');
    Route::get('/api/notifications/status', [NotificationController::class, 'getNotificationStatus'])->name('notifications.status');
});

Route::get('/test-notification', function() {
    if (!auth()->check()) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    
    $userId = auth()->id();
    
    // Check if user has any subscriptions
    $tokenCount = \App\Models\FcmToken::where('user_id', $userId)
        ->where('is_active', true)
        ->count();
    
    if ($tokenCount === 0) {
        return response()->json([
            'success' => false,
            'error' => 'No active push subscriptions found',
            'message' => 'Enable push notifications in the app first',
            'user_id' => $userId
        ], 400);
    }

    \App\Jobs\SendPushNotification::dispatch(
        $userId,
        '🔔 Test Notification',
        'This is a test web push notification sent at ' . date('H:i:s'),
        [
            'test' => true, 
            'timestamp' => now(),
            'type' => 'test'
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Test notification dispatched to ' . $tokenCount . ' device(s)',
        'user_id' => $userId,
        'subscriptions' => $tokenCount
    ]);
});
