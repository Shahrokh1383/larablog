<?php

use Illuminate\Support\Facades\Route;
use Modules\ReaderExperience\Http\Controllers\Api\DashboardController;
use Modules\ReaderExperience\Http\Controllers\Api\ReadingController;
use Modules\ReaderExperience\Http\Controllers\Api\SavedPostController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('reading/posts/{post}/read', [ReadingController::class, 'store']);
    
    Route::post('saved-posts/{post}', [SavedPostController::class, 'toggle']);
    Route::get('saved-posts', [SavedPostController::class, 'index']);
    
    Route::get('dashboard/overview', [DashboardController::class, 'overview']);
    Route::get('dashboard/recently-read', [DashboardController::class, 'recentlyRead']);
});