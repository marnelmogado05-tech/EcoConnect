<?php

namespace App\Providers;

use App\Services\LocationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // This binding previously named LocationService with no import, so it resolved
        // as App\Providers\LocationService — a class that does not exist. The container
        // held a binding for a phantom, and every app(LocationService::class) built a
        // fresh instance instead of the intended singleton.
        $this->app->singleton(LocationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
