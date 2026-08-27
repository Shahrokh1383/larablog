<?php

use Illuminate\Support\Facades\Route;
use Modules\Articles\Http\Controllers\Api\PostPublicController;

Route::prefix('posts')->group(function () {
    Route::get('/', [PostPublicController::class, 'index']);
    Route::get('{slug}', [PostPublicController::class, 'show']);
    Route::get('{slug}/related', [PostPublicController::class, 'related']);
});

// Aggregation endpoints for Posts filtered by Taxonomy slugs.
// Kept in Articles module to respect Article I (Thin Controllers) and Article III boundaries.
Route::middleware('throttle:public-taxonomy')->prefix('categories/{categorySlug}')->group(function () {
    Route::get('posts', [PostPublicController::class, 'postsByCategory']);
});

Route::middleware('throttle:public-taxonomy')->prefix('tags/{tagSlug}')->group(function () {
    Route::get('posts', [PostPublicController::class, 'postsByTag']);
});