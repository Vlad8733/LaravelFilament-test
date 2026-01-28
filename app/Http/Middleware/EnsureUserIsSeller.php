<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Access denied.');
        }

        if (! $user->isSeller() && ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied.'], 403);
            }

            abort(403, 'Access denied. Seller privileges required.');
        }

        return $next($request);
    }
}
