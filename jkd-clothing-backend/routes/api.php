<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BrandController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Users
    Route::prefix('users')->group(function() {
        Route::get('/general-data', [UserController::class, 'getGeneralData']);
    });

    Route::apiResource('users', UserController::class);

    // Permissions
    Route::prefix('permissions')->group(function() {
        Route::post('/general-data', [PermissionController::class, 'index']);
        Route::get('/roles/{role}', [PermissionController::class, 'getRolePermissions']);
        Route::post('/roles/{role}/assign', [PermissionController::class, 'assignPermissions']);
    });

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Products
    Route::apiResource('products', ProductController::class);

    // Brands
    Route::apiResource('brands', BrandController::class);
});
