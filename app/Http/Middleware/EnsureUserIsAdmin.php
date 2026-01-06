<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            // Если не админ — редирект на главную или 403
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied.'], 403);
            }

            abort(403, 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
