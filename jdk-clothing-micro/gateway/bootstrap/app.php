<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'auth.gateway' => \App\Http\Middleware\AuthenticateGateway::class,
            'rate.limit'   => \App\Http\Middleware\RateLimitGateway::class,
            'log.requests' => \App\Http\Middleware\LogRequests::class,
            'cors'         => \App\Http\Middleware\CorsGateway::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gateway internal error.',
                    'error'   => class_basename($e),
                ], 500);
            }
        });
    })
    ->create();
