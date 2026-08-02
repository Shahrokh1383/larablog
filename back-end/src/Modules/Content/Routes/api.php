<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\PostPublicController;

Route::prefix('posts')->group(function () {
    Route::get('/', [PostPublicController::class, 'index']);
    Route::get('{slug}', [PostPublicController::class, 'show']);
    Route::get('{slug}/related', [PostPublicController::class, 'related']);
});