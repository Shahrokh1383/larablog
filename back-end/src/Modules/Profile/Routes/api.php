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
});