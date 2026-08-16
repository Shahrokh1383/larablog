<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxonomy\Http\Controllers\Api\CategoryPublicController;
use Modules\Taxonomy\Http\Controllers\Api\TagPublicController;

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryPublicController::class, 'index']);
    Route::get('{slug}/posts', [CategoryPublicController::class, 'posts']);
});

Route::prefix('tags')->group(function () {
    Route::get('/', [TagPublicController::class, 'index']);
    Route::get('/popular', [TagPublicController::class, 'popular']);
    Route::get('{slug}/posts', [TagPublicController::class, 'posts']);
});