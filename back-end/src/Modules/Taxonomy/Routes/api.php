<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxonomy\Http\Controllers\Api\CategoryPublicController;
use Modules\Taxonomy\Http\Controllers\Api\TagPublicController;

Route::middleware('throttle:public-taxonomy')->prefix('categories')->group(function () {
    Route::get('/', [CategoryPublicController::class, 'index']);
});

Route::middleware('throttle:public-taxonomy')->prefix('tags')->group(function () {
    Route::get('/', [TagPublicController::class, 'index']);
    Route::get('/popular', [TagPublicController::class, 'popular']);
});