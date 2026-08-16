<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\CategoryPublicController;
use Modules\Content\Http\Controllers\Api\TagPublicController;
use Modules\Content\Http\Controllers\Api\HomePublicController;

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryPublicController::class, 'index']);
    Route::get('{slug}/posts', [CategoryPublicController::class, 'posts']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagPublicController::class, 'index']);
    Route::get('/popular', [TagPublicController::class, 'popular']);
    Route::get('{slug}/posts', [TagPublicController::class, 'posts']);
});

Route::get('/home', [HomePublicController::class, 'index']);