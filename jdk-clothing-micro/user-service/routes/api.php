<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me',       [AuthController::class, 'me']);

    // Rutas de usuarios
    Route::prefix('users')->group(function () {
        Route::get('/general-data', [UserController::class, 'getGeneralData']);
    });
    Route::apiResource('users', UserController::class);

    // Rutas de permisos
    Route::prefix('permissions')->group(function () {
        Route::post('/general-data',       [PermissionController::class, 'index']);
        Route::get('/roles/{role}',         [PermissionController::class, 'getRolePermissions']);
        Route::post('/roles/{role}/assign', [PermissionController::class, 'assignPermissions']);
    });
});