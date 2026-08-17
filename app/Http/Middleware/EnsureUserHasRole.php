<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to one or more roles.
 *
 * Replaces four near-identical middleware classes that all failed open:
 *
 *     if (Auth::check()) {
 *         if (! Auth::user()->isAdmin()) { return redirect()->route('login'); }
 *     }
 *     return $next($request);          // anonymous requests landed here
 *
 * An unauthenticated request skipped the role check entirely and was passed straight
 * through. Nothing was exposed in practice only because every route group also listed
 * 'auth' ahead of the role alias — the protection depended on callers remembering to
 * pair them. This version rejects unless the request is both authenticated and carries
 * a permitted role.
 */
class EnsureUserHasRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $request->expectsJson()
                ? abort(401)
                : redirect()->guest(route('login'));
        }

        // Roles arrive from the route definition as strings; the model casts to an enum.
        if (! in_array($user->role?->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
