<?php

use App\Http\Controllers\GatewayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['cors', 'rate.limit', 'log.requests'])->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'gateway'   => 'ok',
            'version'   => '1.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::post('/login',    [GatewayController::class, 'userProxy']);
    Route::post('/register', [GatewayController::class, 'userProxy']);
});

Route::middleware(['cors', 'rate.limit', 'log.requests', 'auth.gateway'])->group(function () {

    Route::post('/logout',  [GatewayController::class, 'userProxy']);
    Route::post('/refresh', [GatewayController::class, 'userProxy']);
    Route::get('/me',       [GatewayController::class, 'userProxy']);

    Route::any('/users/{path?}',       [GatewayController::class, 'userProxy'])
        ->where('path', '.*');

    Route::any('/permissions/{path?}', [GatewayController::class, 'userProxy'])
        ->where('path', '.*');

    Route::any('/catalog/{path?}', [GatewayController::class, 'catalogProxy'])
        ->where('path', '.*');
});