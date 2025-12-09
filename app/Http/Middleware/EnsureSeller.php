<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSeller
{
    /**
     * Allow only users with is_seller = true.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->is_seller) {
            abort(403);
        }

        return $next($request);
    }
}
