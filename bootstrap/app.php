<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserHasRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // A single role middleware, parameterised: 'role:police,bfp' admits either.
        $middleware->alias([
            'role' => EnsureUserHasRole::class,

            // Retained so existing route definitions keep working; each is the
            // parameterised middleware bound to one role.
            'admin' => EnsureUserHasRole::class.':admin',
            'police' => EnsureUserHasRole::class.':police',
            'bfp' => EnsureUserHasRole::class.':bfp',
            'user' => EnsureUserHasRole::class.':user',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
