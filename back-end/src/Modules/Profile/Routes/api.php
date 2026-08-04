<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\Api\ProfileController;
use Modules\Profile\Http\Controllers\Api\PublicProfileController;

// Public Routes
Route::get('profiles/{username}', [PublicProfileController::class, 'show']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    
    // Avatar Management
    Route::post('profile/upload-avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('profile/delete-avatar', [ProfileController::class, 'deleteAvatar']);
});