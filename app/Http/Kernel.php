<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [

        \App\Http\Middleware\LogUserActivity::class,
        \App\Http\Middleware\CheckBan::class,
    ];

    protected $routeMiddleware = [
        'seller' => \App\Http\Middleware\EnsureSeller::class,
        'activitylog' => \App\Http\Middleware\LogUserActivity::class,
        'check.ban' => \App\Http\Middleware\CheckBan::class,
    ];
}
