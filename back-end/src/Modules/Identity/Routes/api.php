<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Api\AuthController;
use Modules\Identity\Http\Controllers\Api\OAuthController;
use Modules\Identity\Http\Controllers\Api\PasswordResetController;

// Registration
Route::post('register', [AuthController::class, 'register'])
    ->middleware('throttle:10,1');

// Authentication (public and admin)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
});

// Password reset
Route::middleware('throttle:5,1')->group(function () {
    Route::post('forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('reset-password', [PasswordResetController::class, 'reset']);
});

// Email verification (signed URL)
Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

// OAuth (not throttled here; provider-specific)
Route::get('oauth/{provider}/redirect', [OAuthController::class, 'redirect']);
Route::get('oauth/{provider}/callback', [OAuthController::class, 'callback']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
});