<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Глобальные middleware для всего приложения
     */
    protected $middleware = [
        // ...existing global middleware...
        \App\Http\Middleware\LogUserActivity::class,
    ];

    protected $routeMiddleware = [
        'seller' => \App\Http\Middleware\EnsureSeller::class,
        'activitylog' => \App\Http\Middleware\LogUserActivity::class,
    ];
}
