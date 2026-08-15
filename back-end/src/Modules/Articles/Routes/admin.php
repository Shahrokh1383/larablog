<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\Http\Controllers\Api\PostController;

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::post('posts/upload-image', [PostController::class, 'uploadImage']);
    Route::delete('posts/delete-image', [PostController::class, 'deleteImage']);
    
    Route::apiResource('posts', PostController::class);
});