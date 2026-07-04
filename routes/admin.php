<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminIncidentController;
use App\Http\Controllers\Admin\AdminCitizenController;
use App\Http\Controllers\Admin\AdminPoliceController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminBFPController;
use App\Http\Controllers\User\IncidentFollowupController;

Route::middleware('auth', 'admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics');

    Route::get('/backup', [AdminBackupController::class, 'index'])->name('admin.backup');
    Route::post('/backup', [AdminBackupController::class, 'create'])->name('admin.backup.create');
    Route::get('/backup/download/{filename}', [AdminBackupController::class, 'download'])->name('admin.backup.download');
    Route::delete('/backup/delete/{filename}', [AdminBackupController::class, 'delete'])->name('admin.backup.delete');

    Route::get('/incidents', [AdminIncidentController::class, 'index'])->name('admin.incidents');
    Route::put('/incidents/assign/{id}', [AdminIncidentController::class, 'assign'])->name('admin.incidents.assign');
    Route::put('/incidents/reject/{id}', [AdminIncidentController::class, 'reject'])->name('admin.incidents.reject');
    Route::put('/incidents/resolve/{id}', [AdminIncidentController::class, 'resolve'])->name('admin.incidents.resolve');
    Route::post('/incidents/{incident}/respond', [IncidentFollowupController::class, 'respond'])->name('admin.incidents.respond');

    Route::get('/incidents/export', [AdminIncidentController::class, 'export'])->name('admin.incidents.export');
    Route::get('/incidents/print', [AdminIncidentController::class, 'print'])->name('admin.incidents.print');

    Route::get('/citizens', [AdminCitizenController::class, 'index'])->name('admin.citizens');
    Route::get('/citizens/{id}', [AdminCitizenController::class, 'show'])->name('admin.citizens.show');
    Route::put('/citizens/{id}/status', [AdminCitizenController::class, 'updateStatus'])->name('admin.citizens.update-status');
    Route::put('/citizens/{id}/reset-password', [AdminCitizenController::class, 'resetPassword'])->name('admin.citizens.reset-password');

    Route::get('/manage-police', [AdminPoliceController::class, 'index'])->name('admin.police');
    Route::post('/manage-police', [AdminPoliceController::class, 'store'])->name('admin.police.store');
    Route::put('/manage-police/{id}', [AdminPoliceController::class, 'update'])->name('admin.police.update-profile');
    Route::get('/manage-police/{id}', [AdminPoliceController::class, 'show'])->name('admin.police.show');
    Route::put('/manage-police/{id}/status', [AdminPoliceController::class, 'updateStatus'])->name('admin.police.update-status');

    Route::get('/manage-bfp', [AdminBFPController::class, 'index'])->name('admin.bfp');
    Route::post('/manage-bfp', [AdminBFPController::class, 'store'])->name('admin.bfp.store');
    Route::put('/manage-bfp/{id}', [AdminBFPController::class, 'update'])->name('admin.bfp.update-profile');
    Route::get('/manage-bfp/{id}', [AdminBFPController::class, 'show'])->name('admin.bfp.show');
    Route::put('/manage-bfp/{id}/status', [AdminBFPController::class, 'updateStatus'])->name('admin.bfp.update-status');
});
