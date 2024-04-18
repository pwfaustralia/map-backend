<?php

use App\Http\Middleware\CheckForAnyScope;
use App\Http\Middleware\CheckScopes;
use App\Http\Middleware\CookieAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'cookie-auth' => CookieAuth::class,
            'scopes' => CheckScopes::class,
            'scope' => CheckForAnyScope::class,
        ]);
        $middleware->priority([
            'auth:api',
            'scope',
            'cookie-auth'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
