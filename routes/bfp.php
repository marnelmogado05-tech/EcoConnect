<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BFP\BFPDashboardController;
use App\Http\Controllers\BFP\BFPIncidentController;


Route::middleware('auth', 'bfp')->prefix('bfp')->group(function () {
    Route::get('/dashboard', [BFPDashboardController::class, 'index'])->name('bfp.dashboard');

    Route::get('/incidents', [BFPIncidentController::class, 'index'])->name('bfp.incidents');
    Route::put('/incidents/taken/{id}', [BFPIncidentController::class, 'taken'])->name('bfp.incidents.taken');

    Route::get('/incidents/export', [BFPIncidentController::class, 'export'])->name('bfp.incidents.export');
    Route::get('/incidents/print', [BFPIncidentController::class, 'print'])->name('bfp.incidents.print');
});
