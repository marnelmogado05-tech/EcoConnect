<?php

namespace App\Providers;

use App\Events\IncidentStatusChanged;
use App\Events\IncidentReported;
use App\Events\UserRegistered;
use App\Listeners\SendIncidentStatusNotification;
use App\Listeners\SendAdminIncidentNotification;
use App\Listeners\SendNewUserNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        IncidentStatusChanged::class => [
            SendIncidentStatusNotification::class,
        ],
        IncidentReported::class => [
            SendAdminIncidentNotification::class,
        ],
        UserRegistered::class => [
            SendNewUserNotification::class,
        ],
    ];

    /**
     * Enable the listeners for the application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
