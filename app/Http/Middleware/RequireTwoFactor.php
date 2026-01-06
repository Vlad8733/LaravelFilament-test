<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /**
     * Routes that should be accessible without 2FA verification
     */
    protected array $except = [
        'two-factor.challenge',
        'two-factor.verify',
        'logout',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if not authenticated
        if (! $user) {
            return $next($request);
        }

        // Skip if 2FA is not enabled for this user
        if (! $user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Skip if already verified in this session
        if ($request->session()->get('2fa:verified')) {
            return $next($request);
        }

        // Skip exempt routes
        if ($this->isExceptRoute($request)) {
            return $next($request);
        }

        // Store intended URL and redirect to 2FA challenge
        $request->session()->put('2fa:user:id', $user->id);

        auth()->logout();

        return redirect()->route('two-factor.challenge');
    }

    /**
     * Check if current route is exempt from 2FA
     */
    protected function isExceptRoute(Request $request): bool
    {
        foreach ($this->except as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }
}
