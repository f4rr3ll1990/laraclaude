<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow the request through only when the authenticated user is an admin.
 *
 * Stacks after `auth:sanctum`, so a missing/expired token yields 401 there and
 * a valid token belonging to a non-admin user yields 403 here.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
