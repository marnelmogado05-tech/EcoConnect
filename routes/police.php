<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Police\PoliceDashboardController;
use App\Http\Controllers\Police\PoliceIncidentController;


Route::middleware('auth', 'police')->prefix('police')->group(function () {
    Route::get('/dashboard', [PoliceDashboardController::class, 'index'])->name('police.dashboard');

    Route::get('/incidents', [PoliceIncidentController::class, 'index'])->name('police.incidents');
    // Route::put('/incidents/assign/{id}', [PoliceIncidentController::class, 'assign'])->name('police.incidents.assign');
    // Route::put('/incidents/resolve/{id}', [PoliceIncidentController::class, 'resolve'])->name('police.incidents.resolve');
    Route::put('/incidents/taken/{id}', [PoliceIncidentController::class, 'taken'])->name('police.incidents.taken');

    Route::get('/incidents/export', [PoliceIncidentController::class, 'export'])->name('police.incidents.export');
    Route::get('/incidents/print', [PoliceIncidentController::class, 'print'])->name('police.incidents.print');
});
