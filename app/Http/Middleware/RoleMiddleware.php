<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:admin') or 'role:admin|editor'
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $allowed = array_map('trim', preg_split('/[|,]/', $roles));

        if (! in_array($user->role, $allowed, true)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
