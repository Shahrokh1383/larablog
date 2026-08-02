<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\PostController;
use Modules\Content\Http\Controllers\Api\CategoryController;
use Modules\Content\Http\Controllers\Api\TagController;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('posts/upload-image', [PostController::class, 'uploadImage']);
    Route::delete('posts/delete-image', [PostController::class, 'deleteImage']);
    
    Route::apiResource('posts', PostController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
});