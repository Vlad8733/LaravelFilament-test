<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $route = $request->route();
            $action = $route ? $route->getName() : $request->path();
            // Не логируем сам просмотр журнала активности
            if ($action !== 'activity_log.index') {
                activity_log('Visited: '.$action);
            }
        }

        return $next($request);
    }
}
