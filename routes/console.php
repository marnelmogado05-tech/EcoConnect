<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| This schedule previously lived in app/Console/Kernel.php, which Laravel 12's
| application skeleton never loads — so incidents:auto-reject had never run once.
|
| Requires a scheduler process: `php artisan schedule:work` locally, or a cron entry
| running `php artisan schedule:run` every minute in production.
|
*/

Schedule::command('incidents:auto-reject')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        Log::error('Auto-reject incidents command failed');
    });
