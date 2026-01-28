<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    protected array $except = [
        'two-factor.challenge',
        'two-factor.verify',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        if ($request->session()->get('2fa:verified')) {
            return $next($request);
        }

        if ($this->isExceptRoute($request)) {
            return $next($request);
        }

        $request->session()->put('2fa:user:id', $user->id);

        auth()->logout();

        return redirect()->route('two-factor.challenge');
    }

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
