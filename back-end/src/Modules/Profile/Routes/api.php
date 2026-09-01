<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\Api\ProfileController;

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::delete('profile', [ProfileController::class, 'destroy']);
    
    // Avatar Management
    Route::post('profile/upload-avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('profile/delete-avatar', [ProfileController::class, 'deleteAvatar']);
});