<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    /**
     * Laravel 12's skeleton omits this trait, so $this->authorize() is unavailable
     * until it is added. Its absence is part of why access decisions ended up
     * scattered across controllers as ad-hoc query scopes, or missing entirely.
     */
    use AuthorizesRequests;
}
