<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\PostPublicController;
use Modules\Content\Http\Controllers\Api\CategoryPublicController;
use Modules\Content\Http\Controllers\Api\TagPublicController;

Route::prefix('posts')->group(function () {
    Route::get('/', [PostPublicController::class, 'index']);
    Route::get('{slug}', [PostPublicController::class, 'show']);
    Route::get('{slug}/related', [PostPublicController::class, 'related']);
});

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryPublicController::class, 'index']);
    Route::get('{slug}/posts', [CategoryPublicController::class, 'posts']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagPublicController::class, 'index']);
    Route::get('/popular', [TagPublicController::class, 'popular']);
});