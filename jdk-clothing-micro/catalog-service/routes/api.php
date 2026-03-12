<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\AttributeValueController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ProductController;

// Categories
Route::apiResource('categories', CategoryController::class);

// Brands
Route::apiResource('brands', BrandController::class);

// Attributes
Route::apiResource('attributes', AttributeController::class);

// Attributes-Values
Route::apiResource('attribute-values', AttributeValueController::class);

// Variants
Route::apiResource('variants', ProductVariantController::class);

// Products
Route::apiResource('products', ProductController::class);
