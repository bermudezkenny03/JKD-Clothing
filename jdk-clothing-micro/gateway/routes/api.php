<?php

use App\Http\Controllers\GatewayController;
use Illuminate\Support\Facades\Route;

// RUTAS PÚBLICAS
Route::middleware(['cors', 'rate.limit', 'log.requests'])->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'gateway'   => 'ok',
            'version'   => '1.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::post('/login', [GatewayController::class, 'userProxy'])
        ->defaults('path', 'login');

    Route::post('/register', [GatewayController::class, 'userProxy'])
        ->defaults('path', 'register');
});

// RUTAS PROTEGIDAS
Route::middleware(['cors', 'rate.limit', 'log.requests', 'auth.gateway'])->group(function () {

    // User Service 
    Route::post('/logout', [GatewayController::class, 'userProxy'])
        ->defaults('path', 'logout');

    Route::post('/refresh', [GatewayController::class, 'userProxy'])
        ->defaults('path', 'refresh');

    Route::get('/me', [GatewayController::class, 'userProxy'])
        ->defaults('path', 'me');

    Route::any('/users{path?}', [GatewayController::class, 'userProxy'])
        ->where('path', '(/.*)?')
        ->defaults('path', 'users');

    Route::any('/permissions{path?}', [GatewayController::class, 'userProxy'])
        ->where('path', '(/.*)?')
        ->defaults('path', 'permissions');

    // Catalog Service
    Route::any('/catalog{path?}', [GatewayController::class, 'catalogProxy'])
        ->where('path', '(/.*)?')
        ->defaults('path', 'catalog');
});
