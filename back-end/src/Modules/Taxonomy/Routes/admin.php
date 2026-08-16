<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\CategoryController;
use Modules\Content\Http\Controllers\Api\TagController;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
});