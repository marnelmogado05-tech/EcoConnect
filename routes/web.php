<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\UserIncidentController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\TrackIncidentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\MediaEvidenceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\IncidentFollowupController;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/otp-verify', [RegisteredUserController::class, 'showOtpForm'])->name('otp.verify');

// Throttled: a six-digit code is a one-million guess space, and these routes previously
// accepted unlimited attempts. The per-attempt counter in the controller caps a single
// code; this caps how fast an attacker can cycle through fresh ones.
Route::post('/otp-verify', [RegisteredUserController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')
    ->name('otp.verify.submit');

Route::post('/otp-resend', [RegisteredUserController::class, 'resendOtp'])
    ->middleware('throttle:3,1')
    ->name('otp.resend');

Route::get('/hotspots', function () {
    return view('hotspots');
})->name('hotspots');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// The register, otp-verify and otp-resend routes were each declared a second time here.
// Laravel keeps the last definition for a given method and URI, so these silently
// replaced the ones above — which would have discarded the throttling added to them,
// and shadowed the guest-protected POST /register declared in routes/auth.php.

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

    // ID cards live on the private disk and are streamed only after UserPolicy
    // authorises the viewer, so there is no public URL that reaches one.
    Route::get('/profile/id-card/download', [IdCardController::class, 'download'])->name('profile.id-card.download');
    Route::get('/profile/id-card/view', [IdCardController::class, 'show'])->name('profile.id-card.view');
    Route::get('/users/{user}/id-card', [IdCardController::class, 'show'])->name('users.id-card');

    // Evidence is streamed from the private disk after IncidentPolicy::viewMedia.
    Route::get('/media/{media}', [MediaEvidenceController::class, 'show'])->name('media.show');
});

require __DIR__.'/auth.php';

require __DIR__.'/admin.php';

require __DIR__.'/police.php';

require __DIR__.'/bfp.php';

Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::get('/new-report', [UserIncidentController::class, 'create'])->name('report-incident');
    Route::post('/new-report', [UserIncidentController::class, 'store'])->name('incidents.store');

    Route::get('/incidents', [UserIncidentController::class, 'index'])->name('incidents');
    Route::get('/incidents/{incident}', [UserIncidentController::class, 'show'])->name('incidents.show');
});

Route::post('/track-incident/followup', [TrackIncidentController::class, 'addFollowup'])->name('track.followup');

Route::middleware(['auth'])->group(function () {
    Route::post('/incidents/{incident}/followups', [IncidentFollowupController::class, 'store'])->name('followups.store');
    Route::post('/incidents/{incident}/respond', [IncidentFollowupController::class, 'respond'])->name('followups.respond');
    Route::get('/incidents/{incident}/followups', [IncidentFollowupController::class, 'show'])->name('followups.show');

    // Browser push subscriptions. Named for what they are; the previous names described
    // FCM tokens, which this application has never used.
    Route::post('/api/notifications/subscribe', [NotificationController::class, 'subscribe'])->name('notifications.subscribe');
    Route::post('/api/notifications/unsubscribe', [NotificationController::class, 'unsubscribe'])->name('notifications.unsubscribe');
    Route::post('/api/notifications/unsubscribe-all', [NotificationController::class, 'unsubscribeAll'])->name('notifications.unsubscribe-all');
    Route::get('/api/notifications/status', [NotificationController::class, 'status'])->name('notifications.status');
});
